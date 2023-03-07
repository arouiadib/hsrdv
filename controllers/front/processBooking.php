<?php

use PrestaShop\Module\HsRdv\Model\Client;
use PrestaShop\Module\HsRdv\Model\Reparation;
use PrestaShop\Module\HsRdv\Model\Appareil;
use PrestaShop\Module\HsRdv\Model\Booking;
use PrestaShop\Module\HsRdv\Model\BookingException;


class HsRdvProcessBookingModuleFrontController extends ModuleFrontController {

	public $ssl = false;
	private $rdv;
    
    public function initContent() {
        parent::initContent();
        $this->ajax = true;
    }

    public function displayAjax()
    {
        if ($this->errors)
            die(Tools::jsonEncode(array('hasError' => true, 'errors' => $this->errors)));

        if(Tools::getValue('action')=='ReserverTimeSlot')
        {
            $this->context->smarty->assign($this->getTemplateVars());

            echo json_encode(
                [
                    'modal' => $this->context->smarty->fetch('module:hsrdv/views/templates/front/calendar/timeslots/timeslots_form.tpl'),
                    'time_booked' =>  Tools::getValue('time'),
                    'date_booked' =>  Tools::getValue('date')
                ]
            );
            die();
        }
    }

    public function getBooking()
    {
        $token = Tools::getValue('reparationToken');
        $reparation = Reparation::getReparationFromToken($token);
        $appareils = Appareil::getAppareilsFromIdReparation($reparation['id_reparation']);

        $this->booking['nom'] =  Tools::getValue('nom');
        $this->booking['prenom'] =  Tools::getValue('prenom');
        $this->booking['telephone'] =  Tools::getValue('telephone');
        $this->booking['time'] =  Tools::getValue('time');
        $this->booking['addresse_postale'] =  Tools::getValue('addresse_postale');
        $this->booking['email'] =  Tools::getValue('email');
        $this->booking['appareils'] =  $appareils;
        $this->booking['id_reparation'] =  Tools::getValue('id_reparation');
        $this->booking['id_order'] =  Tools::getValue('id_order');

        return $this->booking;
    }

    public function getTemplateVars() {

        $notifications2 = false;

        if (Tools::getValue('action') === 'ReserverTimeSlot'){
            $this->validateBooking();
            if (!empty($this->errors)) {

                $notifications2['messages'] = $this->errors;
                $notifications2['nw_error'] = true;
            } elseif (!empty($this->success)) {
                $notifications2['messages'] = $this->success;
                $notifications2['nw_error'] = false;
            }
        }

        return [
            'client_booking' => $this->getBooking(),
            'timeslots' => $this->timeslots(Tools::getValue('date')),
            'book_form_action_link' => $rdvFormAction = $this->context->link->getModuleLink('hsrdv', 'processBooking'),
            'notifications2' => $notifications2,
            'reparationToken' => Tools::getValue('reparationToken'),
            //'token' => $this->context->cookie->contactFormToken,
            //'id_module' => $this->id
            'date' => Tools::getValue('date'),
            'date_booked' => Tools::getValue('dateBooked'),
            'time_booked' => Tools::getValue('time'),
        ];
    }

    public function validateBooking() {

        $nom = trim(Tools::getValue('nom'));
        $prenom = trim(Tools::getValue('prenom'));
        $telephone = trim(Tools::getValue('telephone'));
        $addressePostale = trim(Tools::getValue('addresse_postale'));
        $timeslot = Tools::getValue('time');

        if (!$timeslot) {
            $this->context->controller->errors[] = $this->trans(
                'Ce n\'est pas possible! Vous devez choisir un creneau',[], 'Modules.Hsrdv.Shop');

        }

        elseif (!$nom) {
            $this->context->controller->errors[] = $this->trans(
                'Ce n\'est pas possible! Vous devez saisir votre nom.',[], 'Modules.Hsrdv.Shop');

        }

        elseif (!$prenom) {
            $this->context->controller->errors[] = $this->trans(
                'Ce nest pas possible! Vous devez saisir votre prenom',[], 'Modules.Hsrdv.Shop');

        }

        elseif (!$addressePostale) {
            $this->context->controller->errors[] = $this->trans(
                'Ce nest pas possible! Vous devez saisir votre code postal ',[], 'Modules.Hsrdv.Shop');

        }
        elseif ($nom && !Validate::isName($nom)) {
            $this->context->controller->errors[] = $this->trans(
                'Ce nest pas valide! Vous devez corriger votre nom.',
                [],
                'Modules.Hsrdv.Shop'
            );
        }
        elseif ($prenom && !Validate::isName($prenom)) {
            $this->context->controller->errors[] = $this->trans(
                'Ce n\'est pas valide! Vous devez corriger votre prenom.',
                [],
                'Modules.Hsrdv.Shop'
            );
        }

        elseif ($telephone && !Validate::isPhoneNumber($telephone)) {
            $this->context->controller->errors[] = $this->trans(
                'Le numéro de téléphone n\'est pas valide!',
                [],
                'Modules.Hsrdv.Shop'
            );
        }
        /*elseif ( empty($serverToken)
            || $clientToken !== $serverToken
            || $clientTokenTTL < time()
        ) {
            $this->context->controller->errors[] = $this->trans(
                'Une erreur s\'est produite lors de candidature, merci de réessayer plus tard!',
                [],
                'Modules.Hsrdv.Shop'
            );
            $this->createNewToken();
        }*/

        if (!count($this->errors)) {
            $this->success[] = $this->trans(
                'Your choosen timeslot is successfuly booked',
                [],
                'Modules.Hsrdv.Shop'
            );

            $this->saveBooking();
            //$this->sendMailDemandeReparationConfirmee();
            // Send mails
        }
    }

