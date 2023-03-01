<?php

namespace PrestaShop\Module\HsRdv\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity()
 * @ORM\Table(name="ps_hsrdv_devis_ligne")
 */

class DevisLigne
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_devis_ligne", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_devis", type="integer")
     */
    private $id_devis;

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_type_reparation", type="integer")
     */
    private $id_type_reparation;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_appareil", type="integer")
     */
    private $id_appareil;

    /**
     * @var string
     *
     * @ORM\Column(name="name_type_reparation", type="string", length=512)
     */
    private $name_type_reparation;

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
    public function getIdDevis()
    {
        return $this->id_devis;
    }

    /**
     * @param int $id_devis
     */
    public function setIdDevis($id_devis)
    {
        $this->id_devis = $id_devis;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
    public function getIdAppareil()
    {
        return $this->id_appareil;
    }

    /**
     * @param int $id_appareil
     */
    public function setIdAppareil($id_appareil)
    {
        $this->id_appareil = $id_appareil;
    }

    /**
     * @return string
     */
    public function getNameTypeReparation()
    {
        return $this->name_type_reparation;
    }

    /**
     * @param string $name_type_reparation
     */
    public function setNameTypeReparation($name_type_reparation)
    {
        $this->name_type_reparation = $name_type_reparation;
    }
}