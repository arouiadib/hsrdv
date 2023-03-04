<?php

namespace PrestaShop\Module\HsRdv\Model;

use Db;

/**
 * Class Reparation
 */
class Reparation extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_reparation;

    /**
     * @var int
     */
    public $id_status;

    /**
     * @var int
     */
    public $id_client;

    /**
     * @var \DateTime
     */
    public $date_reparation;

    /**
     * @var \DateTime
     */
    public $date_demande;

    /**
     * @var int
     */
    public $mode_livraison;

    /**
     * @var \DateTime
     */
    public $date_livraison;

    /**
     * @var string
     */
    public $token;

    /**
     * @var int
     */
    public $id_order;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'hsrdv_reparation',
        'primary' => 'id_reparation',
        'multilang' => false,
        'fields' => array(
            'id_reparation' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_status' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_client' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'date_demande' => array('type' => self::TYPE_DATE),
            'date_reparation' => array('type' => self::TYPE_DATE),
            'mode_livraison' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'date_livraison' => array('type' => self::TYPE_DATE),
            'token' => array('type' => self::TYPE_STRING),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
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
            'id_reparation' => $this->id_reparation,
            'id_status' => $this->id_status,
            'id_client' => $this->id_client,
            'date_demande' => $this->date_demande,
            'date_reparation' => $this->date_reparation,
            'mode_livraison' => $this->mode_livraison,
            'date_livraison' => $this->date_livraison
        ];
    }

    public static function getTotal()
    {
        $sql = 'SELECT r.id_reparation FROM ' . _DB_PREFIX_ . 'reparation r';

        if (!$reparations = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return count($reparations);
    }

    public static function getReparationFromToken($token)
    {
        $sql = 'SELECT r.id_reparation, r.`id_client`, r.`id_order`  FROM ' . _DB_PREFIX_ . 'hsrdv_reparation r WHERE r.token = "'.$token. '"';

        return Db::getInstance()->getRow($sql);
    }

/*    public static function getReparationFromIdReparation($id_order)
    {
        $sql = 'SELECT r.*  FROM ' . _DB_PREFIX_ . 'hsrdv_reparation r WHERE r.id_order = "'.$id_order. '"';

        return Db::getInstance()->getRow($sql);
    }*/


    public static function getReparationsForEnqueteSatisfaction($interval = 10)
    {
        $sql = 'SELECT r.id_reparation, c.`id_customer`, r.`token`  
                FROM ' . _DB_PREFIX_ . 'hsrdv_reparation r 
                JOIN ' . _DB_PREFIX_ . '_customer c
                ON r.id_client = c.id_customer
                WHERE r.`date_livraison` <= DATE_SUB(CURDATE(), INTERVAL '.$interval.' DAY)
                AND r.`date_livraison` >= DATE_SUB(CURDATE(), INTERVAL '.($interval + 1).'  DAY)';

        return Db::getInstance()->executeS($sql);
    }

    public static function getReparationsForRappelRendezVous($interval = 2)
    {
        $sql = 'SELECT id_reparation 
        FROM `ps_hsrdv_booking` 
        WHERE CURDATE() = DATE_SUB(`date_booking`, INTERVAL '.$interval.' DAY)';

        return Db::getInstance()->executeS($sql);
    }


//*/1 * * * * /usr/bin/php /var/www/html/hsdev/modules/hsrdv/cronSendMailsRappelRendezVous.php
//*/1 * * * * /usr/bin/php /var/www/html/hsdev/modules/hsrdv/cronSendMailsRappelEnqueteSatisfaction.php

}
