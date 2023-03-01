<?php

namespace PrestaShop\Module\HsRdv\Model;

use Configuration;
use Db;
use DateTime;
use DateInterval;

/**
 * Class BookingException
 */
class BookingException extends \ObjectModel
{
    /**
     * @var int
     */
    public $id_booking_exception;

    /**
     * @var date
     */
    public $date_booking_exception;

    /**
     * @var string
     */
    public $timeslot_booking_exception;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'hsrdv_booking_exception',
        'primary' => 'id_booking_exception',
        'multilang' => false,
        'fields' => array(
            'id_booking_exception' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'date_booking_exception' => array('type' => self::TYPE_DATE),
            'time_booking_exception' => array('type' => self::TYPE_STRING),
            'timeslot_booking_exception' => array('type' => self::TYPE_STRING)
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
            'id_booking_exception' => $this->id_booking_exception,
            'date_booking_exception' => $this->date_booking_exception,
            'timeslot_booking_exception' => $this->timeslot_booking_exception,
            'time_booking_exception' => $this->timeslot_booking_exception
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
    public static function getDayBookingExceptions($date)
    {
        $sql = 'SELECT timeslot_booking_exception, time_booking_exception 
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking_exception b
                WHERE date_booking_exception = "' . $date . '"';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @return date
     */
    public static function getMorningBookingExceptions($date)
    {
        $maxTime = Configuration::get('HSRDV_BREAK_TIME');

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'hsrdv_booking_exception
                WHERE date_booking_exception = "' . $date . '"
                AND time_booking_exception < CAST("' . $maxTime . '" AS time)';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @return date
     */
    public static function getAfternoonBookingExceptions($date)
    {
        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = $breaktime->add($breakDuration);
        $maxTime = $restartAfterBreak->format("H:i");
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'hsrdv_booking_exception
                WHERE date_booking_exception = "' . $date . '"
                AND time_booking_exception >= CAST("' . $maxTime . '" AS time)';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @return date
     */
    public static function getCountBookingExceptionsMonth($month, $year)
    {
        $sql = 'SELECT count(*) as count, DAY(date_booking_exception) as day, date_booking_exception 
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking_exception 
                WHERE MONTH(date_booking_exception) = ' . (int)$month . '
                AND YEAR(date_booking_exception) = 20' . (int)$year . '
                GROUP BY DAY(date_booking_exception)';

        $results = Db::getInstance()->executeS($sql);

        return $results;
    }


    /**
     * @return date
     */
    public static function getBisCountBookingExceptionsMonth($month, $year)
    {
        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = $breaktime->add($breakDuration);
        $maxTime = $restartAfterBreak->format("H:i");

        $sqlMorning = 'SELECT count(*) as count, DAY(date_booking_exception) as day, date_booking_exception, 1 as morning
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking_exception 
                WHERE MONTH(date_booking_exception) = ' . (int)$month . '
                AND YEAR(date_booking_exception) = 20' . (int)$year . '
                AND time_booking_exception < CAST("' . $maxTime . '" AS time)
                GROUP BY DAY(date_booking_exception)';

        $resultsMorning = Db::getInstance()->executeS($sqlMorning);

        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = $breaktime->add($breakDuration);
        $minTime = $restartAfterBreak->format("H:i");

        $sqlAfternoon = 'SELECT count(*) as count, DAY(date_booking_exception) as day, date_booking_exception, 0 as morning
                FROM ' . _DB_PREFIX_ . 'hsrdv_booking_exception 
                WHERE MONTH(date_booking_exception) = ' . (int)$month . '
                AND YEAR(date_booking_exception) = 20' . (int)$year . '
                AND time_booking_exception >= CAST("' . $minTime . '" AS time)
                GROUP BY DAY(date_booking_exception)';

        $resultsAfternoon = Db::getInstance()->executeS($sqlAfternoon);

        return array_merge($resultsMorning, $resultsAfternoon);
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
    public static function getBookingsFromIdReparation($id_reparation)
    {
        $sql = 'SELECT b.`id_booking`, b.`date_booking`, b.`timeslot_booking`
                FROM `' . _DB_PREFIX_ . 'hsrdv_booking` b
                WHERE b.id_reparation = ' . (int)$id_reparation;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @return date
     */
    public static function getBookingException($day, $month, $year, $time_booking_exception)
    {
        $sql = 'SELECT b.`id_booking_exception` 
                FROM `' . _DB_PREFIX_ . 'hsrdv_booking_exception` b
                WHERE b.time_booking_exception =  CAST("' . $time_booking_exception . '" AS time)
                AND DAY(date_booking_exception) = ' . (int)$day . '
                AND MONTH(date_booking_exception) = ' . (int)$month . '
                AND YEAR(date_booking_exception) = 20' . (int)$year;

        //var_dump($sql);

        return Db::getInstance()->executeS($sql);
    }

}
