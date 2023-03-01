<?php

namespace PrestaShop\Module\HsRdv\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity()
 * @ORM\Table(name="ps_hsrdv_devis")
 */

class Devis
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_devis", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_reparation", type="integer")
     */
    private $id_reparation;

    /**
     * @var integer
     *
     * @ORM\Column(name="acompte", type="integer")
     */
    private $acompte;

    /**
     * @var string
     *
     * @ORM\Column(name="remarques_specifiques", type="text")
     */
    private $remarques_specifiques;


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
     * @return int
     */
    public function getAcompte()
    {
        return $this->acompte;
    }

    /**
     * @param int $acompte
     */
    public function setAcompte($acompte)
    {
        $this->acompte = $acompte;
    }

    /**
     * @return string
     */
    public function getRemarquesSpecifiques()
    {
        return $this->remarques_specifiques;
    }

    /**
     * @param string $remarques_specifiques
     */
    public function setRemarquesSpecifiques($remarques_specifiques)
    {
        $this->remarques_specifiques = $remarques_specifiques;
    }
}