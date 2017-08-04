{extends file="parent:frontend/index/index.tpl"}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_breadcrumb'}{/block}

{block name='frontend_index_navigation'}{/block}

{block name='frontend_index_navigation_categories_top'}{/block}

{block name='frontend_index_search'}{/block}

{block name='frontend_index_content_left'}{/block}

{block name="frontend_index_footer"}{/block}

{block name="frontend_index_shopware_footer"}{/block}

{block name="frontend_index_header_css_screen" append}
<style type="text/css">
    .shopware_footer {
        display: none;
    }
    body {
        text-align: center; font-size: small;
    }
</style>
{/block}

{block name="frontend_index_header_javascript_jquery_lib" append}
    <script type="text/javascript">
        function iframeBreakout(redirectUrl)
        {
            parent.location.href = redirectUrl;
        }

        window.onload = function() {
                iframeBreakout('{$redirectUrl}');
        };
    </script>
{/block}

{namespace name="frontend/checkout/return"}

{block name='frontend_index_content'}
<div>
    <p>{s name="WirecardCheckoutPagePaymentRedirectHeader"}Weiterleitung{/s}</p>
    <p>{s name="WirecardCheckoutPagePaymentRedirectText"}Sie werden nun weitergeleitet.{/s}</p>
    <p>{s name="WirecardCheckoutPagePaymentRedirectLinkText"}Falls Sie nicht weitergeleitet werden, klicken Sie bitte{/s}
        <a href="#" onclick="iframeBreakout('{$redirectUrl}')">
            {s name="WirecardCheckoutPagePaymentRedirectLink"}hier.{/s}
        </a>
    </p>
</div>
{/block}