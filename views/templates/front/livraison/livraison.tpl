{extends file='page.tpl'}

{block name='content_wrapper'}
    <section class="calendar container">
        <div class="row">
            <div class="livraison-content" id="mode-livraison">
                {include file='module:hsrdv/views/templates/front/livraison/livraison_form.tpl'}
            </div>
    </section>
{/block}