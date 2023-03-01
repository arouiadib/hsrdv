<?php
use PrestaShop\Module\HsRdv\Model\Reparation;

class HsRdvSatisfactionModuleFrontController extends ModuleFrontController {

	public $ssl = false;

	public function init() 
	{
		parent::init();
	}

	public function initContent()
    {
        parent::initContent();

        //$livraisonFormActionLink = $this->context->link->getModuleLink('hsrdv', 'processLivraison');

        $reparationToken = Tools::getValue('reparationToken');

        $reparation = Reparation::getReparationFromToken($reparationToken);

        $this->context->smarty->assign(
            array(
                //'livraison_form_action_link' => $livraisonFormActionLink,
                'reparationToken' => $this->getReparationFromToken(),
                'notifications2' => false,
                'id_reparation' => $reparation['id_reparation']
            )
        );

        $this->setTemplate('module:hsrdv/views/templates/front/satisfaction.tpl');
    }

    public function getReparationFromToken()
    {
        return Tools::getValue('reparationToken');

    }

    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->registerJavascript(
            'hsrdv',
            'modules/'.$this->module->name.'/views/js/satisfaction.js',
            array(
                'position' => 'bottom',
                'priority' => 150
            )
        );
    }
}
