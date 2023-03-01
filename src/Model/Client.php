<?php

namespace PrestaShop\Module\HsRdv\Model;

use Db;

/**
 * Class Client
 */
class Client extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_client;

    /**
     * @var string
     */
    public $nom;

    /**
     * @var string
     */
    public $prenom;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $addresse_postale;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'hsrdv_client',
        'primary' => 'id_client',
        'multilang' => false,
        'fields' => array(
            'id_client' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'nom' => array('type' => self::TYPE_STRING),
            'prenom' => array('type' => self::TYPE_STRING),
            'email' => array('type' => self::TYPE_STRING),
            'phone' => array('type' => self::TYPE_STRING),
            'addresse_postale' => array('type' => self::TYPE_STRING),
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
            'id_client' => $this->id_client,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'phone' => $this->phone,
            'addresse_postale' => $this->addresse_postale
        ];
    }

    public static function getClientFromId($id_client)
    {
        $sql = 'SELECT c.`email`
                FROM `' . _DB_PREFIX_ . 'hsrdv_client` c
                WHERE c.id_client=' . (int)$id_client;

        return Db::getInstance()->getRow($sql);
    }

}
