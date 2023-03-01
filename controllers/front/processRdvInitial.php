<?php

use PrestaShop\Module\HsRdv\Model\Client;
use PrestaShop\Module\HsRdv\Model\Reparation;
use PrestaShop\Module\HsRdv\Model\Appareil;

class HsRdvProcessRdvInitialModuleFrontController extends ModuleFrontController {

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

        if(Tools::getValue('action')=='PrendreRendezVous')
        {
            $this->context->smarty->assign($this->getTemplateVars());

            echo json_encode($this->context->smarty->fetch('module:hsrdv/views/templates/front/rdv-form.tpl'));
            die();
        }
    }

    public function getRdv()
    {
        $this->rdv['appareils'] =  Tools::getValue('appareils');

        $this->rdv['email'] =  Tools::safeOutput(
            Tools::getValue(
                'from',
                !empty($this->context->cookie->email) && Validate::isEmail($this->context->cookie->email) ?
                    $this->context->cookie->email :
                    ''
            )
        );
        $this->rdv['newsletter'] = Tools::getValue('newsletter') === 'on' ? true :false ;

        return $this->rdv;

    }

    public function getTemplateVars() {


        $notifications2 = false;

        if (Tools::getValue('action') === 'PrendreRendezVous'){
            $this->validateReparation();
            if (!empty($this->errors)) {

                $notifications2['messages'] = $this->errors;
                $notifications2['nw_error'] = true;
            } elseif (!empty($this->success)) {
                $notifications2['messages'] = $this->success;
                $notifications2['nw_error'] = false;
            }
        }

        return [
            'rdv' => $this->getRdv(),
            'rdv_form_action' => $rdvFormAction = $this->context->link->getModuleLink('hsrdv', 'processRdvInitial'),
            'notifications2' => $notifications2,
            //'token' => $this->context->cookie->contactFormToken,
            //'id_module' => $this->id
        ];
    }

    public function validateReparation() {

        $from = trim(Tools::getValue('from'));
        $appareils = Tools::getValue('appareils');
        $marques = [];
        $references = [];
        $pannes = [];

        foreach ($appareils as $appareil) {
            $marques[] = $appareil['marque'];
            $references[] = $appareil['reference'];
            $pannes[] = $appareil['descriptif_panne'];
        }

        if(!$from) {
            $this->errors[] = $this->trans(
                'Invalid email address.',
                [],
                'Shop.Notifications.Error'
            );
        }

        if ($from && !Validate::isEmail($from)) {
            $this->errors[] = $this->trans(
                'Invalid email address.',
                [],
                'Shop.Notifications.Error'
            );
        }
/*        elseif (empty($marques) ) {
            $this->errors[] = $this->trans(
                'empty marques.',
                [],
                'Shop.Notifications.Error'
            );
        }*/
        if (!empty($marques)) {
            foreach ( $marques as $marque) {
                if(!$marque || !Validate::isCatalogName($marque)) {
                    $this->errors[] = $this->trans(
                        'invalid marque',
                        [],
                        'Shop.Notifications.Error'
                    );
                }
            }

        }
        /*elseif (empty($references) ) {
            $this->errors[] = $this->trans(
                'empty marques.',
                [],
                'Shop.Notifications.Error'
            );
        }*/
        if (!empty($references)) {
            foreach ( $references as $reference) {
                if(!$reference || !Validate::isCatalogName($reference)) {
                    $this->errors[] = $this->trans(
                        'invalid reference',
                        [],
                        'Shop.Notifications.Error'
                    );
                }
            }

        }
/*        elseif (empty($pannes) ) {
            $this->errors[] = $this->trans(
                'empty pannes.',
                [],
                'Shop.Notifications.Error'
            );
        }*/

        if (!empty($pannes)) {

            foreach ($pannes as $panne) {

                if (empty($panne)) {
                    $this->errors[] = $this->trans(
                        'The message cannot be blank.',
                        [],
                        'Shop.Notifications.Error'
                    );
                }
                if (!Validate::isCleanHtml($panne)) {
                    $this->errors[] = $this->trans(
                        'Invalid message',
                        [],
                        'Shop.Notifications.Error'
                    );
                }
            }
        }

        if (!count($this->errors)) {
            $this->success[] = $this->trans(
                'Your message has been successfully sent to our team.',
                [],
                'Modules.Contactform.Shop'
            );

            $appareils_string = $this->saveReparation();
            $this->sendMailDemandeReparationConfirmee($appareils_string);
            // Send mails
        }
    }

    private function saveReparation() {
        $from = Tools::getValue('from');
        $appareils = Tools::getValue('appareils');

        $existing_customer = new Customer();
        $id_customer = $existing_customer->customerExists(strtolower(trim($from)), true, false);

        if (!$id_customer) {
            $customer = new Customer();
            $user_password = '00000000';
            $customer->passwd = md5(pSQL(_COOKIE_KEY_ . $user_password));
            $customer->email = strtolower(trim($from));
            $customer->lastname = $customer->firstname = 'empty';
            $customer->newsletter = Tools::getValue('newsletter') === 'on' ? true :false ;
            $customer->is_guest = 1;
            $customer->active = 1;
            if (!$customer->add()) {
                $this->context->controller->errors[] = $this->trans(
                    'An error occurred while creating customer',
                    [],
                    'Modules.Hsrdv.Shop'
                );
            }
            $id_customer = $customer->id;
        }
        // todo: if excisting customer, update newsletter
        $reparation = new Reparation();
        $reparation->id_status = $this->module ::DEMANDE_REPARATION;
        $reparation->id_client = $id_customer;
        $reparation->token = Tools::passwdGen(12);

        $timeNow = new DateTime();
        $reparation->date_demande = $timeNow->format('Y-m-d H:i:s');;
        if(!$reparation->add()) {
            $this->context->controller->errors[] = $this->trans(
                'An error occurred while creating reparation record',
                [],
                'Modules.Hsrdv.Shop'
            );
        }

        $appareilsListString = '';

        $lastAppareilKey = array_key_last($appareils);
        foreach ($appareils as $key => $appareil) {
            $persitedAppareil = new Appareil();
            $persitedAppareil->marque = $appareil['marque'];
            $persitedAppareil->reference = $appareil['reference'];
            $persitedAppareil->descriptif_panne = $appareil['descriptif_panne'];
            $persitedAppareil->id_reparation = $reparation->id;
            if (!$persitedAppareil->add()) {
                $this->context->controller->errors[] = $this->trans(
                    'An error occurred while creating appareil record',
                    [],
                    'Modules.Hsrdv.Shop'
                );
            }

            $appareilsListString = $appareilsListString .  $appareil['marque'] . ' ' . $appareil['reference'];
            if ($lastAppareilKey != $key)
            {
                $appareilsListString = $appareilsListString . ', ';
            }
        }

        /************************************************
         * Create Order
         *
         */
        $order = new Order();
        //$order->product_list = $package['product_list'];

        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            /*$address = new Address((int)$id_address);
            $this->context->country = new Country((int)$address->id_country, (int)$this->context->cart->id_lang);
            if (!$this->context->country->active) {
                throw new PrestaShopException('The delivery address country is not active.');
            }*/
        }

