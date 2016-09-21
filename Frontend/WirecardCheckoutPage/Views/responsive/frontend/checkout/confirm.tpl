{extends file="parent:frontend/checkout/confirm.tpl"}

{namespace name="frontend/checkout/confirm"}

{block name="frontend_index_content_top" append}
    <div class="grid_20">
        <div class="error" id="errors" {if !$wirecard_error}style="display:none;"{/if}>
            {if 'cancel' eq $wirecard_error}
                    {include file="frontend/_includes/messages.tpl" type="error" content="{s name='WirecardMessageActionCancel'}Der Zahlungsvorgang wurde von Ihnen abgebrochen.{/s}"}
            {elseif 'failure' eq $wirecard_error}
                    {include file="frontend/_includes/messages.tpl" type="error" content="{s name='WirecardMessageActionFailure'}W&auml;hrend des Zahlungsvorgangs ist ein Fehler aufgetreten. Bitte versuchen Sie es noch einmal oder w&auml;hlen eine andere Zahlungsart aus.{/s}"}
            {elseif 'external_error' eq $wirecard_error}
                {include file="frontend/_includes/messages.tpl" type="error" content="$wirecard_message"}
            {/if}
        </div>
    </div>
{/block}

