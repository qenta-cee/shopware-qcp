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

{block name='frontend_checkout_confirm_information_wrapper' append}
    {if $payolutionTerms}
    {if $sUserData.additional.payment.name == 'wcp_invoice' || {$sUserData.additional.payment.name} == 'wcp_installment'}
    <div class="information--panel-item">
        <div class="tos--panel panel has--border">
            <div class="panel--title primary is--underline">
                {s name="WirecardCheckoutPagePayolutionTermsHeader"}Payolution Konditionen{/s}
            </div>
            <div class="panel--body is--wide">
                <ul class="list--checkbox list--unstyled">
                    <li class="block-group row--tos">
                        <span class="block column--checkbox">
                            <input type="checkbox" required="required" aria-required="true" id="wcpPayolutionTermsChecked" name="wcpPayolutionTermsChecked">
                        </span>
                        <span class="block column--label">
                            <label for="wcpPayolutionTermsChecked">
                                {if $payolutionLink1}
                                    {s name="WirecardCheckoutPagePayolutionConsent1"}Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine {/s}
                                    {$payolutionLink1}
                                    {s name="WirecardCheckoutPagePayoltuionLink"}Bewilligung{/s}
                                    {$payolutionLink2}
                                    {s name="WirecardCheckoutPagePayolutionConsent2"} kann ich jederzeit mit Wirkung für die Zukunft widerrufen.{/s}
                                {else}
                                    {s name="WirecardCheckoutPagePayolutionConsent1"}Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine {/s}
                                    {s name="WirecardCheckoutPagePayoltuionLink"}Bewilligung{/s}
                                    {s name="WirecardCheckoutPagePayolutionConsent2"} kann ich jederzeit mit Wirkung für die Zukunft widerrufen.{/s}
                                {/if}
                            </label>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    {/if}
    {/if}
{/block}