/*        $carrier = null;
        if (!$this->context->cart->isVirtualCart() && isset($package['id_carrier'])) {
            $carrier = new Carrier((int)$package['id_carrier'], (int)$this->context->cart->id_lang);
            $order->id_carrier = (int)$carrier->id;
            $id_carrier = (int)$carrier->id;
        } else {
            $order->id_carrier = 0;
            $id_carrier = 0;
        }*/

        $order->id_carrier = 0;
        $id_carrier = 0;
        $reference = Order::generateReference();
        $order->id_customer = (int)$id_customer;
        $order->id_address_invoice = 17/*(int)$this->context->cart->id_address_invoice*/;
        $order->id_address_delivery = 17/*(int)$id_address*/;
        $order->id_currency = $this->context->currency->id;
        $order->id_lang = (int)$this->context->cart->id_lang;
        $order->id_cart = (int)$this->context->cart->id;
        $order->reference = $reference;
        $order->id_shop = (int)$this->context->shop->id;
        $order->id_shop_group = (int)$this->context->shop->id_shop_group;
        $secure_key =  md5(uniqid(rand(), true));
        $order->secure_key = ($secure_key ? pSQL($secure_key) : pSQL($this->context->customer->secure_key));
        $order->payment = 'gh';
        $order->module = 'free_order';
        $order->total_paid = 0;
        $order->total_paid_real = 0;
        $order->total_products = 0;
        $order->total_products_wt = 0;
        $order->conversion_rate = $this->context->currency->conversion_rate;
        $order->current_state = 22;
        /*if (isset($this->name)) {
            $order->module = $this->name;
        }*/
        /*
        $order->recyclable = $this->context->cart->recyclable;
        $order->gift = (int)$this->context->cart->gift;
        $order->gift_message = $this->context->cart->gift_message;
        $order->mobile_theme = $this->context->cart->mobile_theme;
        $order->conversion_rate = $this->context->currency->conversion_rate;
        $amount_paid = !$dont_touch_amount ? Tools::ps_round((float)$amount_paid, 2) : $amount_paid;


        $order->total_products = (float)$this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $id_carrier);
        $order->total_products_wt = (float)$this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $id_carrier);
        $order->total_discounts_tax_excl = (float)abs($this->context->cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $id_carrier));
        $order->total_discounts_tax_incl = (float)abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $id_carrier));
        $order->total_discounts = $order->total_discounts_tax_incl;

        $order->total_shipping_tax_excl = (float)$this->context->cart->getPackageShippingCost((int)$id_carrier, false, null, $order->product_list);
        $order->total_shipping_tax_incl = (float)$this->context->cart->getPackageShippingCost((int)$id_carrier, true, null, $order->product_list);
        $order->total_shipping = $order->total_shipping_tax_incl;

        if (!is_null($carrier) && Validate::isLoadedObject($carrier)) {
            $order->carrier_tax_rate = $carrier->getTaxesRate(new Address((int)$this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
        }

        $order->total_wrapping_tax_excl = (float)abs($this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $id_carrier));
        $order->total_wrapping_tax_incl = (float)abs($this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $id_carrier));
        $order->total_wrapping = $order->total_wrapping_tax_incl;

        $order->total_paid_tax_excl = (float)Tools::ps_round((float)$this->context->cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $id_carrier), _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_paid_tax_incl = (float)Tools::ps_round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $id_carrier), _PS_PRICE_COMPUTE_PRECISION_);

        $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
        $order->round_type = Configuration::get('PS_ROUND_TYPE');

        $order->invoice_date = '0000-00-00 00:00:00';
        $order->delivery_date = '0000-00-00 00:00:00';

        if (self::DEBUG_MODE) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - Order is about to be added', 1, null, 'Cart', (int)$id_cart, true);
        }*/

