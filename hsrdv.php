<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PrestaShop\Module\HsRdv\Entity\TypeReparation;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\Module\HsRdv\Repository\ReparationRepository;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\Module\HsRdv\Model\Reparation;
use PrestaShop\Module\HsRdv\Model\Client;
use PrestaShop\Module\HsRdv\Model\Appareil;
use PrestaShop\Module\HsRdv\Model\Booking;

class Hsrdv extends Module implements WidgetInterface
{
    const STATUSES = [
        'DEMANDE_REPARATION' => [ 'title' => 'Demande de réparation', 'color' => '#faff0b'],
        'PRISE_RDV' => [ 'title' => 'Prise de rendez-vous', 'color' => '#24ff23'],
        'RDV_REFUSE' => [ 'title' => 'Rendez-vous refusé', 'color' => '#ff4727'],
        'RDV_PRIS' => [ 'title' => 'Rendez-vous pris', 'color' => '#26aa00'],
        'REPARATION_EN_COURS' => [ 'title' => 'Réparation en cours', 'color' => '#005d9a'],
        'NON_PRIS_EN_CHARGE' => [ 'title' => 'Non pris en charge', 'color' => '#fe1600'],
        'REPARE' => [ 'title' => 'Réparé', 'color' => '#0a5e00'],
        'A_LIVRER' => [ 'title' => 'A Livrer', 'color' => '#b7b9ff'],
        'LIVRE' => [ 'title' => 'Livré', 'color' => '#0500ce'],
        'ENQUETE' => [ 'title' => 'Enquête de satisfaction', 'color' => '#ff37dc']
    ];

    /* @var ReparationRepository */
    private $reparationRepository;

    public $templates = [];
    public $templateFile;
    /**
     * @var string
     */
    public $templateFileColumn;

