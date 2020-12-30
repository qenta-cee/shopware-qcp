{extends file="parent:frontend/checkout/finish.tpl"}

{namespace name="frontend/checkout/finish"}

{block name="frontend_checkout_finish_teaser" prepend}
	{if true eq $qcpPendingPayment}
		<div class="teaser qenta_pending"><h2 class="center">{s name='QentaCheckoutPageMessageActionPending'}Ihre Zahlung wurde vom Finanzdienstleister noch nicht bestätigt.{/s}</h2></div>
	{/if}
{/block}