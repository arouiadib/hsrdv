<?php

namespace PrestaShop\Module\HsRdv\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ps_hsrdv_reparation")
 */

class Reparation
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_reparation", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_status", type="integer")
     */
    private $idStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="id_client", type="integer")
     */
    private $idClient;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_demande", type="datetime")
     */
    private $dateDemande;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_reparation", type="datetime")
     */
    private $dateReparation;

    /**
     * @var int
     *
     * @ORM\Column(name="mode_livraison", type="integer")
     */
    private $modeLivraison;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_livraison", type="datetime")
     */

    private $dateLivraison;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string")
     */
    private $token;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getIdStatus()
    {
        return $this->idStatus;
    }

    /**
     * @param int $idStatus
     */
    public function setIdStatus($idStatus)
    {
        $this->idStatus = $idStatus;
    }

    /**
     * @return int
     */
    public function getIdClient()
    {
        return $this->idClient;
    }

    /**
     * @param int $idClient
     */
    public function setIdClient($idClient)
    {
        $this->idClient = $idClient;
    }

    /**
     * @return \DateTime
     */
    public function getDateDemande()
    {
        return $this->dateDemande;
    }

    /**
     * @param \DateTime $dateDemande
     */
    public function setDateDemande($dateDemande)
    {
        $this->dateDemande = $dateDemande;
    }

    /**
     * @return \DateTime
     */
    public function getDateReparation()
    {
        return $this->dateReparation;
    }

    /**
     * @param \DateTime $dateReparation
     */
    public function setDateReparation($dateReparation)
    {
        $this->dateReparation = $dateReparation;
    }


    /**
     * @return int
     */
    public function getModeLivraison()
    {
        return $this->modeLivraison;
    }

    /**
     * @param int $modeLivraison
     */
    public function setModeLivraison($modeLivraison)
    {
        $this->modeLivraison = $modeLivraison;
    }

    /**
     * @return \DateTime
     */
    public function getDateLivraison()
    {
        return $this->dateLivraison;
    }

    /**
     * @param \DateTime $dateLivraison
     */
    public function setDateLivraison($dateLivraison)
    {
        $this->dateLivraison = $dateLivraison;
    }
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id_reparation' => $this->getId(),
            'id_status' => $this->getIdStatus(),
            'id_client' => $this->getIdClient()(),
            'date_demande' => $this->getDateDemande(),
            'date_reparation' => $this->getDateReparation(),
            'date_livraions' => $this->getDateLivraison()
        ];
    }
}