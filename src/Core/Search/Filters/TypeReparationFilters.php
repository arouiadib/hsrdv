<?php

namespace PrestaShop\Module\HsRdv\Core\Search\Filters;

use PrestaShop\PrestaShop\Core\Search\Filters;

/**
 * Class PostFilters.
 */
final class TypeReparationFilters extends Filters
{
    /**
     * {@inheritdoc}
     */
    public static function getDefaults()
    {
        return [
            'limit' => 0,
            'offset' => 0,
            'orderBy' => 'id_type_reparation',
            'sortOrder' => 'asc',
            'filters' => [],
        ];
    }
}