    private function saveBooking() {
        setlocale(LC_TIME, "fr_FR");
        $nom = trim(Tools::getValue('nom'));
        $prenom = trim(Tools::getValue('prenom'));
        $addresse_postale = trim(Tools::getValue('addresse_postale'));
        $telephone =  trim(Tools::getValue('telephone'));
        $idReparation = (int) Tools::getValue('id_reparation');
        $idOrder = (int) Tools::getValue('id_order');
        $time = trim(Tools::getValue('time'));
        $date = trim(Tools::getValue('date'));

        $reparationBookings = Booking::getBookingsFromIdReparation($idReparation);
        $order = new Order((int)$idOrder);
        $states = $this->getOrderStatuses();

        if(count($reparationBookings) > 0) {
            foreach ($reparationBookings as $booking) {
                $booking = new Booking($booking['id_booking']);
                $booking->delete();
            }
        }

        $interval = new DateInterval("PT". Configuration::get('HSRDV_TIMESLOT_DURATION') ."M");
        $start = new DateTime($time);
        $end = clone $start;
        $end->add($interval);
        $timeSlotString = $start->format("H:iA")." - ". $end->format("H:iA");

        //todo: for more secure and robust code, check if booking exception exist before storing booking and show exception if true
        $booking = new Booking();
        $booking->timeslot_booking = $timeSlotString;
        $booking->time_booking = $time;
        $booking->date_booking = $date;
        $booking->id_reparation = $idReparation;
        $booking->add();

        $appareils = Appareil::getAppareilsFromIdReparation($idReparation);
        $countAppareils = count($appareils);

        if ($countAppareils > 2) {
            $start->add($interval);
            $end->add($interval);
            $timeSlotString = $start->format("H:iA")." - ". $end->format("H:iA");
            $booking = new Booking();
            $booking->timeslot_booking = $timeSlotString;
            $booking->time_booking = $time;
            $booking->date_booking = $date;
            $booking->id_reparation = $idReparation;
            $booking->add();
        }

        $customer = new Customer($order->id_customer);
        $customer->firstname = $prenom;
        $customer->lastname = $nom;
        //var_dump($customer);
        $addressId = Address::getFirstCustomerAddressId($customer->id);
        //var_dump($addressId);die;
        if ($addressId) {
            $address = new Address($addressId);
        }
        else {
            $address = new Address();
        }

        $address->id_customer = (int)$customer->id;
        $address->id_country = 8;
        $address->alias = 'Un adresse';
        $address->lastname = $customer->lastname;
        $address->firstname = $customer->firstname;
        $address->address1 = $addresse_postale;
        $address->postcode = '15400';
        $address->phone = $telephone;
        $address->city = 'Paris';

        if ($addressId) {
            $address->update();
        }
        else {
            $address->add();
        }

        $order->current_state = $states['RDV_PRIS'];
        $order->update();
        if ($customer->update()) {
            $appareilsListString = '';

            $lastAppareilKey = array_key_last($appareils);
            foreach ($appareils as $key => $appareil) {
                $appareilsListString = $appareilsListString . $appareil['marque'] . ' ' . $appareil['reference'];
                if ($lastAppareilKey != $key)
                {
                    $appareilsListString = $appareilsListString . ', ';
                }
            }

            //echo $datetime->format('l');
            $langLocale = $this->context->language->locale;
            $explodeLocale = explode('-', $langLocale);
            $localeOfContextLanguage = $explodeLocale[0].'_'.Tools::strtoupper($explodeLocale[1]);

            setlocale(LC_ALL, $localeOfContextLanguage.'.UTF-8', $localeOfContextLanguage);
            $dateFormatted = strftime("%d %B %Y", strtotime( $date));


            $var_list = [
                '{date}' =>  $dateFormatted,
                '{liste_appareils}' => $appareilsListString,
                '{nom}' => $nom,
                '{prenom}' => $prenom,
                '{heure}' => $time

            ];
            //todo: Rappeler seulement les appareils acceptés

            if($customer->email){
                $sent= Mail::Send(
                    $this->context->language->id,
                    'hsrdv_confirmation_rendez_vous',
                    $this->trans('Confirmation de rendez-vous le %date% à %heure% - [%id_reparation%]',
                        [
                           '%date%' => $dateFormatted,
                           '%heure%' => $time,
                           '%id_reparation%' => $idReparation
                        ],
                        'Modules.Hsrdv.ProcessRdvInitial'),
                    $var_list,
                    $customer->email,
                    null,
                    null,
                    null,
                    null,
                    null,
                    _PS_MAIL_DIR_,
                    false,
                    null,
                    null,
                    null
                );

                if (!$sent) {
                    $this->context->controller->errors[] = $this->trans(
                        'Erreur envoi mail confirmation de rendez vous ',
                        [],
                        'Modules.Hsrdv.ProcessRdvInitial'
                    );
                }
            }

        } else {
            $this->context->controller->errors[] = $this->trans(
                'An error occurred while creating reparation record',
                [],
                'Modules.Hsrdv.Shop'
            );
        }
    }

