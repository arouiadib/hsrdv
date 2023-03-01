<?php
namespace PrestaShop\Module\HsRdv\Calendar;

use Configuration;
use DateTime;
use DateInterval;
use PrestaShop\Module\HsRdv\Model\Booking;
use PrestaShop\Module\HsRdv\Model\BookingException;

class Calendar
{
    public function calculateNumberSlotsPerDay() {
        $numberSlotsMorning = $this->calculateNumberSlotsMorning();
        $numberSlotsAfternoon = $this->calculateNumberSlotsAfternoon();

        return $numberSlotsMorning + $numberSlotsAfternoon;
    }

    public function reverseBisCountBookingsByDay($month, $year) {

        $bookings = [];
        $results = Booking::getBisCountBookingsMonth($month, $year);

        foreach ($results as $result) {

            if ($result['morning']) {
                $bookings[$result['day']]['morning']['count'] = $result['count'];
                $bookings[$result['day']]['morning']['date'] = $result['date_booking'];
            } else {

                $bookings[$result['day']]['afternoon']['count'] = $result['count'];
                $bookings[$result['day']]['afternoon']['date'] = $result['date_booking'];
            }

        }

        return $bookings;
    }

    public function reverseBisCountBookingExceptionsByDay($month, $year) {

        $bookingExceptions = [];
        $results = BookingException::getBisCountBookingExceptionsMonth($month, $year);

        foreach ($results as $result) {
            if ($result['morning']) {
                $bookingExceptions[$result['day']]['morning']['count'] = $result['count'];
                $bookingExceptions[$result['day']]['morning']['date'] = $result['date_booking_exception'];
            } else {

                $bookingExceptions[$result['day']]['afternoon']['count'] = $result['count'];
                $bookingExceptions[$result['day']]['afternoon']['date'] = $result['date_booking_exception'];
            }
        }

        return $bookingExceptions;
    }

    public function getMonthAvailabilityByDays($month, $year) {

        $firstDayOfMonth = mktime(0,0,0, $month,1, $year);
        $numberDays = date('t', $firstDayOfMonth);

        $availabilities = [];
        $bookings = $this->reverseBisCountBookingsByDay($month, $year);
        $bookingExceptions = $this->reverseBisCountBookingExceptionsByDay($month, $year);

        $numberSlotsMorning = $this->calculateNumberSlotsMorning();
        $numberSlotsAfternoon = $this->calculateNumberSlotsAfternoon();

        for ($i = 1; $i <= (int)$numberDays; $i++) {
            $availabilities[$i]['morning']['count_booked'] = isset($bookings[$i]['morning']['count']) ? $bookings[$i]['morning']['count'] : 0;
            $availabilities[$i]['morning']['count_exceptionned'] = isset($bookingExceptions[$i]['morning']['count']) ? $bookingExceptions[$i]['morning']['count'] : 0;
            $availabilities[$i]['morning']['count_free'] = $numberSlotsMorning - ($availabilities[$i]['morning']['count_booked'] + $availabilities[$i]['morning']['count_exceptionned']);

            $availabilities[$i]['afternoon']['count_booked'] = isset($bookings[$i]['afternoon']['count']) ? $bookings[$i]['afternoon']['count'] : 0;
            $availabilities[$i]['afternoon']['count_exceptionned'] = isset($bookingExceptions[$i]['afternoon']['count']) ? $bookingExceptions[$i]['afternoon']['count'] : 0;
            $availabilities[$i]['afternoon']['count_free'] = $numberSlotsAfternoon - ($availabilities[$i]['afternoon']['count_booked'] + $availabilities[$i]['afternoon']['count_exceptionned']);

        }

        return $availabilities;
    }

    public function getLastPossbileDay() {
        $date = new DateTime();
        $date->add(new DateInterval('P'. Configuration::get('HSRDV_AVAILABLE_DAYS_FOR_BOOKING') .'D'));

        return $date->format('Y-m-d');
    }

    public function calculateNumberSlotsMorning() {
        $start = new DateTime(Configuration::get('HSRDV_DAY_START'));
        $interval = new DateInterval("PT". Configuration::get('HSRDV_TIMESLOT_DURATION') ."M");
        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $count = 0;
        for($intStart = $start; $intStart < $breaktime; $intStart->add($interval)){
            $count++;
        }
        return $count;
    }


    public function calculateNumberSlotsAfternoon() {
        $end = new DateTime(Configuration::get('HSRDV_DAY_END'));
        $interval = new DateInterval("PT". Configuration::get('HSRDV_TIMESLOT_DURATION') ."M");

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = (new DateTime(Configuration::get('HSRDV_BREAK_TIME')))->add($breakDuration);

        $count = 0;
        for($intStart = $restartAfterBreak; $intStart < $end; $intStart->add($interval)){
            $count++;
        }

        return $count;
    }
}
