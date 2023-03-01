<?php

namespace PrestaShop\Module\HsRdv\Core\Search\Filters;

use PrestaShop\PrestaShop\Core\Search\Filters;

/**
 * Class PostFilters.
 */
final class ReparationFilters extends Filters
{
    /**
     * {@inheritdoc}
     */
    public static function getDefaults()
    {
        return [
            'limit' => 0,
            'offset' => 30,
            'orderBy' => 'date_demande',
            'sortOrder' => 'desc',
            'filters' => [],
        ];
    }
}
