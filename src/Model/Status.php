<?php

namespace PrestaShop\Module\HsRdv\Model;

use Db;

/**
 * Class Status
 */
class Status extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_status;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $message;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'hsrdv_status',
        'primary' => 'id_status',
        'multilang' => false,
        'fields' => array(
            'id_status' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'code' => array('type' => self::TYPE_STRING),
            'message' => array('type' => self::TYPE_STRING)
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
            'id_status' => $this->id_status,
            'code' => $this->code,
            'message' => $this->message
        ];
    }
}
