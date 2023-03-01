

$(document).ready(() => {
    var $body = $('body');

    $body.on(
        'click',
        '.show-timesolts ',
        (event) => {
            event.preventDefault();

            prestashop.emit('showTimeSlotsStart', {});

            let $eventTargetParents = $(event.target);
            let actionURL = $eventTargetParents.closest('table').data('action');
            let day = $eventTargetParents.closest('td').data('day');
            let month = $eventTargetParents.closest('table').data('month');
            let year = $eventTargetParents.closest('table').data('year');
            let token = $eventTargetParents.closest('table').data('reparation-token');
            let timeBooked = $eventTargetParents.closest('table').attr('data-time-booked');
            let dateBooked = $eventTargetParents.closest('table').attr('data-date-booked');

            let data = {
                day : day,
                month : month,
                year: year,
                reparationToken: token,
                dateBooked: dateBooked,
                timeBooked: timeBooked
            };
            $.post(actionURL, data, null, 'json').then((resp) => {
                if (resp.modal) {
                    prestashop.repair.showModal(resp.modal, resp.type);
                }
                prestashop.emit('showTimeSlotsEnd', {});

            }).fail((resp) => {
                prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
            });
        }
    );

    $body.on(
        'click',
        '.button-ts label',
        (event) => {
            event.preventDefault();
            let disabledtismeslot = $(event.target).parent().find('input').prop('disabled');
            if (!disabledtismeslot) {
                $(event.target).parent().find('input').prop('checked', 'checked');
                let countAppareils = $body.find('table').data('count-appareils');
                let $nextTimeslot = $(event.target).parent().parent().next().find('.button-ts label');
                let $nextTimeslotInput = $nextTimeslot.parent().find('input');

                if ( countAppareils > 2 ) {
                    let $labels = $(event.target).parents().find('.timeslots-container label');
                    $labels.css('background-color', '');
                    $labels.css('border-radius', '');
                    if ($nextTimeslot.length == 0 || $nextTimeslotInput.prop('disabled')) {
                        $('#'+$(event.target).parent().find('input').attr('id')).prop('checked', false);
                        alert('3 appareils, ont besoin de 2 créneaux minimum!');
                    } else {
                        $nextTimeslot.parent().find('label').css('background-color', '#4cbb6c');
                        $nextTimeslot.parent().find('label').css('border-radius', '4px');
                    }
                }
            }
        }
    );


    $body.on(
        'submit',
        '[data-link-action="book-timeslot"]',
        (event) => {
            event.preventDefault();

            let $form = $(event.target).closest('form');
            let query = $form.serialize() + '&action=ReserverTimeSlot';
            let actionURL = $form.attr('action');

            prestashop.emit('bookTimeSlotStart', {});

            $.post(actionURL, query, null, 'json').then((resp) => {
                if (resp) {

                    let $bookFormContent = $('#book-form-content');
                    $bookFormContent.empty().append(resp.modal);

                    $('body table.table').attr('data-time-booked', resp.time_booked);
                    $('body table.table').attr('data-date-booked', resp.date_booked);
                }
                prestashop.emit('bookTimeSlotEnd', {});

            }).fail((resp) => {
                //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
            });
        }
    );
});