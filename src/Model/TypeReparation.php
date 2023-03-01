<?php

namespace PrestaShop\Module\HsRdv\Model;

/**
 * Class TypeReparation
 */
class TypeReparation extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_type_reparation;

    /**
     * @var string
     */
    public $name;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'hsrdv_type_reparation',
        'primary' => 'id_type_reparation',
        'multilang' => false,
        'fields' => array(
            'id_type_reparation' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'name' => array('type' => self::TYPE_STRING),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

    }

    public function add($auto_date = true, $null_values = false)
    {
        $return = parent::add($auto_date, $null_values);

        return $return;
    }

    public function update($auto_date = true, $null_values = false)
    {
        $return = parent::update($auto_date, $null_values);

        return $return;
    }

    public function toArray()
    {
        return [
            'id_type_reparation' => $this->id_type_reparation,
            'name' => $this->name,
        ];
    }
}
