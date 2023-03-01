<form action="{$livraison_form_action_link}" method="post" data-link-action="livraison">

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
        <div class="col-md-6">
            <label class="">{l s='Mode de livraison' d='Shop.Forms.Labels'}</label>
            <div class="">
                    <div {*class="col-md-6"*}>
                        <div class="form-group">
                            <input type="radio" id="livraisonChoix1" name="mode_livraison" value="1">
                            <label for="livraisonChoix1">{l s='Retrait Atelier' d='Shop.Forms.Labels'}</label>

                            <input type="radio" id="livraisonChoix2" name="mode_livraison" value="2">
                            <label for="livraisonChoix2">{l s='Livraison a domicile' d='Shop.Forms.Labels'}</label>
                        </div>
                    </div>
            </div>
        </div>
        <footer class="form-footer">
            <style>
                input[name=url] {
                    display: none !important;
                }
            </style>
            <input type="hidden" name="id_reparation" value="{$id_reparation}" />
            <input type="hidden" name="reparationToken" value="{$reparationToken}" />
            <input type="hidden" name="token" value="{$token}" />
            <input class="btn btn-primary" name="submitMessage" data-button-action="repairForm"
                   value="{l s='Send' d='Modules.Hsrdv.Shop'}" type="submit">
        </footer>
    {/if}
</form>
