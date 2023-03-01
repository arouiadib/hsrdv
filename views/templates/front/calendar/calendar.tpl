{extends file='page.tpl'}

{block name='content_wrapper'}
    <section class="calendar container">
        <div class="row">
            <div class="cart-content" id="">
                <table class='table table-bordered'
                       data-month="{$dataMonth}"
                       data-year="{$year}"
                       data-action="{$timeslotsUrl}"
                       data-reparation-token="{$reparationToken}"
                       data-count-appareils="{$countAppareils}"
                       data-date-booked="{$bookingsDate}"
                       data-time-booked="{$bookingTime}"
                >
                    <center>
                        <h2>{$monthName} - 20{$year}</h2>
                        <a class='btn btn-xs btn-primary'
                           href="{$previousMonthLink}">
                            {l s='Previous Month' d='Modules.Hsrdv.Shop'}
                        </a>
                        <a href="{$currentMonthLink}"
                           class='btn btn-xs btn-primary'
                           data-month={$dataMonth}>
                            {l s='Current Month' d='Modules.Hsrdv.Shop'}
                        </a>
                        <a href="{$nextMonthLink}"
                            class="btn btn-xs btn-primary">
                            {l s='Next Month' d='Modules.Hsrdv.Shop'}
                        </a>
                    </center>

                    {assign var = "currentDay" value = 1}

                    <tr>
                        {foreach $daysOfWeek as $day}
                            <th  class='header'>{$day}</th>
                        {/foreach}
                    </tr>
                    <tr>

                        {for $k = 0 to $dayOfWeek - 1}
                            <td class="empty"></td>
                        {/for}
                        {while $currentDay <= $numberDays}
                            {if $dayOfWeek == 7}
                                {assign var="dayOfWeek" value = 0}
                                </tr><tr>
                            {/if}
                            {if $dayOfWeek != 0 and $dayOfWeek !=6 }
                                {assign var='currentDate' value=$year|cat:'-'|cat:$dataMonth|cat:"-"|cat:$currentDay}
                                <td class="day-td
                                            {if $availabilities[$currentDay]['morning']['count_free'] + $availabilities[$currentDay]['afternoon']['count_free'] == 0
                                                or $currentDate|strtotime > $lastPossibleDay|strtotime
                                                or $currentDate|strtotime < $todayDate|strtotime }
                                            day-unavailable
                                            {else}
                                            day-available
                                            {/if}
                                            {if $bookingsDate|strtotime == $currentDate|strtotime }day-booked{/if}
                                            "
                                    data-day="{$currentDay}">
                                    <div class="day-number"><h6>{$currentDay}</h6></div>
                                    <div class="clearfix"></div>
                                    <div class="day-availability">
                                            <div class="booked">
                                                {if $bookingsDate|strtotime == $currentDate|strtotime }
                                                <i class="material-icons" style="color:green">check</i>
                                                {/if}
                                            </div>
                                            <div class="day-available-timeslots
                                                {if  $currentDate|strtotime > $lastPossibleDay|strtotime
                                                    or $currentDate|strtotime < $todayDate|strtotime }
                                                    hidden
                                                {/if}">
                                                    {$availabilities[$currentDay]['morning']['count_free'] + $availabilities[$currentDay]['afternoon']['count_free']}/{$dayNumberSlots} {l s='Créneaux disponibles' d='Modules.Hsrdv.Shop'}
                                            </div>

                                            <div class="day-book-button">
                                                {if $availabilities[$currentDay]['morning']['count_free'] + $availabilities[$currentDay]['afternoon']['count_free'] == 0
                                                    or $currentDate|strtotime > $lastPossibleDay|strtotime
                                                    or $currentDate|strtotime < $todayDate|strtotime
                                                }
                                                    <button class='show-timesolts btn btn-xs slot-unavailable' disabled="disabled">{l s='Indisponible' d='Modules.Hsrdv.Shop'}</button>
                                                {else}
                                                    <button class='show-timesolts btn btn-success btn-xs slot-available'>{l s='Réserver' d='Modules.Hsrdv.Shop'}</button>
                                                {/if}
                                            </div>
                                    </div>
                                </td>
                            {else}
                                <td class="day-td day-unavailable">
                                    <div class="day-number"><h6>{$currentDay}</h6></div>
                                    <div class="clearfix"></div>
                                </td>
                            {/if}
                        {assign var = dayOfWeek value= $dayOfWeek + 1}
                        {assign var = currentDay value= $currentDay + 1}
                        {/while}
                    </tr>
                </table>
            </div>
    </section>
{/block}