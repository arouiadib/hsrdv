<?php

namespace PrestaShop\Module\HsRdv\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity()
 * @ORM\Table(name="ps_hsrdv_booking_exception")
 */

class BookingException
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_booking_exception", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="date_booking_exception", type="date")
     */
    private $date_booking_exception;

    /**
     * @var string
     *
     * @ORM\Column(name="timeslot_booking_exception", type="string", length=64)
     */
    private $timeslot_booking_exception;


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
    public function getDateBookingException()
    {
        return $this->date_booking_exception;
    }

    /**
     * @param string $date_booking_exception
     */
    public function setDateBookingException($date_booking_exception)
    {
        $this->date_booking_exception = $date_booking_exception;
    }

    /**
     * @return string
     */
    public function getTimeslotBookingException()
    {
        return $this->timeslot_booking_exception;
    }

    /**
     * @param string $timeslot_booking_exception
     */
    public function setTimeslotBookingException($timeslot_booking_exception)
    {
        $this->timeslot_booking_exception = $timeslot_booking_exception;
    }
}