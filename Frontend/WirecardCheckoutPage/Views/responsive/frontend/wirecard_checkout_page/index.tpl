{extends file="parent:frontend/index/index.tpl"}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_breadcrumb'}
    <hr class="clear"/>
{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div class="content block">
        <div id="payment" class="grid_20" style="margin:10px 0 10px 20px;width:959px;">
            <iframe src="{$redirectUrl}" id="wcp_iframe"></iframe>
        </div>
    </div>
{/block}

