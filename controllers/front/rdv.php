<?php

class HsrdvRdvModuleFrontController extends ModuleFrontController {

	public $ssl = true;

    private $rdv;

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        parent::init();
    }

	public function initContent()
    {
        parent::initContent();

        $rdvFormAction = $this->context->link->getModuleLink('hsrdv', 'processRdvInitial');

        $this->context->smarty->assign(
            array(
                'notifications2' => false,
                'rdv'               => $this->getRdv(),
                'rdv_form_action'   => $rdvFormAction
            )
        );

        $this->setTemplate('module:hsrdv/views/templates/front/rdv-initiate.tpl');
    }


    public function getRdv()
    {
        $this->rdv['appareils'] =  [];
        $this->rdv['email'] =  '';
        $this->rdv['newsletter'] = 0;

        return $this->rdv;
    }

    public function setMedia()
    {
        parent::setMedia();

       $this->context->controller->registerJavascript(
            'hsrdv',
            'modules/'.$this->module->name.'/views/js/initial-rdv.js',
            array(
                'position' => 'bottom',
                'priority' => 150
            )
        );
    }
}