    public function timeslots($date){

        $bookingsOfTheDay = array_column(Booking::getDayBookings($date), 'timeslot_booking');
        $bookingExceptionsOfTheDay = array_column(BookingException::getDayBookingExceptions($date), 'timeslot_booking_exception');

        $start = new DateTime(Configuration::get('HSRDV_DAY_START'));
        $end = new DateTime(Configuration::get('HSRDV_DAY_END'));
        $interval = new DateInterval("PT". Configuration::get('HSRDV_TIMESLOT_DURATION') ."M");
        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));
        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = (new DateTime(Configuration::get('HSRDV_BREAK_TIME')))->add($breakDuration);

        $slots = [];
        $i = 0;

        for($intStart = $start; $intStart < $breaktime; $intStart->add($interval)){
            $endPeriod = clone $intStart;
            $endPeriod->add($interval);
            if($endPeriod > $breaktime){
                break;
            }
            $timeSlotString = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
            $slots['morning'][$i]['time'] = $intStart->format("H:i");
            $slots['morning'][$i]['timeslot_string'] = $timeSlotString;
            $slots['morning'][$i]['exceptionned'] = in_array($timeSlotString, $bookingExceptionsOfTheDay) ? true : false;
            $slots['morning'][$i]['booked'] = in_array($timeSlotString, $bookingsOfTheDay) ? true : false;
            $i++;
        }

        for($intStart = $restartAfterBreak; $intStart < $end; $intStart->add($interval)){
            $endPeriod = clone $intStart;
            $endPeriod->add($interval);
            if($endPeriod > $end){
                break;
            }
            $timeSlotString = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
            $slots['afternoon'][$i]['time'] = $intStart->format("H:i");
            $slots['afternoon'][$i]['timeslot_string'] = $timeSlotString;
            $slots['afternoon'][$i]['exceptionned'] = in_array($timeSlotString, $bookingExceptionsOfTheDay) ? true : false;
            $slots['afternoon'][$i]['booked'] = in_array($timeSlotString, $bookingsOfTheDay) ? true : false;
            $i++;
        }

        return $slots;
    }

    private function getOrderStatuses() {
        $finalStatuses = [];
        $statuses = Hsrdv::STATUSES;
        $dbStatuses = OrderState::getOrderStates($this->context->language->id);

        foreach ($dbStatuses as $dbStatus) {
            foreach ($statuses as $key => $status) {
                if ($status['title'] == $dbStatus['name'] ) {
                    $finalStatuses[$key] = (int)$dbStatus['id_order_state'];
                }
            }

        }

        return $finalStatuses;
    }
}