    public function __construct()
    {
        $this->name = 'hsrdv';
        $this->tab = 'content_management';
        $this->version = '2.0.0';
        $this->author = 'Adib Aroui';
        $this->need_instance = 1;

        parent::__construct();

        $this->displayName = $this->l('Module de prise de rendez vous');
        $this->description = $this->l('Module de prise de rendez vous sous PrestaShop 1.7 pour HiFi Store Paris');

        $this->confirmUninstall = $this->l('Uninstall?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('rdv', 'editRdv', 'livraison', 'satisfaction');
    }

    public function install()
    {
        $hooks = [
            'displayBackOfficeOrderActions',
            'displayAdminOrderTabLink',
            'displayAdminOrderTabContent',
            'displayAdminOrderMain',
            'displayAdminOrderSide',
            'displayAdminOrder',
            'displayAdminOrderTop',
            'actionGetAdminOrderButtons',
            'moduleRoutes',
            'actionFrontControllerSetMedia',
            'actionAdminControllerSetMedia',
            'displayHeader'
        ];

        if (!parent::install() || !(bool)$this->registerHook($hooks)) {
            return false;
        }


        foreach (\Hsrdv::STATUSES as $status) {
            $this->addOrderState($status['title'], $status['color']);
        }


        if (null !== $this->getReparationRepository()) {
            $installed = $this->installDatabase();
        }

        if ($installed) {
            return true;
        }

        $this->uninstall();

        return false;
    }


    public function installDatabase()
    {
        $installed = true;

        $errorsCreation = $this->reparationRepository->createTables();
        $errorsFixtures = $this->reparationRepository->installFixtures();

        $errors = array_merge($errorsCreation, $errorsFixtures);
        if (!empty($errors)) {
            $this->addModuleErrors($errors);
            $installed = false;
        }

        return $installed;
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallTabs();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitHsrdvModule')) == true) {
            $output = $this->postProcess();
        }

        /*$this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');*/

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitHsrdvModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Working day start time'),
                        'name' => 'HSRDV_DAY_START',
                        'size' => 20,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Working day end time'),
                        'name' => 'HSRDV_DAY_END',
                        'size' => 20,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Timeslot duration (in minutes)'),
                        'name' => 'HSRDV_TIMESLOT_DURATION',
                        'size' => 20,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Working day break time'),
                        'name' => 'HSRDV_BREAK_TIME',
                        'size' => 20,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Working day break duration (in minutes)'),
                        'name' => 'HSRDV_BREAK_DURATION',
                        'size' => 20,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of available days for booking (in days)'),
                        'name' => 'HSRDV_AVAILABLE_DAYS_FOR_BOOKING',
                        'size' => 20,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of days before appointment when to send remainder mail'),
                        'name' => 'HSRDV_NB_DAYS_MAIL_REMAINDER',
                        'size' => 20,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of days after delivery date when to send satisafaction query'),
                        'name' => 'HSRDV_NB_DAYS_MAIL_SATISFACTION',
                        'size' => 20,
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'HSRDV_DAY_START' => Configuration::get('HSRDV_DAY_START'),
            'HSRDV_DAY_END' => Configuration::get('HSRDV_DAY_END'),
            'HSRDV_TIMESLOT_DURATION' => Configuration::get('HSRDV_TIMESLOT_DURATION'),
            'HSRDV_BREAK_TIME' => Configuration::get('HSRDV_BREAK_TIME'),
            'HSRDV_BREAK_DURATION' => Configuration::get('HSRDV_BREAK_DURATION'),
            'HSRDV_AVAILABLE_DAYS_FOR_BOOKING' => Configuration::get('HSRDV_AVAILABLE_DAYS_FOR_BOOKING'),
            'HSRDV_NB_DAYS_MAIL_REMAINDER' => Configuration::get('HSRDV_NB_DAYS_MAIL_REMAINDER'),
            'HSRDV_NB_DAYS_MAIL_SATISFACTION' => Configuration::get('HSRDV_NB_DAYS_MAIL_SATISFACTION')
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * @return ReparationRepository|null
     */
    private function getReparationRepository()
    {
        if (null === $this->reparationRepository) {
            try {
                $this->reparationRepository = $this->get('prestashop.module.hsrdv.reparation.repository');
            } catch (Throwable $e) {
                /** @var LegacyContext $context */
                $legacyContext = $this->get('prestashop.adapter.legacy.context');
                /** @var Context $shopContext */
                $shopContext = $this->get('prestashop.adapter.shop.context');

                $this->reparationRepository = new ReparationRepository(
                    $this->get('doctrine.dbal.default_connection'),
                    SymfonyContainer::getInstance()->getParameter('database_prefix'),
                    $legacyContext->getLanguages(true, $shopContext->getContextShopID()),
                    $this->get('translator')
                );
            }
        }

        return $this->reparationRepository;
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all)
            && $this->installTabs();
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all)
            && $this->uninstallTabs();
    }

    private function installTabs()
    {
        $return = true;
        $tabs = $this->getHsRdvTabs();
        $parentTabID = Tab::getIdFromClassName('AdminParentOrders');
        foreach ($tabs as $tab) {
            $subTab = new Tab();
            $subTab->class_name = $tab['class_name'];
            $subTab->id_parent = $parentTabID;
            $subTab->module = $this->name;
            $subTab->route_name = $tab['route_name'];
            foreach (Language::getLanguages(true) as $lang) {
                $subTab->name[$lang['id_lang']] = $this->trans($tab['name'], array(), 'Modules.Hsrdv.Admin', $lang['locale']);
            }
            $return &= $subTab->save();
        }

        return $return;
    }

    private function uninstallTabs()
    {
        $return = true;

        $tabs = $this->getHsRdvTabs();
        foreach ($tabs as $tab) {
            $subTabId = (int)Tab::getIdFromClassName($tab['class_name']);
            $subTab = new Tab($subTabId);
            $return &= $subTab->delete();
        }

        return $return;
    }

    private function getHsRdvTabs()
    {
        return [
            [
                'parent_class_name' => 'AdminParentOrders ',
                'class_name' => 'HsRdvCalendarController',
                'name' => 'Calendrier',
                'route_name' => 'admin_rdv_calendar'
            ],
            [
                'parent_class_name' => 'AdminParentOrders ',
                'class_name' => 'HsRdvTypeReparationController',
                'name' => 'Types Réparation',
                'route_name' => 'admin_type_reparation_list'
            ]
        ];
    }

    public function getWidgetVariables($hookName, array $configuration)
    {

    }


    public function renderWidget($hookName, array $configuration)
    {

    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/back.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/public/order.js', 'all');

    }


    public function hookActionFrontControllerSetMedia()
    {

    }

    public function hookDisplayHeader()
    {

    }

    public function hookModuleRoutes($params)
    {
        return $this->getModuleRoutes('ModuleRoutes', 'reparation');
    }

    public function getModuleRoutes($ModuleRoutes, $alias)
    {
        if ($ModuleRoutes == 'ModuleRoutes') {
            return array(
                'module-hsrdv-rdv' => array(
                    'controller' => 'rdv',
                    'rule' => $alias . '/rendez-vous',
                    'keywords' => array(),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'hsrdv',
                    ),
                ),
                'module-hsrdv-editRdv' => array(
                    'controller' => 'editRdv',
                    'rule' => $alias . '/editer-rendez-vous',
                    'keywords' => array(),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'hsrdv',
                    ),
                ),
                'module-hsrdv-calendar' => array(
                    'controller' => 'calendar',
                    'rule' => $alias . '/calendar',
                    'keywords' => array(),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'hsrdv',
                    ),
                ),
                'module-hsrdv-satisfaction' => array(
                    'controller' => 'satisfaction',
                    'rule' => $alias . '/enquete-satisfaction',
                    'keywords' => array(),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'hsrdv',
                    ),
                ),
                'module-hsrdv-livraison' => array(
                    'controller' => 'livraison',
                    'rule' => $alias . '/livraison',
                    'keywords' => array(),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'hsrdv',
                    ),
                ),
            );
        }
    }

    public function cronSendMailsEnqueteSatisfaction()
    {
        $preparedReparations = [];

        $reparations = Reparation::getReparationsForEnqueteSatisfaction(Configuration::get('HSRDV_NB_DAYS_MAIL_SATISFACTION'));

        foreach ($reparations as $reparation) {

            $customer = new Customer($reparation['id_customer']);
            $preparedReparations[] = [
                'id_reparation' => $reparation['id_reparation'],
                'email' => $customer->email,
                'token' => $reparation['token']
            ];

        }

        foreach ($preparedReparations as $preparedReparation) {

            $appareils = Appareil::getAppareilsFromIdReparation($preparedReparation['id_reparation']);
            $appareils_string = '';
            foreach ($appareils as $appareil) {
                $appareils_string = $appareils_string . $appareil['marque'] . $appareil['reference'] . ', ';
            }

            //var_dump($appareils_string );die;
            $linkInMail = $this->context->link->getModuleLink('hsrdv', 'satisfaction') . '?reparationToken=' . $preparedReparation['token'];
            $var_list = [
                '{email}' => $preparedReparation['email'],
                '{appareils_string}' => $appareils_string,
                '{link_mail}' => $linkInMail
            ];

            if ($preparedReparation['email']) {
                $sent = Mail::Send(
                    $this->context->language->id,
                    'hsrdv_enquete_satisfaction',
                    $this->trans('Comment vous trouvez la reparation: %appareils_name%?',
                        ['%appareils_name%' => $appareils_string],
                        'Modules.Hsrdv.ProcessRdvInitial'),
                    $var_list,
                    $preparedReparation['email'],
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
                        'Erreur envoi mail ',
                        ['%appareils_name%' => $appareils_string],
                        'Modules.Hsrdv.ProcessRdvInitial'
                    );
                }
            }
        }


        /*$this->initModel();
        $settings = $this->getSettings();
        $secure_key = $settings['security_token_key'];
        if ($secure_key && Tools::getValue('secure_key') == $secure_key && $this->model && $this->model->active) {
            $this->setTimeLimit();
            try {
                // Auto delete old posts if auto_delete_posts_older_than_days setting is set
                $this->deleteAllPostsOlderThanDays($settings['auto_delete_posts_older_than_days']);

                if ($this->model->auto_post_type == self::$POST_CATEGORY_PRODUCTS_CRON) {
                    // Get products by rule for posting
                    $products = $this->getProductsForAutoPost();

                    // If  products list is empty, start-over the cycle
                    if (empty($products) && $this->model->is_start_over_cycle) {
                        $this->model->restarted_at = date('Y-m-d H:i:s');
                        $products = $this->getProductsForAutoPost();
                    }

                    // Schedule products for posting
                    foreach ($products as $product) {
                        $this->scheduleProductForPosting($product['id_product']);
                    }
                } elseif ($this->model->auto_post_type == self::$POST_NEW_PRODUCTS_CRON) {
                    $this->scheduleNewProductForPosting();
                }

                // Post pending products starting with oldest one
                $this->postPendingProducts();
            } catch (Exception $e) {
                // Save cron error
                $this->model->addErrorLog('CRON: ' . $e->getMessage());
                if ($settings['debug_mode']) {
                    die($e->getMessage());
                }
            }

            $this->model->posted_at = date('Y-m-d H:i:s');
            $this->model->update();
        }*/
    }

    public function cronSendMailsRappelRendezVous()
    {
        //todo: security
        //todo: Rappeler seulement les appareils acceptés
        $preparedReparations = [];

        $idsReparation = Reparation::getReparationsForRappelRendezVous(Configuration::get('HSRDV_NB_DAYS_MAIL_REMAINDER'));
        foreach ($idsReparation as $idReparation) {
            $reparation = new Reparation((int)$idReparation['id_reparation']);
            $client = new Client($reparation->id_client);
            $preparedReparations[] = [
                'id_reparation' => $reparation->id_reparation,
                'email' => $client->email,
                'token' => $reparation->token,
                'id_client' => $reparation->id_client
            ];
        }


        foreach ($preparedReparations as $preparedReparation) {
            $appareils = Appareil::getAppareilsFromIdReparation($preparedReparation['id_reparation']);
            $appareilsListString = '';

            $lastAppareilKey = array_key_last($appareils);
            foreach ($appareils as $key => $appareil) {
                $appareilsListString = $appareilsListString . $appareil['marque'] . ' ' . $appareil['reference'];
                if ($lastAppareilKey != $key) {
                    $appareilsListString = $appareilsListString . ', ';
                }
            }

            $id_client = $preparedReparation['id_client'];
            $customer = new Customer($id_client);

            $linkInMail = $this->context->link->getModuleLink('hsrdv', 'satisfaction') . '?reparationToken=' . $preparedReparation['token'];
            $booking = Booking::getBookingsFromIdReparation($preparedReparation['id_reparation']);


            $time = new DateTime($booking[0]['time_booking']);
            $timeFormatted = $time->format("H:i");


            $langLocale = $this->context->language->locale;
            $explodeLocale = explode('-', $langLocale);
            $localeOfContextLanguage = $explodeLocale[0] . '_' . Tools::strtoupper($explodeLocale[1]);

            setlocale(LC_ALL, $localeOfContextLanguage . '.UTF-8', $localeOfContextLanguage);
            $dateFormatted = strftime("%d %B %Y", strtotime($booking[0]['date_booking']));

            $var_list = [
                '{nom}' => $customer->lastname,
                '{prenom}' => $client->firstname,
                '{liste_appareils}' => $appareilsListString,
                '{link_mail}' => $linkInMail,
                '{date}' => $dateFormatted,
                '{heure}' => $timeFormatted,
            ];

            if ($preparedReparation['email']) {
                $sent = Mail::Send(
                    $this->context->language->id,
                    'hsrdv_rappel_rendez_vous',
                    $this->trans('Rappel de rendez-vous le %date% à %heure%  -  [%id_reparation%]',
                        [
                            '%date%' => $dateFormatted,
                            '%heure%' => $timeFormatted,
                            '%id_reparation%' => $preparedReparation['id_reparation']
                        ],
                        'Modules.Hsrdv.ProcessRdvInitial'),
                    $var_list,
                    $preparedReparation['email'],
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
                        'Erreur envoi mail ',
                        [],
                        'Modules.Hsrdv.ProcessRdvInitial'
                    );
                }
            }
        }
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
        parent::_clearCache($this->templateFileColumn);
    }

    /**
     * Render a twig template.
     */
    private function render(string $template, array $params = []): string
    {
        /** @var Twig_Environment $twig */
        $twig = $this->get('twig');

        return $twig->render($template, $params);
    }

    /**
     * Get path to this module's template directory
     */
    private function getModuleTemplatePath(): string
    {
        return sprintf('@Modules/%s/views/templates/admin/', $this->name);
    }

    /**
     * Displays First decision form
     */
    public function hookDisplayAdminOrderMain(array $params)
    {
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

        //var_dump($finalStatuses);die;
        /** @var OrderSignatureRepository $signatureRepository */
       /* $signatureRepository = $this->get(
            'prestashop.module.demovieworderhooks.repository.order_signature_repository'
        );*/

        /** @var OrderSignaturePresenter $signaturePresenter */
        /*$signaturePresenter = $this->get(
            'prestashop.module.demovieworderhooks.presenter.order_signature_presenter'
        );

        $signature = $signatureRepository->findOneByOrderId($params['id_order']);

        if (!$signature) {
            return '';
        }*/

       /* return $this->render($this->getModuleTemplatePath() . 'customer_signature.html.twig', [
            //'signature' => $signaturePresenter->present($signature, (int) $this->context->language->id),
        ]);*/
        $errors = [];
        try {
            $order = new Order((int)$params['id_order']);
            $presentedReparation = [];

            $entityManager = $this->get('doctrine.orm.entity_manager');
            $reparationRepository = $entityManager->getRepository(\PrestaShop\Module\HsRdv\Entity\Reparation::class);
            $reparation = $reparationRepository->findOneBy(['idOrder'=> (int)$params['id_order']]);

            $appareilRepository = $entityManager->getRepository(\PrestaShop\Module\HsRdv\Entity\Appareil::class);
            $appareils = $appareilRepository->findBy(['id_reparation'=> $reparation->getId()]);

            foreach ($appareils as $appareil) {
                $presentedReparation['appareils'][] = [
                    'id_appareil' => $appareil->getId(),
                    'marque' => $appareil->getMarque(),
                    'reference' => $appareil->getReference(),
                    'descriptif_panne' => $appareil->getDescriptifPanne(),
                    'decision' => $appareil->getDecision()
                ];
            }

            /*$devisRepository = $entityManager->getRepository(Devis::class);
            $devis = $devisRepository->findOneBy(['id_reparation'=> $reparation->getId()]);
            $presentedReparation['devis']=[];
            if ($devis) {
                $devisLigneRepository = $entityManager->getRepository(DevisLigne::class);
                $devisLignes = $devisLigneRepository->findBy(['id_devis'=> $devis->getId()]);

                $presentedReparation['devis']['acompte'] = $devis->getAcompte();
                $presentedReparation['devis']['remarques_specifiques'] = $devis->getRemarquesSpecifiques();
                $presentedReparation['devis']['devis_lignes'] = [];
                foreach ($devisLignes as $ligne) {
                    $presentedReparation['devis']['devis_lignes'][] = [
                        'id_devis_ligne' => $ligne->getId(),
                        'price' => $ligne->getPrice(),
                        'name_type_reparation' => $ligne->getNameTypeReparation(),
                        'id_type_reparation' => $ligne->getIdTypeReparation(),
                        'id_appareil' => $ligne->getIdAppareil()
                    ];
                }
            }*/

            $presentedReparation['id_reparation'] = $reparation->getId();
            $presentedReparation['mode_livraison'] = $reparation->getModeLivraison();
            $presentedReparation['date_reparation'] = $reparation->getDateReparation();
            $presentedReparation['date_livraison'] = $reparation->getDateLivraison();

            $status = new OrderState((int)$order->current_state);

            $presentedReparation['status'] = [
                'id_status' => $status->id,
                'message' => $status->name,
                'color' => $status->color
            ];

            $typeReparationReparation = $entityManager->getRepository(TypeReparation::class);
            $typesReparation = $typeReparationReparation->findAll();

            return $this->render('@Modules/hsrdv/views/templates/admin/reparation/show.html.twig', [
                'id_order' => $params['id_order'],
                'presented_reparation' => $presentedReparation,
                'types_reparation' => $typesReparation,
                'statuses' => $finalStatuses,
                'initial_decision_form_action' => $this->get('router')->generate('admin_rdv_reparation_inital_decision'),
                'prise_en_charge_decision_form_action' => $this->get('router')->generate('admin_rdv_reparation_prise_en_charge_decision'),
                'etat_reparation_form_action' => $this->get('router')->generate('admin_rdv_reparation_etat_reparation'),
                'etat_livraison_form_action' => $this->get('router')->generate('admin_rdv_reparation_etat_livraison'),
                'generation_devis_form_action' => $this->get('router')->generate('admin_rdv_reparation_generer_devis')
            ]);
            //return new Response($presenter->present());

        } catch (DatabaseException $e) {
            $errors[] = [
                'key' => 'Could not find #%i',
                'domain' => 'Admin.Catalog.Notification',
                'parameters' => [$reparation->getId()],
            ];
        }
    }

    public function addOrderState($title, $color)
    {
        $state_exist = false;
        $states = OrderState::getOrderStates((int)$this->context->language->id);

        // check if order state exist
        foreach ($states as $state) {
            if (in_array($title, $state)) {
                $state_exist = true;
                break;
            }
        }

        // If the state does not exist, we create it.
        if (!$state_exist) {
            // create new order state
            $order_state = new OrderState();
            $order_state->color = $color;
            $order_state->send_email = false;
            $order_state->module_name = $this->name;
/*            $order_state->template = 'name of your email template';*/
            $order_state->name = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
                $order_state->name[ $language['id_lang'] ] = $title;

            // Update object
            $order_state->add();
        }

        return true;
    }
}