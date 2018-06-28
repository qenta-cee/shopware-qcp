{extends file="frontend/index/index.tpl"}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_breadcrumb'}{/block}

{block name='frontend_index_navigation'}{/block}

{block name='frontend_index_navigation_categories_top'}{/block}

{block name='frontend_index_search'}{/block}

{block name='frontend_index_content_left'}{/block}

{block name="frontend_index_footer"}{/block}

{block name="frontend_index_shopware_footer"}{/block}

{block name="frontend_index_header_css_screen"}
	<style type="text/css">
		.shopware_footer {
			display: none;
		}
		body {
			text-align: center; font-size: small;
		}
	</style>
{/block}

{namespace name="frontend/checkout/pending"}

{block name='frontend_index_content'}
	<div class="teaser wirecard_pending"><h2 class="center">{s name='WirecardCheckoutPageMessageActionPending'}Ihre Zahlung wurde vom Finanzdienstleister noch nicht bestätigt.{/s}</h2></div>
{/block}