prestashop.repair = prestashop.repair || {};

prestashop.repair.showModal = (html, type) => {

    function getRepairModal() {
        return $('#timeslots-modal');
    }

    let $repairModal = getRepairModal();
    if ($repairModal.length){
        $repairModal.remove();
    }
    $('body').append(html);

    $repairModal = getRepairModal();
    $repairModal.modal('show').on('hidden.bs.modal', (event) => {

    });
};


$(document).ready(() => {
    var $body = $('body');

    $body.on(
        'click',
        '.show-timeslots',
        (event) => {
            event.preventDefault();

            let $eventTargetParents = $(event.target);
            let actionURL = $eventTargetParents.closest('table').data('action-timeslots');
            let day = $eventTargetParents.closest('td').data('day');
            let month = $eventTargetParents.closest('table').data('month');
            let year = $eventTargetParents.closest('table').data('year');
            let available = $eventTargetParents.closest('td').hasClass('day-available');

            let data = {
                day : day,
                month : month,
                year: year,
                available: +available
            };

            show();

            $.post(actionURL, data, null, 'html').then((resp) => {
                if (resp) {
                    prestashop.repair.showModal(resp);
                }
                hide();
            }).fail((resp) => {
                console.log(resp);
            });
        }
    );

    $body.on(
        'click',
        '.toggle-half-day',
        (event) => {
            event.preventDefault();
            let actionURL;
            let day;
            let month;
            let year;


            let $eventTargetParents = $(event.target);
            if ($(event.target).closest('button').hasClass('toggle-source-timeslots')) {
                actionURL = $eventTargetParents.closest('.timeslots-container').data('action-toggle-half-day');
                day = $eventTargetParents.closest('.modal-body').data('day');
                month = $eventTargetParents.closest('.modal-body').data('month');
                year = $eventTargetParents.closest('.modal-body').data('year');
            } else if($(event.target).closest('button').hasClass('toggle-source-calendar')) {
                actionURL = $eventTargetParents.closest('table').data('action-toggle-half-day');
                day = $eventTargetParents.closest('td').data('day');
                month = $eventTargetParents.closest('table').data('month');
                year = $eventTargetParents.closest('table').data('year');
            } else {
                return;
            }
            let activate = $(event.target).closest('button').hasClass('activate');

            let order = $(event.target).closest('button').hasClass('toggle-morning');

            let data = {
                day : day,
                month : month,
                year: year,
                order: +order,
                activate: +activate
            };

            show();

            $.post(actionURL, data, null, 'json').then((resp) => {
                if (resp.status == "OK") {

                    if ($(event.target).closest('button').hasClass('toggle-source-timeslots')) {
                        let $icons = order ? $('.morning-timeslots').find('.timeslots').find('i') : $('.afternoon-timeslots').find('.timeslots').find('i');
                        if (!activate) {
                            $icons.each(function () {
                                $(this).css('color', 'green');
                                $(this).text('check');
                                $(this).next('label').text('Permettre');
                            });

                        } else {
                            $icons.each(function () {
                                $(this).css('color', 'red');
                                $(this).text('clear');
                                $(this).next('label').text('Restreinde');
                            });
                        }
                    } else if($(event.target).closest('button').hasClass('toggle-source-calendar')) {
                        console.log('here');
                    } else {
                        return;
                    }

                }
                hide();
            }).fail((resp) => {
                alert('Database wasn\' correctly updated, try again by refreshing the  page');
            });
        }
    );

    $body.on(
        'click',
        '.toggle-day',
        (event) => {
            event.preventDefault();

            let $eventTargetParents = $(event.target).closest('button');
            let actionURL = $eventTargetParents.closest('.modal-body').data('action-toggle-day');
            let day = $eventTargetParents.closest('.modal-body').data('day');
            let month = $eventTargetParents.closest('.modal-body').data('month');
            let year = $eventTargetParents.closest('.modal-body').data('year');
            let activate = $(event.target).closest('button').hasClass('activate');

            let data = {
                day : day,
                month : month,
                year: year,
                activate: +activate
            };

            show();

            $.post(actionURL, data, null, 'json').then((resp) => {
                if (resp.status == "OK") {
                    let $icons = $('.timeslots').find('i');
                    if (!activate) {
                        $icons.each(function () {
                            $(this).css('color', 'green');
                            $(this).text('check');
                            $(this).next('label').text('Permettre');
                        });

                    } else {
                        $icons.each(function () {
                            $(this).css('color', 'red');
                            $(this).text('clear');
                            $(this).next('label').text('Restreinde');
                        });
                    }
                }
                hide();
            }).fail((resp) => {
                alert('Database wasn\' correctly updated, try again by refreshing the  page');
            });
        }
    );


    $body.on(
        'click',
        '.toggle-timeslot-exception',
        (event) => {
            event.preventDefault();

            let $eventTargetParents = $(event.target);
            let actionURL = $eventTargetParents.closest('form').data('action-timeslot-exception');
            let timeslotString = $eventTargetParents.closest('form').find('input[name="timeslotString"]').val();
            let time_booking = $eventTargetParents.closest('form').find('input[name="time_booking"]').val();
            let day = $eventTargetParents.closest('.modal-body').data('day');
            let month = $eventTargetParents.closest('.modal-body').data('month');
            let year = $eventTargetParents.closest('.modal-body').data('year');
            let activate = $eventTargetParents.closest('button').hasClass('activate');

            let data = {
                day: day,
                month: month,
                year: year,
                time_booking: time_booking,
                timeslotString: timeslotString,
                activate: +activate
            };

            show();

            $.post(actionURL, data, null, 'json').then((resp) => {
                if (resp.status == "OK") {
                    $eventTargetParents.closest('button').toggleClass('activate');
                    if ($eventTargetParents.closest('button').hasClass('activate')) {
                        $eventTargetParents.closest('button').find('i').css('color', 'green');
                        $eventTargetParents.closest('button').find('i').text('check');
                        $eventTargetParents.closest('button').find('i').next('label').text('Permettre');
                    } else {

                        $eventTargetParents.closest('button').find('i').css('color', 'red');
                        $eventTargetParents.closest('button').find('i').text('clear');
                        $eventTargetParents.closest('button').find('i').next('label').text('Restreindre');
                    }
                }
                hide();
            }).fail((resp) => {
                alert('Database wasn\' correctly updated, try again by refreshing the  page');
            });
        }
    );



    const template = `<div class="hsrdv-overlay">
                <div class="overlay__inner">
                <div class="overlay__content"><span class="spinner"></span></div>
                </div>
                </div>`;

    function show() {
        if ($('.hsrdv-overlay').length === 1) {
            return;
        }

        $('body').append(template);
    }

    function hide() {
        $('.hsrdv-overlay').remove();
    }

});