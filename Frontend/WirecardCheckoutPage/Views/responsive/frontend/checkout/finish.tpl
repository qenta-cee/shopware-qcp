{extends file="parent:frontend/checkout/finish.tpl"}

{namespace name="frontend/checkout/finish"}

{block name="frontend_checkout_finish_teaser" prepend}
 {if true eq $pendingPayment}
    <div class="teaser wirecard_pending"><h2 class="center">{s name='WirecardCheckoutPageMessageActionPending'}Ihre Zahlung wurde vom Finanzdienstleister noch nicht bestätigt.{/s}</h2></div>
 {/if}
{/block}
