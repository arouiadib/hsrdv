<?php

namespace PrestaShop\Module\HsRdv\Model;

use Db;

/**
 * Class Appareil
 */
class Appareil extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_appareil;

    /**
     * @var string
     */
    public $marque;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var string
     */
    public $descriptif_panne;

    /**
     * @var int
     */
    public $id_type_reparation;

    /**
     * @var int
     */
    public $id_reparation;
    /**
     * @var string
     */
    public $remarques_specifique;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'hsrdv_appareil',
        'primary' => 'id_appareil',
        'multilang' => false,
        'fields' => array(
            'id_appareil' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'marque' => array('type' => self::TYPE_STRING),
            'reference' => array('type' => self::TYPE_STRING),
            'descriptif_panne' => array('type' => self::TYPE_STRING),
            'id_type_reparation' => array('type' => self::TYPE_INT),
            'id_reparation' => array('type' => self::TYPE_INT),
            'remarques_specifique' => array('type' => self::TYPE_STRING),
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
            'id_appareil' => $this->id_appareil,
            'marque' => $this->marque,
            'reference' => $this->reference,
            'descriptif_panne' => $this->descriptif_panne,
            'id_type_reparation' => $this->id_type_reparation,
            'id_reparation' => $this->id_reparation,
            'remarques_specifique' => $this->remarques_specifique
        ];
    }

    public static function getAppareilsFromIdReparation($id_reparation)
    {
        $sql = 'SELECT a.`marque`, a.`reference`
                FROM `' . _DB_PREFIX_ . 'hsrdv_appareil` a
                WHERE a.id_reparation=' . (int)$id_reparation;

        return Db::getInstance()->executeS($sql);
    }
}
