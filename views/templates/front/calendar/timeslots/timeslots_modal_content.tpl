<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title h6 text-sm-center" id="myModalLabel">
                {l s='Book an available timeslot in %today% '
                d='Modules.Hsrdv.Shop'
                sprintf=['%today%' => '']
                }
            </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <p>{l s='book paragraph' d='Modules.Hsrdv.Shop'}</p>
            </div>
            <div class="row">
                <div id="book-form-content">
                    {include file='module:hsrdv/views/templates/front/calendar/timeslots/timeslots_form.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>

