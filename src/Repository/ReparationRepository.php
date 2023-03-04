<?php

namespace PrestaShop\Module\HsRdv\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Exception\DatabaseException;
use Symfony\Component\Translation\TranslatorInterface;
use Context;

/**
 * Class ReparationRepository
 */
class ReparationRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * PostRepository constructor.
     *
     * @param Connection $connection
     * @param string $dbPrefix
     * @param array $languages
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Connection $connection,
        $dbPrefix,
        array $languages,
        TranslatorInterface $translator
    ) {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
        $this->languages = $languages;
        $this->translator = $translator;
    }

    /**
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function createTables()
    {
        $errors = [];
        $engine = _MYSQL_ENGINE_;
        $this->dropTables();

        $queries = [
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_reparation`(
    			`id_reparation` int(10) unsigned NOT NULL auto_increment,
    			`id_order` int(10) unsigned NOT NULL,
    			`date_reparation` datetime,
    			`mode_livraison` int,
    			`date_livraison` datetime,
    	        `token` varchar(70) NOT NULL default '',
    			PRIMARY KEY (`id_reparation`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_client`(
    			`id_client` int(10) unsigned NOT NULL auto_increment,
    			`nom` varchar(70) NOT NULL default '',
    			`prenom` varchar(70) NOT NULL default '',
    			`email` varchar(70) NOT NULL default '',
    			`phone` varchar(70) NOT NULL default '',
    			`addresse_postale` varchar(512) NOT NULL default '',
    			PRIMARY KEY (`id_client`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_appareil`(
    			`id_appareil` int(10) unsigned NOT NULL auto_increment,
    			`marque` varchar(70) NOT NULL default '',
    			`reference` varchar(70) NOT NULL default '',
    			`descriptif_panne` text default NULL,
    			`id_type_reparation` int(10) unsigned NOT NULL,
    			`id_reparation` int(10) unsigned NOT NULL,
    			`id_order` int(10) unsigned NOT NULL,
    			`remarques_specifique` text default NULL,
    			`decision` boolean default NULL,
    			PRIMARY KEY (`id_appareil`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_status`(
    			`id_status` int(10) unsigned NOT NULL auto_increment,
    			`code` varchar(128) NOT NULL default '',
    			`message` varchar(70) NOT NULL default '',
    			`color` varchar(20) NOT NULL default '',
    			PRIMARY KEY (`id_status`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_booking`(
    			`id_booking` int(10) unsigned NOT NULL auto_increment,
    			`date_booking` date NOT NULL,
    			`timeslot_booking` VARCHAR(255) NOT NULL,
    			`time_booking` time NOT NULL,
    			`id_reparation` int(10) unsigned NOT NULL,
    			PRIMARY KEY (`id_booking`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_booking_exception`(
    			`id_booking_exception` int(10) unsigned NOT NULL auto_increment,
    			`date_booking_exception` date NOT NULL,
    			`timeslot_booking_exception` VARCHAR(255) NOT NULL,
    			`time_booking_exception` time NOT NULL,
    			PRIMARY KEY (`id_booking_exception`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_type_reparation` (
                `id_type_reparation` int(10) unsigned NOT NULL auto_increment,
                `name` varchar(256) NOT NULL default '',
                PRIMARY KEY (`id_type_reparation`)
            )ENGINE=$engine  DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_devis` (
                `id_devis` int(10) unsigned NOT NULL auto_increment,
                `id_reparation` int(10) unsigned NOT NULL,
                `acompte` int(10) unsigned NOT NULL,
                `remarques_specifiques` text default NULL,
                PRIMARY KEY (`id_devis`)
            )ENGINE=$engine  DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->dbPrefix}hsrdv_devis_ligne` (
                `id_devis_ligne` int(10) unsigned NOT NULL auto_increment,
                `id_devis` int(10) unsigned NOT NULL,
                `price` int(10) unsigned NOT NULL,
                `id_type_reparation` int(10) unsigned,
                `id_appareil` int(10) unsigned,
                `name_type_reparation` varchar(256) NOT NULL default '',
                PRIMARY KEY (`id_devis_ligne`)
            )ENGINE=$engine  DEFAULT CHARSET=utf8;"

        ];

        foreach ($queries as $query) {
            $statement = $this->connection->executeQuery($query);
            if (0 != (int) $statement->errorCode()) {
                $errors[] = [
                    'key' => json_encode($statement->errorInfo()),
                    'parameters' => [],
                    'domain' => 'Admin.Modules.Notification',
                ];
            }
        }

        return $errors;
    }

    /**
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function dropTables()
    {
        $errors = [];
        $tableNames = [
/*            'hsrdv_reparation',
            'hsrdv_client',
            'hsrdv_appareil',*/
            'hsrdv_status',
/*            'hsrdv_type_reparation',
            'hsrdv_booking_exception',
            'hsrdv_booking'*/

        ];
        foreach ($tableNames as $tableName) {
            $sql = 'DROP TABLE IF EXISTS ' . $this->dbPrefix . $tableName;
            $statement = $this->connection->executeQuery($sql);
            if ($statement instanceof Statement && 0 != (int) $statement->errorCode()) {
                $errors[] = [
                    'key' => json_encode($statement->errorInfo()),
                    'parameters' => [],
                    'domain' => 'Admin.Modules.Notification',
                ];
            }
        }

        return $errors;
    }

    /**
     * @param QueryBuilder $qb
     * @param string $errorPrefix
     *
     * @return Statement|int
     *
     * @throws DatabaseException
     */
    private function executeQueryBuilder(QueryBuilder $qb, $errorPrefix = 'SQL error')
    {
        $statement = $qb->execute();
        if ($statement instanceof Statement && !empty($statement->errorInfo())) {
            throw new DatabaseException($errorPrefix . ': ' . var_export($statement->errorInfo(), true));
        }

        return $statement;
    }

    public function installFixtures()
    {


        $errors = [];
        $sqlInsertStatuses = "INSERT INTO `{$this->dbPrefix}hsrdv_status` (`id_status`, `code`, `message`, `color`)
                               VALUES (1 , 'DEMANDE_REPARATION', 'Demande de réparation', 'yellow'),
                                        (2 , 'PRISE_RDV', 'Prise de rendez-vous', 'lime'), 
                                        (3 , 'RDV_REFUSE', 'Rendez-vous refusé', 'tomato'),
                                        (4 , 'RDV_PRIS', 'Rendez-vous pris', 'yellowgreen'),
                                        (5 , 'REPARATION_EN_COURS', 'Réparation en cours', 'steelblue'),
                                        (6 , 'NON_PRIS_EN_CHARGE ', 'Non pris en charge', 'red'),
                                        (7 , 'REPARE', 'Réparé', 'green'),
                                        (8 , 'A_LIVRER', 'A Livrer', 'grey'),
                                        (9 , 'LIVRE', 'Livré', 'blue'),
                                        (10 , 'ENQUETE', 'Enquête de satisfaction', 'purple')
                               ;";

        $statement = $this->connection->executeQuery($sqlInsertStatuses);
        if ($statement instanceof Statement && 0 != (int) $statement->errorCode()) {
            $errors[] = [
                'key' => json_encode($statement->errorInfo()),
                'parameters' => [],
                'domain' => 'Admin.Modules.Notification',
            ];
        }

        return $errors;
    }


}