// Creating order
        $result = $order->add();

        if (!$result) {
            //PrestaShopLogger::addLog('PaymentModule::validateOrder - Order cannot be created', 3, null, 'Cart', (int)$id_cart, true);
            throw new PrestaShopException('Can\'t save Order');
        }


        $history = new OrderHistory();
        $history->id_order = (int)$order->id;
        $history->changeIdOrderState(22, (int)($result));
// Amount paid by customer is not the right one -> Status = payment error
// We don't use the following condition to avoid the float precision issues : http://www.php.net/manual/en/language.types.float.php
// if ($order->total_paid != $order->total_paid_real)
// We use number_format in order to compare two string
        /*if ($order_status->logable && number_format($cart_total_paid, _PS_PRICE_COMPUTE_PRECISION_) != number_format($amount_paid, _PS_PRICE_COMPUTE_PRECISION_)) {
            $id_order_state = Configuration::get('PS_OS_ERROR');
        }

        $order_list[] = $order;

        if (self::DEBUG_MODE) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - OrderDetail is about to be added', 1, null, 'Cart', (int)$id_cart, true);
        }*/

// Insert new Order detail list using cart for the current order
       /* $order_detail = new OrderDetail(null, null, $this->context);
        $order_detail->createList($order, $this->context->cart, $id_order_state, $order->product_list, 0, true, $package_list[$id_address][$id_package]['id_warehouse']);
        $order_detail_list[] = $order_detail;

        if (self::DEBUG_MODE) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - OrderCarrier is about to be added', 1, null, 'Cart', (int)$id_cart, true);
        }*/

// Adding an entry in order_carrier table
      /*  if (!is_null($carrier)) {
            $order_carrier = new OrderCarrier();
            $order_carrier->id_order = (int)$order->id;
            $order_carrier->id_carrier = (int)$id_carrier;
            $order_carrier->weight = (float)$order->getTotalWeight();
            $order_carrier->shipping_cost_tax_excl = (float)$order->total_shipping_tax_excl;
            $order_carrier->shipping_cost_tax_incl = (float)$order->total_shipping_tax_incl;
            $order_carrier->add();
        }*/
        /************************************************
         * Create Order
         *
         */
        return $appareilsListString;
    }

    public function sendMailDemandeReparationConfirmee($appareils_string) {

        $from = trim(Tools::getValue('from'));

        $var_list = [
            '{email}' =>  $from,
            '{appareils_name}' => $appareils_string,
        ];

        if($from){
            $sent= Mail::Send(
                $this->context->language->id,
                'hsrdv_confirmation_demande',
                $this->trans('Demande de réparation - %appareils_name%',
                    ['%appareils_name%' => $appareils_string ],
                    'Modules.Hsrdv.ProcessRdvInitial'),
                $var_list,
                $from,
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
                    'Erreur lors envoi mail de confirmation de demande. Pourtant, elle est recu dans notre backoffice!',
                    ['%appareils_name%' => $appareils_string ],
                    'Modules.Hsrdv.ProcessRdvInitial'
                );
            }
        }
    }
}
