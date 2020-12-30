{extends file="parent:frontend/checkout/confirm.tpl"}

{namespace name="frontend/checkout/confirm"}

{block name="frontend_index_header_javascript" append}
    {if isset($qcpConsumerDeviceIdScript)}
        {$qcpConsumerDeviceIdScript}
    {/if}
    <script type="text/javascript">
        function enableButton() {
            $('#qcpPayolutionTermsAccept').hide();
            $('.is--primary').attr('disabled', false);
        }

        function checkbirthday() {
            var m = $('#qcp-month').val();
            var d = $('#qcp-day').val();

            var dateStr = $('#qcp-year').val() + '-' + m + '-' + d;
            var minAge = 18;

            var birthdate = new Date(dateStr);
            var today = new Date();
            var limit = new Date((today.getFullYear() - minAge), today.getMonth(), today.getDate());
            if (birthdate < limit) {
                $('#qcp-birthdate').val(dateStr);
                $('#qcpPayolutionAging').hide();
                if ($('#qcpInvoiceTermsChecked').length) {
                    if ($('#qcpInvoiceTermsChecked').is(':checked')) {
                        enableButton();
                    } else {
                        $('.is--primary').attr('disabled', true);
                        $('#qcpPayolutionTermsAccept').show();
                    }
                } else {
                    enableButton();
                }
            }
            else {
                $('#qcpPayolutionTermsAccept').hide();
                $('#qcp-birthdate').val("");
                if ($('#qcp-day').is(":visible") == true) {
                    $('#qcpPayolutionAging').show();
                    $('.is--primary').attr('disabled', true);
                }
            }
        };

        {if $financialInstitutionSelectionEnabled}
        function setFinancialInstitution() {
            var paymentForm = $('#confirm--form');
            paymentForm.append('<input type="hidden" name="financialInstitution" value="' + $('#financialInstitutions').val() + '" />');
        }
        {/if}

        window.onload = function() {
            $(document).ready(function() {
                if ( {$paymentName|json_encode} == 'qcp_invoice' || {$paymentName|json_encode} == 'qcp_installment')
                {
                    $('#confirm--form').append('<input type="hidden" name="birthdate" id="qcp-birthdate" value="" />');
                    checkbirthday();
                }
                {if $financialInstitutionSelectionEnabled}
                    if ( {$paymentName|json_encode} == 'qcp_ideal' || {$paymentName|json_encode} == 'qcp_eps')
                    {
                        setFinancialInstitution();
                    }
                {/if}
            });
        };
    </script>
{/block}

{block name="frontend_index_content_top" append}
    <div class="grid_20">
        <div class="error" id="errors" {if !$qenta_error}style="display:none;"{/if}>
            {if 'cancel' eq $qenta_error}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name='QentaMessageActionCancel'}Der Zahlungsvorgang wurde von Ihnen abgebrochen.{/s}"}
            {elseif 'failure' eq $qenta_error}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name='QentaMessageActionFailure'}W&auml;hrend des Zahlungsvorgangs ist ein Fehler aufgetreten. Bitte versuchen Sie es noch einmal oder w&auml;hlen eine andere Zahlungsart aus.{/s}"}
            {elseif 'external_error' eq $qenta_error}
                {include file="frontend/_includes/messages.tpl" type="error" content="$qenta_message"}
                {/if}
        </div>
    </div>
{/block}

