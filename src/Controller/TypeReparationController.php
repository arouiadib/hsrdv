<?php

namespace PrestaShop\Module\HsRdv\Controller;

use PrestaShop\Module\HsRdv\Core\Search\Filters\TypeReparationFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;


class TypeReparationController extends FrameworkBundleAdminController
{
    public function listAction(Request $request)
    {
        $filtersParams = $this->buildFiltersParamsByRequest($request);

        /** @var TypeReparationGridFactory $typeReparationGridFactory */
        $typeReparationGridFactory = $this->get('prestashop.module.hsrdv.type_reparation.grid.factory');
        $grid = $typeReparationGridFactory->getGrid($filtersParams);
        $presentedGrid = $this->presentGrid($grid);

        return $this->render('@Modules/hsrdv/views/templates/admin/type_reparation/list.html.twig', [
            'grid' => $presentedGrid,
            //'enableSidebar' => true,
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            //'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    public function createAction(Request $request) {

        $form = $this->get('prestashop.module.hsrdv.type_reparation.form_handler')->getForm();

        return $this->render('@Modules/hsrdv/views/templates/admin/type_reparation/form.html.twig', [
            'typeReparationForm' => $form->createView(),
            'enableSidebar' => true,
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    public function editAction(Request $request, $typeReparationId)
    {
        $this->get('prestashop.module.hsrdv.type_reparation.form_provider')->setIdtypeReparation($typeReparationId);

        $form = $this->get('prestashop.module.hsrdv.type_reparation.form_handler')->getForm();

        return $this->render('@Modules/hsrdv/views/templates/admin/type_reparation/form.html.twig', [
            'typeReparationForm' => $form->createView(),
            'enableSidebar' => true,
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    public function createProcessAction(Request $request)
    {
        return $this->processForm($request, 'Successful creation.');
    }

    public function editProcessAction(Request $request, $typeReparationId)
    {
        return $this->processForm($request, 'Successful update.', $typeReparationId);
    }

    public function deleteAction($typeReparationId)
    {
        $repository = $this->get('prestashop.module.hsrdv.type_reparation.repository');
        $errors = [];
        try {
            $repository->delete($typeReparationId);
            // todo: set to Null values in devis_ligne table
        } catch (DatabaseException $e) {
            $errors[] = [
                'key' => 'Could not delete #%i',
                'domain' => 'Admin.Catalog.Notification',
                'parameters' => [$typeReparationId],
            ];
        }

        if (0 === count($errors)) {
            $this->clearModuleCache();
            $this->addFlash('success', $this->trans('Successful deletion.', 'Admin.Notifications.Success'));
        } else {
            $this->flashErrors($errors);
        }

        return $this->redirectToRoute('admin_type_reparation_list');
    }

    private function processForm(Request $request, $successMessage, $typeReparationId = null)
    {
        /** @var LinkBlockFormDataProvider $formProvider */
        $formProvider = $this->get('prestashop.module.hsrdv.type_reparation.form_provider');
        $formProvider->setIdtypeReparation($typeReparationId);

        /** @var FormHandlerInterface $formHandler */
        $formHandler = $this->get('prestashop.module.hsrdv.type_reparation.form_handler');
        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $saveErrors = $formHandler->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans($successMessage, 'Admin.Notifications.Success'));

                return $this->redirectToRoute('admin_type_reparation_list');
            }

            $this->flashErrors($saveErrors);
        }

        return $this->render('@Modules/hsrdv/views/templates/admin/type_reparation/form.html.twig', [
            'typeReparationForm' => $form->createView(),
            'enableSidebar' => true,
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    /**
     * Gets the header toolbar buttons.
     *
     * @return array
     */
    private function getToolbarButtons()
    {
        return [
            'add' => [
                'href' => $this->generateUrl('admin_type_reparation_create'),
                'desc' => $this->trans('Nouveau Type Réparation', 'Modules.Hsrdv.Admin'),
                'icon' => 'add_circle_outline',
            ],
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function buildFiltersParamsByRequest(Request $request)
    {
        $filtersParams = array_merge(TypeReparationFilters::getDefaults(), $request->query->all());
        //$filtersParams['filters']['id_lang'] = $this->getContext()->language->id;

        return $filtersParams;
    }

    /**
     * Clear module cache.
     */
    private function clearModuleCache()
    {
        $this->get('prestashop.module.hsrdv.cache')->clearModuleCache();
    }
}
