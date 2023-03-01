{extends file='page.tpl'}

{block name='content_wrapper'}
    <div class="row">
        <div class="col-md-5" id="help-video">
            <h1>{l s='Comment ca marche?' d='Modules.Hsrdv.Shop'}</h1>
            <iframe width="90%" height="500" src="https://www.youtube.com/embed/wX65iSZTI7E" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="col-md-7" id="rdv-form-content">
            <h1>{l s='Faire une demande de réparation' d='Modules.Hsrdv.Shop'}</h1>
            {include file='module:hsrdv/views/templates/front/rdv-form.tpl'}
        </div>
    </div>
{/block}