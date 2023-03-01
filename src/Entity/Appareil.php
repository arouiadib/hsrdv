<?php

namespace PrestaShop\Module\HsRdv\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity()
 * @ORM\Table(name="ps_hsrdv_appareil")
 */

class Appareil
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_appareil", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="marque", type="string", length=64)
     */
    private $marque;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=64)
     */
    private $reference;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_type_reparation", type="integer")
     */
    private $id_type_reparation;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_reparation", type="integer")
     */
    private $id_reparation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="decision", type="boolean")
     */
    private $decision;

    /**
     * @return bool
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * @param bool $decision
     */
    public function setDecision($decision)
    {
        $this->decision = $decision;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="descriptif_panne", type="string", length=512)
     */
    private $descriptif_panne;


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
     * @return string
     */
    public function getMarque()
    {
        return $this->marque;
    }

    /**
     * @param string $marque
     */
    public function setMarque($marque)
    {
        $this->marque = $marque;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return int
     */
    public function getIdTypeReparation()
    {
        return $this->id_type_reparation;
    }

    /**
     * @param int $id_type_reparation
     */
    public function setIdTypeReparation($id_type_reparation)
    {
        $this->id_type_reparation = $id_type_reparation;
    }

    /**
     * @return int
     */
    public function getIdReparation()
    {
        return $this->id_reparation;
    }

    /**
     * @param int $id_reparation
     */
    public function setIdReparation($id_reparation)
    {
        $this->id_reparation = $id_reparation;
    }

    /**
     * @return string
     */
    public function getDescriptifPanne()
    {
        return $this->descriptif_panne;
    }

    /**
     * @param string $descriptif_panne
     */
    public function setDescriptifPanne($descriptif_panne)
    {
        $this->descriptif_panne = $descriptif_panne;
    }

}