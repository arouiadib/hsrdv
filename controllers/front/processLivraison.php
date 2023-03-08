<?php

use PrestaShop\Module\HsRdv\Model\Client;
use PrestaShop\Module\HsRdv\Model\Reparation;
use PrestaShop\Module\HsRdv\Model\Appareil;
use PrestaShop\Module\HsRdv\Model\Booking;


class HsRdvProcessLivraisonModuleFrontController extends ModuleFrontController {

	public $ssl = false;
    
    public function initContent() {
        parent::initContent();
        $this->ajax = true;
    }

    public function displayAjax()
    {
        if ($this->errors)
            die(Tools::jsonEncode(array('hasError' => true, 'errors' => $this->errors)));

        if(Tools::getValue('action')=='EnvoyerModeLivraison')
        {
            $this->context->smarty->assign($this->getTemplateVars());

            echo json_encode($this->context->smarty->fetch('module:hsrdv/views/templates/front/livraison/livraison_form.tpl'));
            die();
        }
    }


    public function getTemplateVars() {

        $notifications2 = false;

        if (Tools::getValue('action') === 'EnvoyerModeLivraison'){
            $this->validateModeLivraison();
            if (!empty($this->errors)) {

                $notifications2['messages'] = $this->errors;
                $notifications2['nw_error'] = true;
            } elseif (!empty($this->success)) {
                $notifications2['messages'] = $this->success;
                $notifications2['nw_error'] = false;
            }
        }

        $reparation = new Reparation((int)Tools::getValue('id_reparation'));
        $idOrder = $reparation->id_order;

        return [
            'livraison_form_action_link' => $rdvFormAction = $this->context->link->getModuleLink('hsrdv', 'processLivraison'),
            'notifications2' => $notifications2,
            'reparationToken' => Tools::getValue('reparationToken'),
            'id_reparation' => Tools::getValue('id_reparation'),
            'id_order' => $idOrder
        ];
    }

    public function validateModeLivraison() {


        $modeLivraison = Tools::getValue('mode_livraison');


        if (!$modeLivraison) {
            $this->context->controller->errors[] = $this->trans(
                'Ce n\'est pas possible! Vous devez choisir un mode de livraison',[], 'Modules.Hsrdv.Shop');

        }
        if (!count($this->errors)) {
            $this->success[] = $this->trans(
                'Your choosen delivery method is successfuly saved',
                [],
                'Modules.Hsrdv.Shop'
            );

            $this->updateReparationModeLivraison();
            //$this->sendMailDemandeReparationConfirmee();
            // Send mails
        }
    }

    private function updateReparationModeLivraison() {

        $idReparation = (int) Tools::getValue('id_reparation');
        $reparation = new Reparation($idReparation);
        $idOrder = $reparation->id_order;
        $order = new Order($idOrder);
        $states = $this->getOrderStatuses();
        if ($reparation->id_reparation) {
            $order->current_state = $states['A_LIVRER'];
            $reparation->mode_livraison = Tools::getValue('mode_livraison');
            $reparation->update();
            $order->update();


        } else {
            $this->context->controller->errors[] = $this->trans(
                'An error occurred while updating reparation record',
                [],
                'Modules.Hsrdv.Shop'
            );
        }
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
