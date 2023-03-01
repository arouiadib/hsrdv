function addElement() {
    let countELements = $('.form-group-appareil').not('.dummy-appareil').length;
    if(countELements < 3) {

        let $dummyTemplateAppareil = $(".dummy-appareil").eq(0).clone().each(function (tr_idx, tr_elem) {
            var $tr = $(tr_elem);
            $tr.find("input").each(function (i_idx, i_elem) {
                var $input = $(i_elem);

                $input.attr({
                    'name': function (_, id) {
                        return id.replace(/dummyappareil/, 'appareils['+(countELements)+']');
                    }
                });
            });

            $tr.find("textarea").each(function (i_idx, i_elem) {
                var $input = $(i_elem);

                $input.attr({
                    'name': function (_, id) {
                        return id.replace(/dummyappareil/, 'appareils['+(countELements)+']');
                    }
                });
            });
        }).html();


        let $dummyTemplateTab = $(".dummy-tab").eq(0).clone().each(function (tr_idx, tr_elem) {
            var $tr = $(tr_elem);
            $tr.attr('id', ($tr.attr('id')).replace(/li-/, 'li-'+(countELements)));

            $tr.find("button.remove-appareil").each(function (i_idx, i_elem) {
                var $input = $(i_elem);

                $input.attr({
                    'id': function (_, id) {
                        return id.replace(/remove-appareil-/, 'remove-appareil-'+(countELements ));
                    }
                });
            });

            $tr.find("span.li-reference").each(function (i_idx, i_elem) {
                var $input = $(i_elem);

                $input.text(countELements + 1);
            });
        }).html();


        let $elementTemplate = $('<div class="form-group-appareil content-appareil" id="form-element-'+(countELements)+'"></div>').append($dummyTemplateAppareil);
        $('#tab-content').append($elementTemplate);
        $('.content-appareil').removeClass("active");
        $('.content-appareil:last').addClass("active");


        let $tabTemplate= $('<li class="tab-appareil" id="li-'+(countELements)+'"></li>').append($dummyTemplateTab);
        $('#tabs').append($tabTemplate);
        $('.tab-appareil').removeClass("active");
        $('.tab-appareil').not('.dummy-tab').last().addClass("active");
        $('span.plus').insertAfter('#tabs li:last');

    } else {
        toggleStatusAddAppareilButton(true);
        alert('Maximum trois appareils');
    }
}

function toggleStatusAddAppareilButton(status) {
    $('input[name="addSubForm"]').attr('disabled', status);
}


function removeElement(id_element) {
    $("#form-element-"+id_element).remove();
}


$(document).ready(() => {
    if ($('.form-group-appareil').not('.dummy-appareil').length === 0) {
        addElement();
        $('.form-group-appareil').not('.dummy-appareil').last().addClass("active");
        $('.tab-appareil').not('.dummy-tab').last().addClass("active");
    }


    var $body = $('body');

    $body.on(
        'click',
        '.add-appareil',
        (event) => {
            event.preventDefault();
            addElement();
        }
    );

    $body.on(
        'submit',
        '[data-link-action="prendre-rendez-vous"]',
        (event) => {
            event.preventDefault();

            let $form = $(event.target).closest('form');
            let query = $form.serialize() + '&action=PrendreRendezVous';
            let actionURL = $form.attr('action');

            prestashop.emit('repairFormStart', {});

            $.post(actionURL, query, null, 'json').then((resp) => {
                if (resp) {

                    let $repairFormContent = $('#rdv-form-content');
                    $repairFormContent.empty().append(resp);

                }
                prestashop.emit('repairFormEnd', {});

            }).fail((resp) => {
                //prestashop.emit('handleError', {eventType: 'repairProduct', resp: resp});
            });
        }
    );

    $body.on(
        'click',
        'button.remove-appareil',
        (event) => {
            event.preventDefault();
            let idFormAppareil = event.target.id.slice("remove-appareil-".length);
            removeElement(idFormAppareil);
            $('#'+event.target.id).parent().remove();
            $('.li-marque-reference:first').click();
        }
    );

    $body.on(
        'keyup',
        '.form-group-appareil input',
        (event) => {
            event.preventDefault();
            let $targetSpan =  $(event.target).attr('class').split(' ')[1];
            let $formElementID = $(event.target).parents('.form-group-appareil').attr('id').slice("form-element-".length);
            $('li#li-'+$formElementID).find('span.li-'+$targetSpan).text($(event.target).val());
        }
    );

    let $tabLinks = $('.tab-appareil');
    let $tabContent = $('.content-appareil');

    $body.on(
        'click',
        '.li-marque-reference',
        (event) => {
            event.preventDefault();
            let $id = $(event.currentTarget).parent().attr('id');
            $id = $id.slice("li-".length);

            $('.content-appareil').removeClass("active");
            $('.tab-appareil').removeClass("active");

            $("#form-element-" + $id).addClass("active");
            $(event.currentTarget).parent().addClass("active");
        }
    );
});