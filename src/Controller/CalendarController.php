<?php

namespace PrestaShop\Module\HsRdv\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use PrestaShop\Module\HsRdv\Core\Search\Filters\ReparationFilters;
use PrestaShop\Module\HsRdv\Entity\Reparation;
use PrestaShop\Module\HsRdv\Entity\Status;
use PrestaShop\Module\HsRdv\Entity\Appareil;
use PrestaShop\Module\HsRdv\Entity\Client;
use Hrdv;
use DateTime;
use DateInterval;
use PrestaShop\Module\HsRdv\Model\BookingException;
use PrestaShop\Module\HsRdv\Model\Booking;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShop\Module\HsRdv\Calendar\Calendar;
use Configuration;
/**
 * Class CalendarController.
 *
 * @ModuleActivated(moduleName="hsrdv", redirectRoute="admin_module_manage")
 */
class CalendarController extends FrameworkBundleAdminController
{
    public function showTimeslotsAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {
            $day = $request->request->get('day');
            $month = $request->request->get('month');
            $year = $request->request->get('year');
            $available = $request->request->get('available');

            $dayMonthYear = mktime(0,0,0, $month, $day, $year);

            $toggleHalfDayUrl = $this->generateUrl('admin_rdv_calendar_toggle_half_day');
            $toggleDayUrl = $this->generateUrl('admin_rdv_calendar_toggle_day');
            $toggleTimeslotExceptionUrl = $this->generateUrl('admin_rdv_calendar_toggle_timeslot');

            $date = date('Y-m-d', $dayMonthYear);

            $morningExceptionsTimeslots = $this->getHalfDayBookingException($day, $month, $year, 1);
            $afternoonExceptionsTimeslots = $this->getHalfDayBookingException($day, $month, $year, 0);
            $morningBookingTimeslots = $this->getHalfDayBookings($day, $month, $year, 1);
            $afternoonBookingTimeslots = $this->getHalfDayBookings($day, $month, $year, 0);

            $calendar = new Calendar();
            $numberSlotsMorning = $calendar->calculateNumberSlotsMorning();
            $numberSlotsAfternoon = $calendar->calculateNumberSlotsAfternoon();


            $activateMorning = count($morningBookingTimeslots) == $numberSlotsMorning ? 0 : 1;
            $activateAfternoon = count($afternoonBookingTimeslots) ==  $numberSlotsAfternoon ? 0 : 1;

            return $this->render('@Modules/hsrdv/views/templates/admin/calendar/timeslots/timeslots.html.twig', [
                        'timeslots' => $this->timeslots($date),
                        'toggle_half_day_url' => $toggleHalfDayUrl,
                        'toggle_day_url' => $toggleDayUrl,
                        'toggle_timeslot_exception' => $toggleTimeslotExceptionUrl,
                        'activate_morning'  => $activateMorning,
                        'activate_afternoon'  => $activateAfternoon,
                        'date' => $date,
                        'day' => $day,
                        'month' => $month,
                        'year' => $year,
                        'available' => $available
                    ]);

        }

