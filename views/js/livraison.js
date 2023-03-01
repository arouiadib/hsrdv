

$(document).ready(() => {
    var $body = $('body');


    $body.on(
        'submit',
        '[data-link-action="livraison"]',
        (event) => {
            event.preventDefault();

            let $form = $(event.target).closest('form');
            let query = $form.serialize() + '&action=EnvoyerModeLivraison';
            let actionURL = $form.attr('action');

            prestashop.emit('showTimeSlotsStart', {});

            $.post(actionURL, query, null, 'json').then((resp) => {
                if (resp) {

                    let $livraisonFormContent = $('#mode-livraison');
                    $livraisonFormContent.empty().append(resp);

                }
               prestashop.emit('bookTimeSlotEnd', {});

            }).fail((resp) => {
                //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
            });


        }
    );
});