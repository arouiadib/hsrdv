<?php

use PrestaShop\Module\HsRdv\Calendar\Calendar;
use PrestaShop\Module\HsRdv\Model\Appareil;
use PrestaShop\Module\HsRdv\Model\Reparation;
use PrestaShop\Module\HsRdv\Model\Booking;

class HsRdvCalendarModuleFrontController extends ModuleFrontController {

	public $ssl = false;

	public function init() 
	{
		parent::init();
	}

	public function initContent()
    {
        parent::initContent();

        $calendar = new Calendar();

        $month = (int) Tools::getValue('month');
        $year = (int) Tools::getValue('year');

        $availabilities = $calendar->getMonthAvailabilityByDays($month, $year);

        $daysOfWeek = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        // What is the first day of the month in question?
        $firstDayOfMonth = mktime(0,0,0, $month,1, $year);

        // How many days does this month contain?
        $numberDays = date('t', $firstDayOfMonth);

        // Retrieve some information about the first day of the
        // month in question.
        $dateComponents = getdate($firstDayOfMonth);

        // What is the name of the month in question?
        $monthName = $dateComponents['month'];

        // What is the index value (0-6) of the first day of the
        // month in question.
        $dayOfWeek = $dateComponents['wday'];

        $timeslotsUrl = $this->context->link->getModuleLink('hsrdv', 'timeslots');

        $currentMonthLink = $this->context->link->getModuleLink('hsrdv', 'calendar')
            . '?reparationToken=' . $this->getReparationFromToken()
            . '&month=' . idate('m')
            . '&year=' . idate('y')
        ;

        $currentDay = idate('d');
        $currentMonth = idate('m');
        $currentYear = idate('y');

        $currentDate = mktime(0,0,0, $currentMonth, $currentDay, $currentYear);
        $todayDate = date('Y-m-d', $currentDate);

        $reparationToken = Tools::getValue('reparationToken');

        $reparation = Reparation::getReparationFromToken($reparationToken);
        $countAppareils = count(Appareil::getAppareilsFromIdReparation($reparation['id_reparation']));

        $bookings = Booking::getBookingsFromIdReparation($reparation['id_reparation']);
        $dateBookings = '';
        $timeBooking = '';

        if (count($bookings)) {
            $dateBookings = array_column($bookings, 'date_booking')[0];
            $timeBooking = array_column($bookings, 'time_booking')[0];
        }

        $this->context->smarty->assign(
            array(
                'previousMonthLink' => $this->getPreviousMonthLink($month, $year),
                'nextMonthLink' => $this->getNextMonthLink($month, $year),
                'currentMonthLink' => $currentMonthLink,
                'today' => $currentDay,
                'todayDate' => $todayDate,
                'bookingsDate' => $dateBookings,
                'bookingTime' => $timeBooking,
                'currentMonth' => $currentMonth,
                'dataMonth' => $month,
                'monthName' => $monthName,
                'month' => str_pad($month, 2, "0", STR_PAD_LEFT),
                'year' => $year,
                'daysOfWeek' => $daysOfWeek,
                'dayOfWeek' => $dayOfWeek,
                'numberDays' => $numberDays,
                'lastPossibleDay' => $calendar->getLastPossbileDay(),
                'currentDate' => (new DateTime())->format('Y-m-d'),
                'availabilities' => $availabilities,
                'timeslotsUrl' => $timeslotsUrl,
                'reparationToken' => $this->getReparationFromToken(),
                'dayNumberSlots' => $calendar->calculateNumberSlotsPerDay(),
                'countAppareils' => $countAppareils
            )
        );

        $this->setTemplate('module:hsrdv/views/templates/front/calendar/calendar.tpl');
    }

    public function getReparationFromToken()
    {
        return Tools::getValue('reparationToken');

    }
    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->registerJavascript(
            'hsrdv',
            'modules/' .$this->module->name. '/views/js/calendar.js',
            array(
                'position' => 'bottom',
                'priority' => 150
            )
        );

        $this->context->controller->registerStylesheet(
            'hsrdv-css',
            'modules/' . $this->module->name . '/views/css/front.css',
            array(
                'media' => 'all',
                'priority' => 100,
            )
        );
    }

    public function getPreviousMonthLink($month, $year) {
	    if ($month - 1 < 1) {
            $month = 12;
            $year = $year - 1;
        } else {
            $month = $month - 1;
        }
        $previousMonthLink = $this->context->link->getModuleLink('hsrdv', 'calendar')
            . '?reparationToken=' . $this->getReparationFromToken()
            . '&month=' . $month
            . '&year=' . $year
        ;

	    return $previousMonthLink;
    }

    public function getNextMonthLink($month, $year) {

        if ($month + 1 > 12) {
            $month = 1;
            $year = $year + 1;
        } else {
            $month = $month + 1;
        }
        $previousMonthLink = $this->context->link->getModuleLink('hsrdv', 'calendar')
            . '?reparationToken=' . $this->getReparationFromToken()
            . '&month=' . $month
            . '&year=' . $year
        ;

        return $previousMonthLink;
    }
}
