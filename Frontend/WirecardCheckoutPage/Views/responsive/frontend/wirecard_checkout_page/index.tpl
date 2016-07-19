{extends file="parent:frontend/index/index.tpl"}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_breadcrumb'}
    <br class="clear"/>
{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div class="content block">
        <div id="payment" class="grid_20">
            <iframe src="{$redirectUrl}" width="680" height="660" id="wcp_iframe" style="border: 0; margin: auto;"></iframe>
        </div>
    </div>
{/block}

