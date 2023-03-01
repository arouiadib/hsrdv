$(document).ready(() => {
    var $body = $('body');

    $body.on(
        'submit',
        '[data-action="firstFeedback"]',
        (event) => {
            event.preventDefault();
            let $form = $(event.target).closest('form');
            let query = $form.serialize() + '&action=sendFirstFeedback';
            let actionURL = $form.attr('action');
            show();
            $.post(actionURL, query, null, 'json').then((resp) => {
                if (resp) {
                    let $statusReparation = $('.status-reparation > span.line-content');
                    $statusReparation.empty().append(resp.rdv_status.message);
                    $statusReparation.css('background-color', resp.rdv_status.color);

                    $form.find('input').attr('disabled', true);
                }
                hide();

            }).fail((resp) => {
                //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
            });
        }
    );

    $body.on(
        'submit',
        '[data-action="priseEnChargeFeedback"]',
        (event) => {
            event.preventDefault();
            let $form = $(event.target).closest('form');
            let query = $form.serialize() + '&action=sendPriseEnChargeFeedback';
            let actionURL = $form.attr('action');
            show();
            $.post(actionURL, query, null, 'json').then((resp) => {
                if (resp) {
                    let $statusReparation = $('.status-reparation > span.line-content');
                    $statusReparation.empty().append(resp.rdv_status.message);
                    $statusReparation.css('background-color', resp.rdv_status.color);

                    $form.find('input').attr('disabled', true);
                    if (resp.prise_en_charge)  $('#devis-form').show();

                }
                hide();

            }).fail((resp) => {
                //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
            });
        }
    );

    $body.on(
        'submit',
        '[data-action="etatReparation"]',
        (event) => {
            event.preventDefault();
            let $form = $(event.target).closest('form');
            let query = $form.serialize() + '&action=sendEtatReparation';
            let actionURL = $form.attr('action');
            show();
            $.post(actionURL, query, null, 'json').then((resp) => {
                if (resp) {
                    let $statusReparation = $('.status-reparation > span.line-content');
                    $statusReparation.empty().append(resp.rdv_status.message);
                    $statusReparation.css('background-color', resp.rdv_status.color);

                    $form.find('input').attr('disabled', true);

                    // Show form for type reparations
                }
                hide();

            }).fail((resp) => {
                //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
            });
        }
    );

    $body.on(
        'submit',
        '[data-action="etatLivraison"]',
        (event) => {
            event.preventDefault();
            let $form = $(event.target).closest('form');
            let query = $form.serialize() + '&action=sendEtatLivraison';
            let actionURL = $form.attr('action');

            show();
            $.post(actionURL, query, null, 'json').then((resp) => {
                if (resp) {
                    let $statusReparation = $('.status-reparation > span.line-content');
                    $statusReparation.empty().append(resp.rdv_status.message);
                    $statusReparation.css('background-color', resp.rdv_status.color);

                    $form.find('input').attr('disabled', true);
                }
                hide();

            }).fail((resp) => {
                //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
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
    
    function addLigneDevis() {
        let countELements = $('.form-group-line').not('.dummy-devis-ligne').length;
        console.log(countELements);
        let $dummyTemplateAppareil = $(".dummy-devis-ligne").eq(0).clone().each(function (tr_idx, tr_elem) {
            var $tr = $(tr_elem);

            $tr.find("td:first").html(countELements + 1);
            $tr.find("input").each(function (i_idx, i_elem) {
                var $input = $(i_elem);

                $input.attr({
                    'name': function (_, id) {
                        return id.replace(/dummyline/, 'lines['+(countELements)+']');
                    }
                });
            });
            $tr.find("select").each(function (i_idx, i_elem) {
                var $input = $(i_elem);

                $input.attr({
                    'name': function (_, id) {
                        return id.replace(/dummyline/, 'lines['+(countELements)+']');
                    }
                });
            });

            $tr.find("textarea").each(function (i_idx, i_elem) {
                var $input = $(i_elem);

                $input.attr({
                    'name': function (_, id) {
                        return id.replace(/dummyline/, 'lines['+(countELements)+']');
                    }
                });
            });
        }).html();

        let $elementTemplate = $('<tr class="form-group-line devis-line" id="form-element-'+(countELements)+'"></tr>').append($dummyTemplateAppareil);
        $('#devis-table tbody').append($elementTemplate);
    }
    

    function removeElement(id_element) {
        $("#form-element-"+id_element).remove();
    }


    $(document).ready(() => {
        if ($('.form-group-line').not('.dummy-devis-ligne').length === 0) {
            addLigneDevis();
        }

        var $body = $('body');

        $body.on(
            'click',
            '[data-button-action="addLine"]',
            (event) => {
                event.preventDefault();
                console.log('clicked');
                addLigneDevis();
            }
        );

        $body.on(
            'submit',
            '[data-action="generation-devis"]',
            (event) => {
                event.preventDefault();

                let $form = $(event.target).closest('form');
                let query = $form.serialize() + '&action=GenererDevis';
                let actionURL = $form.attr('action');

                show();

                $.post(actionURL, query, null, 'json').then((resp) => {
                    if (resp) {

/*
                        let $repairFormContent = $('#rdv-form-content');
                        $repairFormContent.empty().append(resp);
*/

                    }
                    hide();

                }).fail((resp) => {
                    //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
                });
            }
        );

        $body.on(
            'click',
            'button.remove-line',
            (event) => {
                event.preventDefault();
                console.log("fz");
                let idLine = event.target.id.slice("remove-line-".length);
                removeElement(idLine);
                //$('#'+event.target.id).parent().remove();
            }
        );
        
    });
});