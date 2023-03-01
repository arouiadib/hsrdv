<?php

namespace PrestaShop\Module\HsRdv\Repository;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\DBAL\Connection;

class TypeReparationRepository
{
    /* @var Connection */
    private $connection;

    /* @var string */
    private $db_prefix;

    /* @var array */
    private $languages;

    /* @var TranslatorInterface */
    private $translator;


    public function __construct(
        Connection $connection,
        $dbPrefix,
        array $languages,
        TranslatorInterface $translator) {
        $this->connection = $connection;
        $this->db_prefix = $dbPrefix;
        $this->languages = $languages;
        $this->translator = $translator;
    }

    /**
     * @param array $data
     *
     * @return string
     *
     * @throws DatabaseException
     */
    public function create(array $data)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert($this->db_prefix . 'hsrdv_type_reparation')
            ->values([
                'name' => ':name'
            ])
            ->setParameters([
                'name' => $data['name']
            ]);

        $this->executeQueryBuilder($qb, 'Error creating type reparation');
        $typeReparationId = $this->connection->lastInsertId();

        return $typeReparationId;
    }

    /**
     * @param int $typeReparationId
     * @param array $data
     *
     * @throws DatabaseException
     */
    public function update($typeReparationId, array $data)
    {
          $qb = $this->connection->createQueryBuilder();
          $qb
              ->update($this->db_prefix . 'hsrdv_type_reparation', 'tr')
              ->set('name', ':name')
              ->andWhere('tr.id_type_reparation = :typeReparationId')
              ->setParameters([
                  'typeReparationId' => $typeReparationId,
                  'name' => $data['name'],
              ]);
          $this->executeQueryBuilder($qb, 'Erreur mise à jour type réparation');

    }

    /**
     * @param int $idTypeReparation
     *
     * @throws DatabaseException
     */
    public function delete($idTypeReparation)
    {
        $tableNames = [
            'hsrdv_type_reparation',
        ];

        foreach ($tableNames as $tableName) {
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->delete($this->db_prefix . $tableName)
                ->andWhere('id_type_reparation = :idTypeReparation')
                ->setParameter('idTypeReparation', $idTypeReparation)
            ;
            $this->executeQueryBuilder($qb, 'Delete error');
        }
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
}
