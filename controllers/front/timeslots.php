<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\Module\HsRdv\Model\Booking;
use PrestaShop\Module\HsRdv\Model\BookingException;
use PrestaShop\Module\HsRdv\Model\Reparation;
use PrestaShop\Module\HsRdv\Model\Client;
//todo remove client
use PrestaShop\Module\HsRdv\Model\Appareil;

class HsRdvTimeslotsModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    private $booking;
    private $client;
    /**
    * @see FrontController::initContent()
    */
    public function initContent() {
        parent::initContent();
        $this->ajax = true;
    }

    public function displayAjax()
    {
        if ($this->errors) die(Tools::jsonEncode(array('hasError' => true, 'errors' => $this->errors)));

        //if(Tools::getValue('action')=='PrendreRendezVous')
        //{
        $day = (int) Tools::getValue('day');
        $month = (int) Tools::getValue('month');
        $year = (int) Tools::getValue('year');

        $dayMonthYear = mktime(0,0,0, $month, $day, $year);
        $date = date('Y-m-d', $dayMonthYear);

        $this->context->smarty->assign($this->getTemplateVars($date));

        echo json_encode(
            [
                'modal' => $this->context->smarty->fetch('module:hsrdv/views/templates/front/calendar/timeslots/timeslots_modal.tpl')
            ]);
        die();
        //}
    }

    public function getTemplateVars($date) {
        $bookFormActionLink = $this->context->link->getModuleLink('hsrdv', 'processBooking');

        return [
            'timeslots' => $this->timeslots($date),
            'notifications2' => false,
            'client_booking' => $this->getBooking(),
            'book_form_action_link'   => $bookFormActionLink,
            'reparationToken' => Tools::getValue('reparationToken'),
            'date_booked' => Tools::getValue('dateBooked'),
            'time_booked' => Tools::getValue('timeBooked'),
            'date' => $date
        ];
    }

    public function getBooking()
    {
        $reparationToken = Tools::getValue('reparationToken');

        $reparation = Reparation::getReparationFromToken($reparationToken);
        $appareils = Appareil::getAppareilsFromIdReparation($reparation['id_reparation']);

        $customer = new Customer((int)$reparation['id_client']);
        $addressId = Address::getFirstCustomerAddressId($customer->id);

        if ($addressId) {
            $address = new Address($addressId);
        }

        $this->booking['nom'] = $customer->lastname;
        $this->booking['prenom'] = $customer->firstname;
        $this->booking['email'] = $customer->email;
        $this->booking['telephone'] = $address->phone;
        $this->booking['addresse_postale'] = $address->address1;
        $this->booking['appareils'] = $appareils;
        $this->booking['id_reparation'] = $reparation['id_reparation'];

        return $this->booking;
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
}
