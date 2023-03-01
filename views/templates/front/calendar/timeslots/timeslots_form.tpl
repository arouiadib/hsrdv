<form action="{$book_form_action_link}" method="post" data-link-action="book-timeslot">

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

    <section class="timeslots-container col-md-6">
        <div class="col-md-6 morning-timeslots">
            <label class="">{l s='Morning' d='Shop.Forms.Labels'}</label>
            <div class="timeslots morning">
                {foreach  key=key item=item from=$timeslots.morning}
                    <div {*class="col-md-6"*}>
                        <div class="form-group button-ts">
                            <input type="radio"
                                   id="ts-morning-{$key}"
                                   name="time"
                                   value="{$item.time}"
                                   {if ($item.booked and $item.time|strtotime != $time_booked|strtotime) or $item.exceptionned}disabled{/if}
                                   {if ($item.booked and $item.time|strtotime == $time_booked|strtotime)}checked="checked"{/if}
                            >
                            <label class="btn btn-default" for="ts-morning-{$key}">{$item.timeslot_string}</label>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
        <div class="col-md-6 afternoon-timeslots">
            <label class="">{l s='Afternoon' d='Shop.Forms.Labels'}</label>
            <div class="timeslots afternoon">
                {foreach  key=key item=item from=$timeslots.afternoon}
                    <div {*class="col-md-6"*}>
                        <div class="form-group button-ts">z
                            <input type="radio"
                                   id="ts-morning-{$key}"
                                   name="time"
                                   value="{$item.time}"
                                   {if ($item.booked and $item.time|strtotime != $time_booked|strtotime) or $item.exceptionned}disabled{/if}
                                    {if ($item.booked and $item.time|strtotime == $time_booked|strtotime)}checked="checked"{/if}
                            >
                            <label class="btn btn-default" for="ts-morning-{$key}">{$item.timeslot_string}</label>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </section>

    <section class="form-fields col-md-6">
        <div class="form-group row">
            <label class="col-md-3 form-control-label">{l s='Nom' d='Shop.Forms.Labels'}*</label>
            <div class="col-md-6">
                <input
                        class="form-control"
                        name="nom"
                        type="text"
                        value="{$client_booking.nom}"
                        placeholder="{l s='Your family name' d='Shop.Forms.Help'}"
                >
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 form-control-label">{l s='Prenom' d='Shop.Forms.Labels'}*</label>
            <div class="col-md-6">
                <input
                        class="form-control"
                        name="prenom"
                        type="text"
                        value="{$client_booking.prenom}"
                        placeholder="{l s='Your first name' d='Shop.Forms.Help'}"
                />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 form-control-label">{l s='Telephone' d='Shop.Forms.Labels'}*</label>
            <div class="col-md-6">
                <input
                        class="form-control"
                        name="telephone"
                        type="text"
                        value="{$client_booking.telephone}"
                        placeholder="{l s='Telephone' d='Shop.Forms.Help'}"
                />
            </div>
        </div>


        <div class="form-group row">
            <label class="col-md-3 form-control-label">{l s='Addresse postale' d='Shop.Forms.Labels'}*</label>
            <div class="col-md-6">
                <input
                        class="form-control"
                        name="addresse_postale"
                        type="text"
                        value="{$client_booking.addresse_postale}"
                        placeholder="{l s='Adresse postale' d='Shop.Forms.Help'}"
                >
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 form-control-label">{l s='Email address' d='Shop.Forms.Labels'}</label>
            <div class="col-md-6">
                <input
                        class="form-control"
                        name="from"
                        type="email"
                        value="{$client_booking.email}"
                        disabled="disabled"
                        placeholder="{l s='your@email.com' d='Shop.Forms.Help'}"
                >
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 form-control-label">{l s='Appareils a reparer' d='Shop.Forms.Labels'}</label>
            <div class="col-md-6">
               {foreach from=$client_booking.appareils item=appareil}
                    <span>{$appareil['marque']} {$appareil['reference']}</span>
                {/foreach}
            </div>
        </div>
    </section>

    <footer class="form-footer">
        <style>
            input[name=url] {
                display: none !important;
            }
        </style>
        <input type="hidden" name="id_reparation" value="{$client_booking.id_reparation}" />
        <input type="hidden" name="reparationToken" value="{$reparationToken}" />
        <input type="hidden" name="token" value="{$token}" />
        <input type="hidden" name="date" value="{$date}" />
        <input class="btn btn-primary" name="submitMessage" data-button-action="repairForm"
               value="{l s='Send' d='Modules.Hsrdv.Shop'}" type="submit">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            {l s='Annuler' d='Modules.Hsrdv.Shop'}
        </button>
    </footer>
    {/if}
</form>
