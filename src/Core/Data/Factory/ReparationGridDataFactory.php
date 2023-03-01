<?php

namespace PrestaShop\Module\HsRdv\Core\Data\Factory;

use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PrestaShop\Module\HsRdv\Model\Status;
use DateTime;

final class ReparationGridDataFactory implements GridDataFactoryInterface
{
    /**
     * @var int
     */
    private $idLang;

    /**
     * @var GridDataFactoryInterface
     */
    private $reparationDataFactory;

    /**
     * @var Router
     */
    private $router;


    /**
     * @param GridDataFactoryInterface $reparationDataFactory
     */
    public function __construct(
        GridDataFactoryInterface $reparationDataFactory,
        int $idLang,
        UrlGeneratorInterface $router
    ) {
        $this->idLang = $idLang;
        $this->reparationDataFactory = $reparationDataFactory;
        $this->router= $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $commandeData = $this->reparationDataFactory->getData($searchCriteria);

        $modifiedRecords = $this->applyModification(
            $commandeData->getRecords()->all()
        );

        return new GridData(
            new RecordCollection($modifiedRecords),
            $commandeData->getRecordsTotal(),
            $commandeData->getQuery()
        );
    }

    /**
     * @param array $commandes
     *
     * @return array
     */
    private function applyModification(array $reparations)
    {
        foreach ($reparations as $i => $reparation) {
            $status = new Status($reparation['id_status']);
            $reparations[$i]['id_status'] = $status->message;

            $dateDemande = new DateTime($reparation['date_demande']);
            $reparations[$i]['date_demande'] = $dateDemande->format('Y-m-d');

            if($reparation['date_reparation'] == 0) {
                $reparations[$i]['date_reparation'] = '--';
            } else {
                $dateReparation = new DateTime($reparation['date_reparation']);
                $reparations[$i]['date_reparation'] = $dateReparation->format('Y-m-d');
            }

            if($reparation['date_livraison'] == 0) {
                $reparations[$i]['date_livraison'] = '--';
            } else {
                $dateReparation = new DateTime($reparation['date_livraison']);
                $reparations[$i]['date_livraison'] = $dateReparation->format('Y-m-d');
            }
        }

        return $reparations;
    }
}
