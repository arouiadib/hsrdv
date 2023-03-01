<?php

namespace PrestaShop\Module\HsRdv\Model;

use Db;
use DateInterval;
use DateTime;
use Configuration;

/**
 * Class Booking
 */
class Booking extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_booking;

    /**
     * @var date
     */
    public $date_booking;

    /**
     * @var string
     */
    public $timeslot_booking;

    /**
     * @var int
     */
    public $id_reparation;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'hsrdv_booking',
        'primary' => 'id_booking',
        'multilang' => false,
        'fields' => array(
            'id_booking' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'date_booking' => array('type' => self::TYPE_DATE),
            'time_booking' => array('type' => self::TYPE_STRING),
            'timeslot_booking' => array('type' => self::TYPE_STRING),
            'id_reparation' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt')
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
            'id_booking' => $this->id_booking,
            'date_booking' => $this->date_booking,
            'timeslot_booking' => $this->timeslot_booking,
            'time_booking' => $this->time_booking,
            'id_reparation' => $this->id_reparation,
        ];
    }


    /**
     * @return date
     */
    public static function getBookings($date)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'bookings b';

        if (!$bookings = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return count($bookings);
    }

    /**
     * @return date
     */
    public static function getDayBookings($date)
    {
        $sql = 'SELECT timeslot_booking 
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking b
                WHERE date_booking = "' . $date . '"';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @return date
     */
    public static function getCountBookingsMonth($month, $year)
    {
        $sql = 'SELECT count(*) as count, DAY(date_booking) as day, date_booking
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking 
                WHERE MONTH(date_booking) = ' . (int)$month . '
                AND YEAR(date_booking) = 20' . (int)$year . '
                GROUP BY DAY(date_booking)';

        $results = Db::getInstance()->executeS($sql);

        return $results;
    }


    /**
     * @return date
     */
    public static function getBisCountBookingsMonth($month, $year)
    {
        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = $breaktime->add($breakDuration);
        $maxTime = $restartAfterBreak->format("H:i");

        $sqlMorning = 'SELECT count(*) as count, DAY(date_booking) as day, date_booking, 1 as morning
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking 
                WHERE MONTH(date_booking) = ' . (int)$month . '
                AND YEAR(date_booking) = 20' . (int)$year . '
                AND time_booking < CAST("' . $maxTime . '" AS time)
                GROUP BY DAY(date_booking)';

        $resultsMorning = Db::getInstance()->executeS($sqlMorning);

        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = $breaktime->add($breakDuration);
        $minTime = $restartAfterBreak->format("H:i");

        $sqlAfternoon = 'SELECT count(*) as count, DAY(date_booking) as day, date_booking, 0 as morning
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking 
                WHERE MONTH(date_booking) = ' . (int)$month . '
                AND YEAR(date_booking) = 20' . (int)$year . '
                AND time_booking >= CAST("' . $minTime . '" AS time)
                GROUP BY DAY(date_booking)';

        $resultsAfternoon = Db::getInstance()->executeS($sqlAfternoon);

        return array_merge($resultsMorning, $resultsAfternoon);
    }



    /**
     * @return date
     */
    public static function getBookingsFromIdReparation($id_reparation)
    {
        $sql = 'SELECT `id_booking`, `date_booking`, `timeslot_booking`, `time_booking`
                FROM `' . _DB_PREFIX_ . 'hsrdv_booking`
                WHERE id_reparation = ' . (int)$id_reparation;

        return Db::getInstance()->executeS($sql);
    }


    /**
     * @return date
     */
    public static function getBookingFromDateAndTimeslot($date, $timeslot_string)
    {
        $sql = 'SELECT `id_booking`, `id_reparation`
                FROM `' . _DB_PREFIX_ . 'hsrdv_booking`
                WHERE date_booking = "'. $date . '"
                AND timeslot_booking = "' . $timeslot_string . '"';
        //var_dump($sql);//die;
        return Db::getInstance()->executeS($sql);
    }



    /**
     * @return date
     */
    public static function getMorningBookings($date)
    {
        $maxTime = Configuration::get('HSRDV_BREAK_TIME');

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'hsrdv_booking 
                WHERE date_booking = "' . $date . '"
                AND time_booking < CAST("' . $maxTime . '" AS time)';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @return date
     */
    public static function getAfternoonBookings($date)
    {
        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = $breaktime->add($breakDuration);
        $maxTime = $restartAfterBreak->format("H:i");
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'hsrdv_booking
                WHERE date_booking = "' . $date . '"
                AND time_booking >= CAST("' . $maxTime . '" AS time)';

        return Db::getInstance()->executeS($sql);
    }


    /**
     * @return date
     */
    public static function getBooking($day, $month, $year, $time_booking)
    {
        $sql = 'SELECT `id_booking` 
                FROM `' . _DB_PREFIX_ . 'hsrdv_booking`
                WHERE time_booking =  CAST("' . $time_booking . '" AS time)
                AND DAY(date_booking) = ' . (int)$day . '
                AND MONTH(date_booking) = ' . (int)$month . '
                AND YEAR(date_booking) = 20' . (int)$year;

        return Db::getInstance()->executeS($sql);
    }
}
