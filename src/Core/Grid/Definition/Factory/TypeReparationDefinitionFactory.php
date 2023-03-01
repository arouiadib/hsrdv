<?php

namespace PrestaShop\Module\HsRdv\Core\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;

/**
 * Class TypeReparationDefinitionFactory.
 */
final class TypeReparationDefinitionFactory extends AbstractGridDefinitionFactory
{
    const FACTORY_ID = 'type_reparation_grid_';

    /**
     * {@inheritdoc}
     */
    protected function getId()
    {
        return self::FACTORY_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return "Types de Réparation";
    }

    /**
     * {@inheritdoc}
     */
    protected function getColumns()
    {
        $columns = (new ColumnCollection())
            ->add((new DataColumn('id_type_reparation'))
                ->setName($this->trans('ID', [], 'Modules.HsRdv.Admin'))
                ->setOptions([
                    'field' => 'id_type_reparation',
                ])
            )
            ->add((new DataColumn('name'))
                ->setName($this->trans('Nom', [], 'Modules.HsRdv.Admin'))
                ->setOptions([
                    'field' => 'name',
                ])
            )
            ->add((new ActionColumn('actions'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                        ->add((new LinkRowAction('edit'))
                            ->setIcon('edit')
                            ->setOptions([
                                'route' => 'admin_type_reparation_edit',
                                'route_param_name' => 'typeReparationId',
                                'route_param_field' => 'id_type_reparation',
                            ])
                        )
                        ->add((new SubmitRowAction('delete'))
                            ->setName($this->trans('Delete', [], 'Admin.Actions'))
                            ->setIcon('delete')
                            ->setOptions([
                                'method' => 'POST',
                                'route' => 'admin_type_reparation_delete',
                                'route_param_name' => 'typeReparationId',
                                'route_param_field' => 'id_type_reparation',
                                'confirm_message' => $this->trans(
                                    'Supprimer ce type de réparation?',
                                    [],
                                    'Admin.Notifications.Warning'
                                ),
                            ])
                        ),
                ])
            )
        ;

        return $columns;
    }
}
