<form action="{$rdv_form_action}" method="post" data-link-action="prendre-rendez-vous">
    {if $notifications2}
        <div class="col-xs-12 alert {if $notifications2.nw_error}alert-danger{else}alert-success{/if}">
            <ul>
                {foreach $notifications2.messages as $notif}
                    <li>{$notif}</li>
                {/foreach}
            </ul>
        </div>
    {/if}

    {if !$notifications2 || $notifications2.nw_error}
    <section class="form-fields container">
        <div class="form-group row">
            <label for="rdv-email" class="col-md-3 pr-0 form-control-label">{l s='Addresse mail:' d='Modules.Hsrdv.Shop'} *</label>
            <div class="col-md-9">
                <input
                    id="rdv-email"
                    class="form-control"
                    name="from"
                    type="email"
                    value="{$rdv.email}"
                    placeholder="{l s='your@email.com' mod='Modules.Hsrdv.Shop'}"
                >
            </div>
        </div>
        <div id="LabelForAppareils">{l s='Appareils à réparer:' d='Modules.Hsrdv.Shop'} *
            <span id="maxAppareils" class="hsrdv-note">{l s='( Maximum 3 appareils, Envoyer un message dans le cas echéant )' d='Modules.Hsrdv.Shop'}</span>
            </div>
        <div id="appareils-container">
            <ul id="tabs">
                {foreach item=item from=$rdv.appareils name=appareil}
                    <li class="tab-appareil" id="li-{$smarty.foreach.appareil.index}">
                        <span class="li-marque-reference">
                           <span class="li-marque">{$item.marque}</span> <span class="li-reference">{$item.reference}</span>
                        </span>
                        <button id="remove-appareil-{$smarty.foreach.appareil.index}" class="remove-appareil"></button>
                    </li>
                {/foreach}
            </ul>
            <ul class="dummy-tabs" style="display: none;">
                <li class="tab-appareil dummy-tab" id="li-">
                        <span class="li-marque-reference">
                           <span class="li-marque">Appareil</span> <span class="li-reference">1</span>
                        </span>
                    <button id="remove-appareil-" class="remove-appareil"></button>
                </li>
            </ul>
            <div id="tab-content">
                {foreach item=item from=$rdv.appareils name=appareil}
                    <div id="form-element-{$smarty.foreach.appareil.index}" class="form-group-appareil content-appareil">
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">{l s='Marque:' d='Modules.Hsrdv.Shop'}*</label>
                            <div class="col-md-6">
                                <input
                                        class="form-control marque"
                                        name="appareils[{$smarty.foreach.appareil.index}][marque]"
                                        type="text"
                                        value="{$item.marque}"
                                        placeholder="{l s='Ex: Harman Kardon' d='Modules.Hsrdv.Shop'}"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">{l s='Reference:' d='Modules.Hsrdv.Shop'}*</label>
                            <div class="col-md-6">
                                <input
                                        class="form-control reference"
                                        name="appareils[{$smarty.foreach.appareil.index}][reference]"
                                        type="text"
                                        value="{$item.reference}"
                                        placeholder="{l s='Ex: A-401' d='Modules.Hsrdv.Shop'}"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">{l s='Descriptif de(s) panne:' d='Modules.Hsrdv.Shop'}*</label>
                            <div class="col-md-6">
                                <textarea
                                    class="form-control"
                                    name="appareils[{$smarty.foreach.appareil.index}][descriptif_panne]"
                                    type="text"
                                    placeholder="{l s='Descriptif de(s) panne' d='Modules.Hsrdv.Shop'}"
                                    rows="6"
                                >{if $item.descriptif_panne}{$item.descriptif_panne}{/if}</textarea>
                            </div>
                        </div>
                    </div>
                {/foreach}

                <div class="form-group-appareil dummy-appareil" style="display: none;">
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label">{l s='Marque:' d='Modules.Hsrdv.Shop'}*</label>
                        <div class="col-md-6">
                            <input
                                    class="form-control marque"
                                    name="dummyappareil[marque]"
                                    type="text"
                                    value=""
                                    placeholder="{l s='Ex: Harman Kardon' d='Modules.Hsrdv.Shop'}"
                            >
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label">{l s='Reference:' d='Modules.Hsrdv.Shop'}*</label>
                        <div class="col-md-6">
                            <input
                                    class="form-control reference"
                                    name="dummyappareil[reference]"
                                    type="text"
                                    value=""
                                    placeholder="{l s='Ex: A-401' d='Modules.Hsrdv.Shop'}"
                            >
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label">{l s='Descriptif de(s) panne:' d='Modules.Hsrdv.Shop'}*</label>
                        <div class="col-md-6">
                        <textarea
                                class="form-control"
                                name="dummyappareil[descriptif_panne]"
                                type="text"
                                placeholder="{l s='Descriptif de(s) panne' d='Modules.Hsrdv.Shop'}"
                                rows="6"
                        ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div id="addSubFormButton">
                <input class="btn btn-primary"
                       name="addSubForm"
                       data-button-action="addDevice"
                       value="{l s='Ajouter Appareil' d='Modules.Hsrdv.Shop'}"
                       type="button">
            </div>
            <div>
                <span id="noteMandatoryFields" class="hsrdv-note">{l s='* Tous les champs sont obligatoires * ' d='Modules.Hsrdv.Shop'}</span>
            </div>
        </div>
    </section>

    <footer class="form-footer container">
        <style>
            input[name=url] {
                display: none !important;
            }
        </style>
        {*<input type="hidden" name="id_contact" value="{$id_contact}" />*}
        <input type="hidden" name="token" value="{$token}" />
        <div id="submitMessageButton">
            <input class="btn btn-xxl btn-primary" name="submitMessage" data-button-action="initiateRdv"
                   value="{l s='Envoyer Demande' d='Modules.Hsrdv.Shop'}" type="submit">
        </div>

{*        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            {l s='Annuler' d='Modules.Hsrdv.Shop'}
        </button>*}
    </footer>
    {/if}
</form>