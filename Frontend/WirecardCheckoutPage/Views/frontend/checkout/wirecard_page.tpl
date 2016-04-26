{extends file="frontend/index/index.tpl"}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_breadcrumb'}<hr class="clear" />{/block}

{block name="frontend_index_header_css_screen" append}
    <link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/wcp.css'}" />
{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div id="payment" class="grid_20">
        <iframe src="{$redirectUrl}" id="wcp_iframe"></iframe>
    </div>
{/block}

