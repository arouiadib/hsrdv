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


        return [
            'livraison_form_action_link' => $rdvFormAction = $this->context->link->getModuleLink('hsrdv', 'processLivraison'),
            'notifications2' => $notifications2,
            'reparationToken' => Tools::getValue('reparationToken'),
            'id_reparation' => Tools::getValue('id_reparation'),
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


        if ($reparation->id_reparation) {
            //var_dump(Tools::getValue('mode_livraison'));die;
            $reparation->id_status = $this->module :: A_LIVRER;
            $reparation->mode_livraison = Tools::getValue('mode_livraison');
            //var_dump($reparation->mode_livraison);die;
            $reparation->update();


        } else {
            $this->context->controller->errors[] = $this->trans(
                'An error occurred while updating reparation record',
                [],
                'Modules.Hsrdv.Shop'
            );
        }
    }
}