        return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
            400);

    }


    public function toggleDayAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {
            $day = $request->request->get('day');
            $month = $request->request->get('month');
            $year = $request->request->get('year');
            $activate = $request->request->get('activate');

            $this->toggleDay($day, $month, $year, $activate);
            return new JsonResponse(array(
                'status' => 'OK',
                'message' => []),
                200);
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);

    }

    public function toggleHalfDayAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {
            $day = $request->request->get('day');
            $month = $request->request->get('month');
            $year = $request->request->get('year');
            $order = $request->request->get('order');
            $activate = $request->request->get('activate');

            $this->toggleHalfDay($day, $month, $year, $order, $activate);

            return new JsonResponse(array(
                'status' => 'OK',
                'message' => []),
                200);
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);
    }

    public function toggleTimeslotExceptionAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
                400);
        }

        if(isset($request->request))
        {
            $day = $request->request->get('day');
            $month = $request->request->get('month');
            $year = $request->request->get('year');
            $timeslot = $request->request->get('timeslotString');
            $timeBooking = $request->request->get('time_booking');
            $activate = $request->request->get('activate');

            $this->toggleTimeslotException($day, $month, $year, $timeslot, $timeBooking, $activate);

            return new JsonResponse(array(
                'status' => 'OK',
                'activate' => $activate,
                'message' => []
            ), 200);
        }

        return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Error'),
            400);
    }
    /**
     * @param Request $request
     *
     * @return array
     */
    private function buildFiltersParamsByRequest(Request $request)
    {
        $filtersParams = array_merge(ReparationFilters::getDefaults(), $request->query->all());
        $filtersParams['filters']['id_lang'] = $this->getContext()->language->id;

        return $filtersParams;
    }


    /**
     * Gets the header toolbar buttons.
     *
     * @return array
     */
    private function getToolbarButtons()
    {
        return [
            'add' => [
                'href' => $this->generateUrl('admin_blog_post_create'),
                'desc' => $this->trans('New post', 'Modules.Asblog.Admin'),
                'icon' => 'add_circle_outline',
            ],
        ];
    }

    /**
     *
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function calendarAction(Request $request, $year, $month)
    {
        if ($month === 0 or $year === 0) {
            $month = idate('m');
            $year = idate('y');
        }

        $calendar = new Calendar();
        $bookings = $calendar->getMonthAvailabilityByDays($month, $year);
        //BookingException::getBisCountBookingExceptionssMonth($month, $year);
/*        echo "<pre>";
        var_dump( $bookings);die;*/

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

        $timeslotsUrl = $this->generateUrl('admin_rdv_calendar_timeslots');
        $toggleDayUrl = $this->generateUrl('admin_rdv_calendar_toggle_day');
        $toggleHalfDayUrl = $this->generateUrl('admin_rdv_calendar_toggle_half_day');

        $currentMonthLink =  $this->generateUrl('admin_rdv_calendar', array('month' => 0, 'year' => 0));

        $currentDay = idate('d');
        $currentMonth = idate('m');
        $currentYear = idate('y');

        $currentDate = mktime(0,0,0, $currentMonth, $currentDay, $currentYear);
        $todayDate = date('Y-m-d', $currentDate);


        return $this->render('@Modules/hsrdv/views/templates/admin/calendar/calendar.html.twig', [
            'previous_month_link' => $this->getPreviousMonthLink($month, $year),
            'next_month_link' => $this->getNextMonthLink($month, $year),
            'current_month_link' => $currentMonthLink,
            'today' => $currentDay,
            'today_date' => $todayDate,
            'current_month' => $currentMonth,
            'data_month' => $month,
            'month_name' => $monthName,
            'month' => str_pad($month, 2, "0", STR_PAD_LEFT),
            'year' => $year,
            'days_of_week' => $daysOfWeek,
            'day_of_week' => $dayOfWeek,
            'number_days' => $numberDays,
            'last_possible_day' => $calendar->getLastPossbileDay(),
            'current_date' => (new DateTime())->format('Y-m-d'),
            'bookings' => $bookings,
            'day_number_slots' => $calendar->calculateNumberSlotsPerDay(),
            'morning_number_slots' => $calendar->calculateNumberSlotsMorning(),
            'afternoon_number_slots' => $calendar->calculateNumberSlotsAfternoon(),
            'timeslots_url' => $timeslotsUrl,
            'toggle_half_day_url' => $toggleHalfDayUrl,
            'toggle_day_url' => $toggleDayUrl,
        ]);

    }

    public function getPreviousMonthLink($month, $year) {
        if ($month - 1 < 1) {
            $month = 12;
            $year = $year - 1;
        } else {
            $month = $month - 1;
        }
        $previousMonthLink = $this->generateUrl('admin_rdv_calendar', array('month' => $month, 'year' => $year));

        return $previousMonthLink;
    }

    public function getNextMonthLink($month, $year) {

        if ($month + 1 > 12) {
            $month = 1;
            $year = $year + 1;
        } else {
            $month = $month + 1;
        }

        $previousMonthLink = $this->generateUrl('admin_rdv_calendar', array('month' => $month, 'year' => $year));

        return $previousMonthLink;
    }

    public function timeslots($date){
        $bookingsOfTheDay = array_column(Booking::getDayBookings($date), 'timeslot_booking');
        $bookingExceptionsOfTheDay = array_column(BookingException::getDayBookingExceptions($date), 'timeslot_booking_exception');

        $start = new DateTime(Configuration::get('HSRDV_DAY_START'));
        $end = new DateTime(Configuration::get('HSRDV_DAY_END'));
        $interval = new DateInterval("PT". Configuration::get('HSRDV_TIMESLOT_DURATION') ."M");
        $breaktime = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));

        $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
        $restartAfterBreak = (new DateTime(Configuration::get('HSRDV_BREAK_TIME')))->add($breakDuration);
        $slots = [];
        $i = 0;
        for($intStart = $start; $intStart < $breaktime; $intStart->add($interval)){
            $endPeriod = clone $intStart;
            $endPeriod->add($interval);
            if($endPeriod > $breaktime){
                break;
            }
            $timeSlotString = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
            $slots['morning'][$i]['time_string'] = $intStart->format("H:i");
            $slots['morning'][$i]['timeslot_string'] = $timeSlotString;
            $slots['morning'][$i]['exceptionned'] = in_array($timeSlotString, $bookingExceptionsOfTheDay) ? true : false;
            $slots['morning'][$i]['booked'] = in_array($timeSlotString, $bookingsOfTheDay) ? true : false;
            if (in_array($timeSlotString, $bookingsOfTheDay)) {

                $entityManager = $this->container->get('doctrine.orm.entity_manager');
                $appareilRepository = $entityManager->getRepository(Appareil::class);


                $booking = Booking::getBookingFromDateAndTimeslot($date, $timeSlotString);

                $appareils = $appareilRepository->findBy(['id_reparation'=> $booking[0]['id_reparation']]);
                $appareilsListString = '';

                $lastAppareilKey = array_key_last($appareils);
                foreach ($appareils as $key => $appareil) {
                    $appareilsListString = $appareilsListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                    if ($lastAppareilKey != $key)
                    {
                        $appareilsListString = $appareilsListString . ', ';
                    }
                }
                $slots['morning'][$i]['appareils'] = $appareilsListString;
            }
            $i++;
        }

        for($intStart = $restartAfterBreak; $intStart < $end; $intStart->add($interval)){
            $endPeriod = clone $intStart;
            $endPeriod->add($interval);
            if($endPeriod > $end){
                break;
            }
            $timeSlotString = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
            $slots['afternoon'][$i]['time_string'] = $intStart->format("H:i");
            $slots['afternoon'][$i]['timeslot_string'] = $timeSlotString;
            $slots['afternoon'][$i]['exceptionned'] = in_array($timeSlotString, $bookingExceptionsOfTheDay) ? true : false;
            $slots['afternoon'][$i]['booked'] = in_array($timeSlotString, $bookingsOfTheDay) ? true : false;
            if (in_array($timeSlotString, $bookingsOfTheDay)) {

                $entityManager = $this->container->get('doctrine.orm.entity_manager');
                $appareilRepository = $entityManager->getRepository(Appareil::class);


                $booking = Booking::getBookingFromDateAndTimeslot($date, $timeSlotString);

                $appareils = $appareilRepository->findBy(['id_reparation'=> $booking[0]['id_reparation']]);
                $appareilsListString = '';

                $lastAppareilKey = array_key_last($appareils);
                foreach ($appareils as $key => $appareil) {
                    $appareilsListString = $appareilsListString . $appareil->getMarque() . ' ' . $appareil->getReference();
                    if ($lastAppareilKey != $key)
                    {
                        $appareilsListString = $appareilsListString . ', ';
                    }
                }
                $slots['afternoon'][$i]['appareils'] = $appareilsListString;
            }

            $i++;
        }

        return $slots;
    }

    public function toggleDay($day, $month, $year, $activate) {
        $this->toggleHalfDay($day, $month, $year, 0, $activate);
        $this->toggleHalfDay($day, $month, $year, 1, $activate);
    }

    public function toggleHalfDay($day, $month, $year, $morning, $activate){
        $interval = new DateInterval("PT". Configuration::get('HSRDV_TIMESLOT_DURATION') ."M");
        $dayMonthYear = mktime(0,0,0, $month, $day, $year);
        $date = date('Y-m-d', $dayMonthYear);

        $oldExceptions = $this->getHalfDayBookingException($day, $month, $year, $morning);

       foreach ($oldExceptions as $exception) {
            $bookingException = new BookingException((int)$exception['id_booking_exception']);
            $bookingException->delete();
       }

       if (!$activate) {
           if($morning) {
               $start = new DateTime(Configuration::get('HSRDV_DAY_START'));
               $end = new DateTime(Configuration::get('HSRDV_BREAK_TIME'));
               for($intStart = $start; $intStart < $end; $intStart->add($interval)){
                   $endPeriod = clone $intStart;
                   $endPeriod->add($interval);
                   if($endPeriod > $end){
                       break;
                   }
                   $timeslotBooking = Booking::getBooking($day, $month, $year, $intStart->format("H:i"));
                   if (count($timeslotBooking) != 0) {
                       return;
                   }

                   $timeSlotString = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");

                   $bookingException = new BookingException();
                   $bookingException->time_booking_exception = $intStart->format("H:i");
                   $bookingException->timeslot_booking_exception = $timeSlotString;
                   $bookingException->date_booking_exception = $date;
                   $bookingException->add();
               }
           } else {

               $breakDuration = new DateInterval("PT". Configuration::get('HSRDV_BREAK_DURATION') ."M");
               $start = (new DateTime(Configuration::get('HSRDV_BREAK_TIME')))->add($breakDuration);
               $end = new DateTime(Configuration::get('HSRDV_DAY_END'));

               for($intStart = $start; $intStart < $end; $intStart->add($interval)) {
                   $endPeriod = clone $intStart;
                   $endPeriod->add($interval);
                   if($endPeriod > $end){
                       break;
                   }

                   $timeslotBooking = Booking::getBooking($day, $month, $year, $intStart->format("H:i"));
                   if (count($timeslotBooking) != 0) {
                       return;
                   }
                   $timeSlotString = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");

                   $bookingException = new BookingException();
                   $bookingException->time_booking_exception = $intStart->format("H:i");
                   $bookingException->timeslot_booking_exception = $timeSlotString;
                   $bookingException->date_booking_exception = $date;
                   $bookingException->add();
               }
           }
       }

    }

    public function toggleTimeslotException($day, $month, $year, $timeslotString, $timeBooking, $activate) {

        $timeslotException = BookingException::getBookingException($day, $month, $year, $timeBooking);
        $timeslotBooking = Booking::getBooking($day, $month, $year, $timeBooking);

        if (!$activate) {
            if (count($timeslotBooking) != 0) {
                return;
            }
            if (count($timeslotException ) == 0) {
                $dayMonthYear = mktime(0,0,0, $month, $day, $year);
                $date = date('Y-m-d', $dayMonthYear);

                $bookingException = new BookingException();
                $bookingException->time_booking_exception = $timeBooking;
                $bookingException->timeslot_booking_exception = $timeslotString;
                $bookingException->date_booking_exception = $date;
                $bookingException->add();
            }
        } else {
            foreach ($timeslotException as $te) {
                $bookingException = new BookingException((int)$te['id_booking_exception']);
                $bookingException->delete();
            }
        }
        return;
    }

    public function getHalfDayBookingException($day, $month, $year, $morning) {

        $dayMonthYear = mktime(0,0,0, $month, $day, $year);
        $date = date('Y-m-d', $dayMonthYear);
        if($morning) {
            $bookingExceptions = BookingException::getMorningBookingExceptions($date);
        } else {
            $bookingExceptions = BookingException::getAfternoonBookingExceptions($date);
        }

        return $bookingExceptions;
    }

    public function getHalfDayBookings($day, $month, $year, $morning) {

        $dayMonthYear = mktime(0,0,0, $month, $day, $year);
        $date = date('Y-m-d', $dayMonthYear);
        if($morning) {
            $bookings = Booking::getMorningBookings($date);
        } else {
            $bookings = Booking::getAfternoonBookings($date);
        }

        return $bookings;
    }
}
