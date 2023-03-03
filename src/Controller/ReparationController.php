<?php

namespace PrestaShop\Module\HsRdv\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use PrestaShop\Module\HsRdv\Core\Search\Filters\ReparationFilters;
use PrestaShop\Module\HsRdv\Entity\Reparation;
use PrestaShop\Module\HsRdv\Entity\Status;
use PrestaShop\Module\HsRdv\Entity\Appareil;
//todo remove client
use PrestaShop\Module\HsRdv\Entity\Client;
use PrestaShop\Module\HsRdv\Entity\Devis;
use PrestaShop\Module\HsRdv\Entity\DevisLigne;
use PrestaShop\Module\HsRdv\Entity\TypeReparation;
use Hrdv;
use Mail;
use DateTime;
use Customer;
use OrderState;
use Order;
use Hsrdv;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShop\Module\HsRdv\Calendar\Calendar;

/**
 * Class ReparationController.
 *
 * @ModuleActivated(moduleName="hsrdv", redirectRoute="admin_module_manage")
 */
class ReparationController extends FrameworkBundleAdminController
{
    const RDV_STATUSES = [
        1 => 'Demande de réparation',
        2 => 'Prise de rendez-vous',
        3 => 'Réparation refusée',
        4 => 'Rendez-vous pris',
        5 => 'Réparation en cours',
        6 => 'Non pris en charge',
        7 => 'Réparé',
        8 => 'A Livrer',
        9 => 'Livré',
        10 => 'Enquête de satisfaction'
    ];
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showAction($reparationId)
    {
        $errors = [];
        try {
            $presentedReparation = [];

            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $reparationRepository = $entityManager->getRepository(Reparation::class);
            $reparation = $reparationRepository->find($reparationId);

            $statusRepository = $entityManager->getRepository(Status::class);
            $status = $statusRepository->findOneBy(['id'=> $reparation->getIdStatus()]);

            $appareilRepository = $entityManager->getRepository(Appareil::class);
            $appareils = $appareilRepository->findBy(['id_reparation'=> $reparation->getId()]);

            $customer = new Customer((int)$reparation->getIdClient());

            foreach ($appareils as $appareil) {
                $presentedReparation['appareils'][] = [
                    'id_appareil' => $appareil->getId(),
                    'marque' => $appareil->getMarque(),
                    'reference' => $appareil->getReference(),
                    'descriptif_panne' => $appareil->getDescriptifPanne(),
                    'decision' => $appareil->getDecision()
                ];
            }

            $devisRepository = $entityManager->getRepository(Devis::class);
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
            }

            $presentedReparation['id_reparation'] = $reparationId;
            $presentedReparation['date_demande'] = $reparation->getDateDemande();
            $presentedReparation['mode_livraison'] = $reparation->getModeLivraison();
            $presentedReparation['date_reparation'] = $reparation->getDateReparation();
            $presentedReparation['date_livraison'] = $reparation->getDateLivraison();

            if(!isset($status)) {
               $status = new Status();
            }

            $presentedReparation['status'] = [
                'id_status' => $status->getId(),
                'message' => $status->getMessage(),
                'color' => $status->getColor()
            ];

            $presentedReparation['client'] = [
                'nom' => $customer->lastname,
                'prenom' => $customer->firstname,
                'email' => $customer->email,
                'phone' => '',
                'addresse_postale' => ''
                // todo: get these from address object
                //'phone' => $customer->phone,
                //'addresse_postale' => $customer->getAddressePostale()
            ];


            $typeReparationReparation = $entityManager->getRepository(TypeReparation::class);
            $typesReparation = $typeReparationReparation->findAll();

            //var_dump($typesReparation);die;

            return $this->render('@Modules/hsrdv/views/templates/admin/reparation/show.html.twig', [
                'presented_reparation' => $presentedReparation,
                'types_reparation' => $typesReparation,
                'initial_decision_form_action' => $this->generateUrl('admin_rdv_reparation_inital_decision'),
                'prise_en_charge_decision_form_action' => $this->generateUrl('admin_rdv_reparation_prise_en_charge_decision'),
                'etat_reparation_form_action' => $this->generateUrl('admin_rdv_reparation_etat_reparation'),
                'etat_livraison_form_action' => $this->generateUrl('admin_rdv_reparation_etat_livraison'),
                'generation_devis_form_action' => $this->generateUrl('admin_rdv_reparation_generer_devis')
            ]);
            //return new Response($presenter->present());

        } catch (DatabaseException $e) {
            $errors[] = [
                'key' => 'Could not find #%i',
                'domain' => 'Admin.Catalog.Notification',
                'parameters' => [$reparationId],
            ];
        }
    }


    public function listAction(Request $request)
    {
        $filtersParams = $this->buildFiltersParamsByRequest($request);

        /** @var CommandeGridFactory $reparationGridFactory */
        $reparationGridFactory = $this->get('prestashop.module.hsrdv.grid.factory');
        $grid = $reparationGridFactory->getGrid($filtersParams);
        $presentedGrid = $this->presentGrid($grid);

        return $this->render('@Modules/hsrdv/views/templates/admin/list.html.twig', [
            'grid' => $presentedGrid,
            //'enableSidebar' => true,
            //'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            //'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    public function initialDecisionAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {
            $appareils = !is_null($request->request->get('appareils')) ? $request->request->get('appareils') : [];

            if (count($appareils) == 0)
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'No decision taken'),
                    400);
            }

            $idReparation = (int)$request->request->get('id_reparation');
            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $appareilRepository = $entityManager->getRepository(Appareil::class);
            $appareilsDb = $appareilRepository->findBy(['id_reparation'=> $idReparation]);

            if (count($appareils) != count($appareilsDb))
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Missing appareil decision'),
                    400);
            }
            // Persist appareils decisions
            foreach ($appareilsDb as $key => $appareilDb) {
                foreach ($appareils as $k => $appareilDecision) {
                    if ($k === $appareilDb->getId()) {
                        if ($appareilDecision === 'Oui') {
                            $appareilDb->setDecision(true);
                        } else {
                            $appareilDb->setDecision(false);
                        };
                        $entityManager->flush();
                    }
                }
            }

            $idsAppareilsOui = array_keys($appareils, "Oui");
            $idsAppareilsNon = array_keys($appareils, "Non");

            $reparationRepository = $entityManager->getRepository(Reparation::class);
            $statusRepository = $entityManager->getRepository(Status::class);
            $appareilRepository = $entityManager->getRepository(Appareil::class);
            $reparation = $reparationRepository->find($idReparation);

            $id_customer = $reparation->getIdClient();
            $customer = new Customer((int)$id_customer);

            if (!$customer)
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'customer not found'),
                    400);
            }
            $from = $customer->email;


            if  (count($idsAppareilsOui) <= 0) {
                // Email Non
                $reparation->setIdStatus(\Hsrdv::RDV_REFUSE);
                $status = $statusRepository->findOneBy(['id'=> \Hsrdv::RDV_REFUSE]);


                $appareilsDb = $appareilRepository->findBy(['id_reparation'=> $idReparation]);
                $appareilsListString = '';

                $lastAppareilKey = array_key_last($appareilsDb);
                foreach ($appareilsDb as $key => $appareilDb) {
                    $appareilsListString = $appareilsListString . $appareilDb->getMarque() . ' ' . $appareilDb->getReference();
                    if ($lastAppareilKey != $key)
                    {
                        $appareilsListString = $appareilsListString . ', ';
                    }
                }

                $var_list = [
                    '{liste_appareils}' => $appareilsListString
                ];

                $sent = Mail::Send(
                    $this->getContext()->language->id,
                    'hsrdv_rendez_vous_refuse',
                    $this->trans('Demande de réparation déclinée - ID: %id_reparation%', 'Modules.Hsrdv.Shop', ['%id_reparation%' => $idReparation ]),
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

            } else {
                $status = $statusRepository->findOneBy(['id'=> \Hsrdv::PRISE_RDV]);
                $reparation->setIdStatus(\Hsrdv::PRISE_RDV);
                if (count($idsAppareilsOui) == count($appareils))
                {
                    $reparationToken = $reparation->getToken();
                    $linkInMail = $this->getContext()->link->getModuleLink('hsrdv', 'calendar')
                        . '?reparationToken=' . $reparationToken
                        // Here we use current month, but it should be the first month that has availabity
                        // todo: add method getFisrtMonthWithAvailabitity()
                        . '&month=' . idate('m')
                        . '&year=' . idate('y')
                    ;

                    $appareils = $appareilRepository->findBy(['id_reparation'=> $reparation->getId()]);
                    $appareilsListString = '';

                    $lastAppareilKey = array_key_last($appareils);
                    foreach ($appareils as $key => $appareil) {
                        $appareilsListString = $appareilsListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                        if ($lastAppareilKey != $key)
                        {
                            $appareilsListString = $appareilsListString . ', ';
                        }
                    }

                    $var_list = [
                        '{liste_appareils}' => $appareilsListString,
                        '{link_mail}' => $linkInMail
                    ];

                    $sent = Mail::Send(
                        $this->getContext()->language->id,
                        'hsrdv_acceptation_prise_rendez_vous',
                        $this->trans('Demande de réparation acceptée - %id_reparation%', 'Modules.Hsrdv.Shop', ['%id_reparation%' => $idReparation ]),
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

                } elseif ( count($idsAppareilsOui) < count($appareils)) {

                    $reparationToken = $reparation->getToken();
                    $linkInMail = $this->getContext()->link->getModuleLink('hsrdv', 'calendar')
                        . '?reparationToken=' . $reparationToken
                        . '&month=' . idate('m')
                        . '&year=' . idate('y')
                    ;

                    $appareilsOuiListString = $appareilsNonListString = '';

                    $appareilsOui = $appareilRepository->findBy(['id'=> $idsAppareilsOui]);
                    $appareilsNon = $appareilRepository->findBy(['id'=> $idsAppareilsNon]);


                    $lastAppareilOuiKey = array_key_last($appareilsOui);
                    foreach ($appareilsOui as $key => $appareil) {
                        $appareilsOuiListString = $appareilsOuiListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                        if ($lastAppareilOuiKey != $key)
                        {
                            $appareilsOuiListString = $appareilsOuiListString . ', ';
                        }
                    }

                    $lastAppareilNonKey = array_key_last($appareilsNon);
                    foreach ($appareilsNon as $key => $appareil) {
                        $appareilsNonListString = $appareilsNonListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                        if ($lastAppareilNonKey != $key)
                        {
                            $appareilsNonListString = $appareilsNonListString . ', ';
                        }
                    }

                    $var_list = [
                        '{liste_appareils_oui}' => $appareilsOuiListString,
                        '{liste_appareils_non}' => $appareilsNonListString,
                        '{link_mail}' => $linkInMail
                    ];

                    $sent = Mail::Send(
                        $this->getContext()->language->id,
                        'hsrdv_acceptation_prise_rendez_vous_partielle',
                        $this->trans('Demande de réparation partiellement acceptée - ID: %id_reparation%', 'Modules.Hsrdv.Shop', ['%id_reparation%' => $idReparation ]),
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
                }
            }

            if (!$sent)
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Mail not successfuly sent'),
                    400);
            }

            $entityManager->persist($reparation);
            $entityManager->flush();

            $rdvStatus = [
                'message' => $status->getMessage(),
                'color' => $status->getColor()
            ];

            return new JsonResponse(array(
                    'status' => 'OK',
                    'rdv_status' => $rdvStatus,
                    'message' => []),
                200);
        }

        return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
            400);

    }


    public function initialDecisionBisAction(Request $request)
    {
        if(isset($request->request))
        {
            $states = $this->getOrderStatuses();

            $orderId = $request->request->get('id_order');
            $order = new Order((int)$orderId);
            $appareils = !is_null($request->request->get('appareils')) ? $request->request->get('appareils') : [];

           /* if (count($appareils) == 0)
            {
                $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'No decision taken'),
                    400);
            }*/

            $idReparation = (int)$request->request->get('id_reparation');
            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $appareilRepository = $entityManager->getRepository(Appareil::class);
            $appareilsDb = $appareilRepository->findBy(['id_reparation'=> $idReparation]);

/*            if (count($appareils) != count($appareilsDb))
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Missing appareil decision'),
                    400);
            }*/
            // Persist appareils decisions
            foreach ($appareilsDb as $key => $appareilDb) {
                foreach ($appareils as $k => $appareilDecision) {
                    if ($k === $appareilDb->getId()) {
                        if ($appareilDecision === 'Oui') {
                            $appareilDb->setDecision(true);
                        } else {
                            $appareilDb->setDecision(false);
                        };
                        $entityManager->flush();
                    }
                }
            }

            $idsAppareilsOui = array_keys($appareils, "Oui");
            $idsAppareilsNon = array_keys($appareils, "Non");

            $reparationRepository = $entityManager->getRepository(Reparation::class);
            $appareilRepository = $entityManager->getRepository(Appareil::class);
            $reparation = $reparationRepository->find($idReparation);

            $id_customer = $reparation->getIdClient();
            $customer = new Customer((int)$id_customer);

            if (!$customer)
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'customer not found'),
                    400);
            }
            $from = $customer->email;


            if  (count($idsAppareilsOui) <= 0) {
                // Email Non
                $reparation->setIdStatus(\Hsrdv::RDV_REFUSE);
                $order->current_state = $states['RDV_REFUSE'];

                $appareilsDb = $appareilRepository->findBy(['id_reparation'=> $idReparation]);
                $appareilsListString = '';

                $lastAppareilKey = array_key_last($appareilsDb);
                foreach ($appareilsDb as $key => $appareilDb) {
                    $appareilsListString = $appareilsListString . $appareilDb->getMarque() . ' ' . $appareilDb->getReference();
                    if ($lastAppareilKey != $key)
                    {
                        $appareilsListString = $appareilsListString . ', ';
                    }
                }

                $var_list = [
                    '{liste_appareils}' => $appareilsListString
                ];

                $sent = Mail::Send(
                    $this->getContext()->language->id,
                    'hsrdv_rendez_vous_refuse',
                    $this->trans('Demande de réparation déclinée - ID: %id_reparation%', 'Modules.Hsrdv.Shop', ['%id_reparation%' => $idReparation ]),
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

            } else {
                $reparation->setIdStatus(\Hsrdv::PRISE_RDV);
                $order->current_state = $states['PRISE_RDV'];
                if (count($idsAppareilsOui) == count($appareils))
                {
                    $reparationToken = $reparation->getToken();
                    $linkInMail = $this->getContext()->link->getModuleLink('hsrdv', 'calendar')
                        . '?reparationToken=' . $reparationToken
                        // Here we use current month, but it should be the first month that has availabity
                        // todo: add method getFisrtMonthWithAvailabitity()
                        . '&month=' . idate('m')
                        . '&year=' . idate('y')
                    ;

                    $appareils = $appareilRepository->findBy(['id_reparation'=> $reparation->getId()]);
                    $appareilsListString = '';

                    $lastAppareilKey = array_key_last($appareils);
                    foreach ($appareils as $key => $appareil) {
                        $appareilsListString = $appareilsListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                        if ($lastAppareilKey != $key)
                        {
                            $appareilsListString = $appareilsListString . ', ';
                        }
                    }

                    $var_list = [
                        '{liste_appareils}' => $appareilsListString,
                        '{link_mail}' => $linkInMail
                    ];

                    $sent = Mail::Send(
                        $this->getContext()->language->id,
                        'hsrdv_acceptation_prise_rendez_vous',
                        $this->trans('Demande de réparation acceptée - %id_reparation%', 'Modules.Hsrdv.Shop', ['%id_reparation%' => $idReparation ]),
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

                } elseif ( count($idsAppareilsOui) < count($appareils)) {

                    $reparationToken = $reparation->getToken();
                    $linkInMail = $this->getContext()->link->getModuleLink('hsrdv', 'calendar')
                        . '?reparationToken=' . $reparationToken
                        . '&month=' . idate('m')
                        . '&year=' . idate('y')
                    ;

                    $appareilsOuiListString = $appareilsNonListString = '';

                    $appareilsOui = $appareilRepository->findBy(['id'=> $idsAppareilsOui]);
                    $appareilsNon = $appareilRepository->findBy(['id'=> $idsAppareilsNon]);


                    $lastAppareilOuiKey = array_key_last($appareilsOui);
                    foreach ($appareilsOui as $key => $appareil) {
                        $appareilsOuiListString = $appareilsOuiListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                        if ($lastAppareilOuiKey != $key)
                        {
                            $appareilsOuiListString = $appareilsOuiListString . ', ';
                        }
                    }

                    $lastAppareilNonKey = array_key_last($appareilsNon);
                    foreach ($appareilsNon as $key => $appareil) {
                        $appareilsNonListString = $appareilsNonListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                        if ($lastAppareilNonKey != $key)
                        {
                            $appareilsNonListString = $appareilsNonListString . ', ';
                        }
                    }

                    $var_list = [
                        '{liste_appareils_oui}' => $appareilsOuiListString,
                        '{liste_appareils_non}' => $appareilsNonListString,
                        '{link_mail}' => $linkInMail
                    ];

                    $sent = Mail::Send(
                        $this->getContext()->language->id,
                        'hsrdv_acceptation_prise_rendez_vous_partielle',
                        $this->trans('Demande de réparation partiellement acceptée - ID: %id_reparation%', 'Modules.Hsrdv.Shop', ['%id_reparation%' => $idReparation ]),
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
                }
            }

            if (!$sent)
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Mail not successfuly sent'),
                    400);
            }

            $entityManager->persist($reparation);
            $entityManager->flush();

            $order->update();

            return $this->redirectToRoute('admin_orders_view', [
                'orderId' => $orderId,
            ]);
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);

    }
    public function priseEnChargeDecisionAction(Request $request)
    {

/*        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }*/

        if(isset($request->request))
        {
            $decision_prise_en_charge = $request->request->get('decision_prise_en_charge');

            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $reparationRepository = $entityManager->getRepository(Reparation::class);
            $appareilRepository = $entityManager->getRepository(Appareil::class);

            $reparation = $reparationRepository->find((int)$request->request->get('id_reparation'));

            $id_client = $reparation->getIdClient();
            $customer = new Customer((int)$id_client);
            $from = $customer->email;

            $appareils = $appareilRepository->findBy(['id_reparation'=> $reparation->getId()]);
            $appareilsListString = '';
            $lastAppareilKey = array_key_last($appareils);
            foreach ($appareils as $key => $appareil) {
                $appareilsListString = $appareilsListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                if ($lastAppareilKey != $key)
                {
                    $appareilsListString = $appareilsListString . ', ';
                }
            }

            //todo: Only liste appareils oui
            $var_list = [
                '{email}' =>  $from,
                '{liste_appareils}' => $appareilsListString,
            ];

            $idOrder = $reparation->getIdOrder();
            $order = new Order((int)$idOrder);

            $states = $this->getOrderStatuses();

            if  ($decision_prise_en_charge === 'Oui') {
                // Email Non
                $reparation->setIdStatus(\Hsrdv::REPARATION_EN_COURS);
                $order->current_state = $states['REPARATION_EN_COURS'];
                $sent = Mail::Send(
                    $this->getContext()->language->id,
                    'hsrdv_reparation_en_cours',
                    $this->trans('La réparation est en cours',
                        'Modules.Hsrdv.Shop'),
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

            } else {
                $reparation->setIdStatus(\Hsrdv::NON_PRIS_EN_CHARGE);
                $order->current_state = $states['NON_PRIS_EN_CHARGE'];
                $sent = Mail::Send(
                    $this->getContext()->language->id,
                    'hsrdv_non_pris_en_charge',
                    $this->trans('Votre appareil n\'est pas pris en charge!',
                        'Modules.Hsrdv.Shop'),
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
            }

            if (!$sent)
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Mail not successfuly sent'),
                    400);
            }

            $entityManager->persist($reparation);
            $entityManager->flush();
            $order->update();

            return $this->redirectToRoute('admin_orders_view', [
                'orderId' => $idOrder,
            ]);

           /* return new JsonResponse(array(
                    'status' => 'OK',
                    'rdv_status' => $rdvStatus,
                    'prise_en_charge' => $decision_prise_en_charge === 'Oui' ? 1 : 0,
                    'message' => []
                ),
                200);*/
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);

    }

    public function genererDevisAction(Request $request)
    {

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {

            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $acompte = (int)$request->request->get('acompte');
            $remarques_specifiques = trim($request->request->get('remarques_specifiques'));
            $id_reparation = $request->request->get('id_reparation');
            $lines =  $request->request->get('lines');
            $lines = isset($lines) ? $lines : [];
            $reparationRepository = $entityManager->getRepository(Reparation::class);
            $reparation = $reparationRepository->find((int)$request->request->get('id_reparation'));

            $devisRepository = $entityManager->getRepository(Devis::class);
            $devis = $devisRepository->findOneBy(['id_reparation'=> $id_reparation]);

            if (!$devis) {
                $devis = new Devis();

            }

            $devis->setIdReparation($id_reparation);
            $devis->setAcompte($acompte);
            $devis->setRemarquesSpecifiques(trim($remarques_specifiques));
            $entityManager->persist($devis);

            // if no devis, new devis
            /*var_dump($devis); die;*/
            $devisLigneRepository = $entityManager->getRepository(DevisLigne::class);
            $devisLignes = $devisLigneRepository->findBy(['id_devis'=> $devis->getId()]);

/*            echo "<pre>";
            var_dump($devisLignes);*/
            foreach ($devisLignes as $ligne) {
                $entityManager->remove($ligne);
            }
            $entityManager->flush();
            $devisLignes = $devisLigneRepository->findBy(['id_devis'=> $devis->getId()]);
            //var_dump($devisLignes); die;

            foreach ($lines as $line) {
                $ligneDevis = new DevisLigne();
                $ligneDevis->setPrice($line['price']);
                $ligneDevis->setIdAppareil($line['appareil']);
                $ligneDevis->setIdTypeReparation($line['id_type_reparation']);
                $ligneDevis->setIdDevis($devis->getId());

                $typeReparationRepository = $entityManager->getRepository(TypeReparation::class);
                $typeReparation = $typeReparationRepository->findOneBy(['id' => $line['id_type_reparation']]);
                $ligneDevis->setNameTypeReparation($typeReparation->getName());
                $entityManager->persist($ligneDevis);
                $entityManager->flush();
            }

            //$entityManager->flush();
            // Persist Form data
            // Send mail + pdf joint
            // Download pdf

            return new JsonResponse(array(
                'status' => 'OK',
               /* 'rdv_status' => $rdvStatus,*/
                'message' => []),
                200);
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);

    }

    public function etatReparationAction(Request $request)
    {

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {

            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $reparationRepository = $entityManager->getRepository(Reparation::class);
            $statusRepository = $entityManager->getRepository(Status::class);
            $appareilRepository = $entityManager->getRepository(Appareil::class);

            $reparation = $reparationRepository->find((int)$request->request->get('id_reparation'));

            $reparation->setIdStatus(\Hsrdv::REPARE);
            $reparation->setDateReparation(new \DateTime());
            $status = $statusRepository->findOneBy(['id'=> \Hsrdv::REPARE]);

            $id_client = $reparation->getIdClient();
            $customer = new Customer((int)$id_client);
            $from = $customer->email;

            $reparationToken = $reparation->getToken();
            $linkInMail = $this->getContext()->link->getModuleLink('hsrdv', 'livraison'). '?reparationToken=' . $reparationToken;

            $appareils = $appareilRepository->findBy(['id_reparation'=> $reparation->getId()]);
            $appareilsListString = '';

            $lastAppareilKey = array_key_last($appareils);
            foreach ($appareils as $key => $appareil) {
                $appareilsListString = $appareilsListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                if ($lastAppareilKey != $key)
                {
                    $appareilsListString = $appareilsListString . ', ';
                }
            }


            $var_list = [
                '{email}' =>  $from,
                '{liste_appareils}' => $appareilsListString,
                '{link_mail}' => $linkInMail
            ];

            $sent = Mail::Send(
                $this->getContext()->language->id,
                'hsrdv_repare',
                $this->trans('La réparation est achevée',
                    'Modules.Hsrdv.Shop'),
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

            if (!$sent)
            {
                return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Mail not successfuly sent'),
                    400);
            }

            $entityManager->persist($reparation);
            $entityManager->flush();

            $rdvStatus = [
                'message' => $status->getMessage(),
                'color' => $status->getColor()
            ];

            return new JsonResponse(
                                array(
                                    'status' => 'OK',
                                    'rdv_status' => $rdvStatus,
                                    'message' => []
                                ),
                                200);
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);

    }


    public function etatLivraisonAction(Request $request)
    {

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {
            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $reparationRepository = $entityManager->getRepository(Reparation::class);
            $statusRepository = $entityManager->getRepository(Status::class);

            $reparation = $reparationRepository->find((int)$request->request->get('id_reparation'));

            $reparation->setIdStatus(\Hsrdv::LIVRE);
            $reparation->setDateLivraison(new \DateTime());
            $status = $statusRepository->findOneBy(['id'=> \Hsrdv::LIVRE]);

            $entityManager->persist($reparation);
            $entityManager->flush();

            $rdvStatus = [
                'message' => $status->getMessage(),
                'color' => $status->getColor()
            ];

            return new JsonResponse(array(
                'status' => 'OK',
                'rdv_status' => $rdvStatus,
                'message' => []),
                200);
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);

    }
    /**
     * @param Request $request
     *
     * @return array
     */
    private function buildFiltersParamsByRequest(Request $request)
    {
        $filtersParams = array_merge(ReparationFilters::getDefaults(), $request->query->all());
        $filtersParams['filters']['id_lang'] = $this->getContext()->language->id;

        return $filtersParams;
    }


    /**
     * Gets the header toolbar buttons.
     *
     * @return array
     */
    private function getToolbarButtons()
    {
        return [
        ];
    }

    private function getOrderStatuses() {
        $finalStatuses = [];
        $statuses = Hsrdv::STATUSES;
        $dbStatuses = OrderState::getOrderStates($this->getContext()->language->id);

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