{block name='frontend_checkout_confirm_product_table' prepend}
    {if 'qcp_ideal' eq $paymentName}
        {if $financialInstitutionSelectionEnabled}
            <div class="panel has--border is--rounded" id="wd_payment_fields">
                <div class="panel--title is--underline">
                    <img src="{link file={$paymentLogo}}"/>{$wirecardAdditionalHeadline}
                </div>

                <div class="panel--body is--wide">
                    <div class="wirecard--field">
                        <select name="financialInstitution" id="financialInstitutions" onchange="setFinancialInstitution()">

                            {foreach from=$idlFinancialInstitutions item=bank key=short}
                                <option value="{$short}"
                                        {if $short eq $financialInstitutionsSelected}selected="selected" {/if}>
                                    {$bank}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="wirecard--clearer"></div>
                </div>
            </div>
        {/if}
    {elseif 'qcp_eps' eq $paymentName}
        {if $financialInstitutionSelectionEnabled}
            <div class="panel has--border is--rounded" id="wd_payment_fields">
                <div class="panel--title is--underline">
                    <img src="{link file={$paymentLogo}}"/>{$wirecardAdditionalHeadline}
                </div>

                <div class="panel--body is--wide">
                    <div class="wirecard--field">
                        <select name="financialInstitution" id="financialInstitutions" onchange="setFinancialInstitution()">

                            {foreach from=$epsFinancialInstitutions item=bank key=short}
                                <option value="{$short}"
                                        {if $short eq $financialInstitutionsSelected}selected="selected" {/if}>
                                    {$bank}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="wirecard--clearer"></div>
                </div>
            </div>
        {/if}
    {elseif 'qcp_invoice' eq $paymentName || 'qcp_installment' eq $paymentName}
        <div class="panel has--border is--rounded" id="wd_payment_fields">
        <div class="panel--title is--underline">
            <img src="{link file={$paymentLogo}}"/>{$paymentDesc}
        </div>
    <div class="panel--body is--wide">
        <div class="payment--selection-label is--underline" name="birthdate">{s name="QentaCheckoutPageBirthday"}Geburtsdatum{/s}</div>
        <div class="payment--form-group">
            <div class="row">
                <select name="days" id="qcp-day" onchange="checkbirthday();" required>
                    <option value="0">-</option>
                    {foreach from=$days item=v}
                        <option value="{$v}" {if ($bDay == $v)}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
                <select name="months" id="qcp-month" onchange="checkbirthday()" required>
                    <option value="0">-</option>
                    {foreach from=$months item=v}
                        <option value="{$v}" {if ($bMonth == $v)}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
                <select name="years" id="qcp-year" onchange="checkbirthday()" required>
                    <option value="0">-</option>
                    {foreach from=$years item=v}
                        <option value="{$v}" {if ($bYear == $v)}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear" style="content:''; clear:both; float:none;"></div>
            <span id="qcpPayolutionAging" style="color:red;font-weight:bold;display:none;">
		                    {s name="QentaCheckoutPageBirthdayInformation"}Sie müssen mindestens 18 Jahre alt sein, um dieses Zahlungsmittel nutzen zu können.{/s}
		                </span>
        </div>
        {if $payolutionTerms}
            <div class="payment--selection-label is--underline">
                {s name="QentaCheckoutPagePayolutionTermsHeader"}Payolution Konditionen{/s}
            </div>
                <ul class="list--checkbox list--unstyled">
                    <li class="block-group row--tos">
					            <span class="column--checkbox">
						            <input type="checkbox" required="required" aria-required="true" id="qcpInvoiceTermsChecked" onchange="checkbirthday()" name="qcpInvoiceTermsChecked">
					            </span>
                        <span class="column--checkbox">
						            <label for="qcpInvoiceTermsChecked">{if $qcpPayolutionLink1}
                                            {s name="QentaCheckoutPagePayolutionConsent1"}Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine {/s}
                                            {$qcpPayolutionLink1}
                                            {s name="QentaCheckoutPagePayolutionLink"}Bewilligung{/s}
                                            {$qcpPayolutionLink2}
                                            {s name="QentaCheckoutPagePayolutionConsent2"} kann ich jederzeit mit Wirkung für die Zukunft widerrufen.{/s}
                                        {else}
                                            {s name="QentaCheckoutPagePayolutionConsent1"}Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine {/s}
                                            {s name="QentaCheckoutPagePayolutionLink"}Bewilligung{/s}
                                            {s name="QentaCheckoutPagePayolutionConsent2"} kann ich jederzeit mit Wirkung für die Zukunft widerrufen.{/s}
                                        {/if}
						            </label>
					            </span>
                    </li>
                </ul>
            <span id="qcpPayolutionTermsAccept" style="color:red;font-weight:bold;display:none;">
                            {s name="QentaCheckoutPagePayolutionTermsAccept"}Bitte akzeptieren Sie die payolution Konditionen.{/s}
                        </span>
            <div class="clear" style="content:''; clear:both; float:none;"></div>
            <div class="wirecard--clearer"></div>
        {/if}
    </div>
        </div>
{/if}
{/block}