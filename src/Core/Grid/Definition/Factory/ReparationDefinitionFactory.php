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
 * Class ReparationDefinitionFactory
 */
final class ReparationDefinitionFactory extends AbstractGridDefinitionFactory
{
    const FACTORY_ID = 'rdv_reparation_grid_';

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
        return 'rdv reparation grid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id_reparation'))
                ->setName($this->trans('ID', [], 'Modules.Hsrdv.Admin'))
                ->setOptions([
                    'field' => 'id_reparation',
                ])
            )
            ->add((new DataColumn('date_demande'))
                ->setName($this->trans('Date demande', [], 'Modules.Hsrdv.Admin'))
                ->setOptions([
                    'field' => 'date_demande',
                ])
            )
            ->add((new DataColumn('date_reparation'))
                ->setName($this->trans('Date reparation', [], 'Modules.Hsrdv.Admin'))
                ->setOptions([
                    'field' => 'date_reparation',
                ])
            )
            ->add((new DataColumn('id_status'))
                ->setName($this->trans('Status', [], 'Modules.Hsrdv.Admin'))
                ->setOptions([
                    'field' => 'id_status',
                ])
            )
            ->add((new DataColumn('date_livraison'))
                ->setName($this->trans('Date Livraison', [], 'Modules.Hsrdv.Admin'))
                ->setOptions([
                    'field' => 'date_livraison',
                ])
            )
            ->add((new ActionColumn('actions'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                ])
            )
            ->add((new ActionColumn('actions'))
                ->setName($this->trans('Actions', [], 'Admin.Global'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                        ->add((new LinkRowAction('view'))
                            ->setName($this->trans('View', [], 'Admin.Actions'))
                            ->setIcon('zoom_in')
                            ->setOptions([
                                'route' => 'admin_rdv_reparation_show',
                                'route_param_name' => 'reparationId',
                                'route_param_field' => 'id_reparation',
                                'clickable_row' => true,
                            ])
                        )
                ])
            );
        ;
    }
}
