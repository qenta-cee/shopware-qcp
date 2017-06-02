{extends file="parent:frontend/checkout/confirm.tpl"}

{namespace name="frontend/checkout/confirm"}

{block name="frontend_index_header_javascript" append}
    <script type="text/javascript">
        function checkbirthday() {
            var m = $('#wcp-month').val();
            var d = $('#wcp-day').val();

            var dateStr = $('#wcp-year').val() + '-' + m + '-' + d;
            var minAge = 18;

            var birthdate = new Date(dateStr);
            var year = birthdate.getFullYear();
            var today = new Date();
            var limit = new Date((today.getFullYear() - minAge), today.getMonth(), today.getDate());
            if (birthdate < limit) {
                $('#wcp-birthdate').val(dateStr);
                $('#wcpPayolutionAging').hide();
                if ($('#wcpInvoiceTermsChecked').is(':checked')) {
                    $('#wcpPayolutionTermsAccept').hide();
                    $('.is--primary').attr('disabled', false);
                }
                else
                {
                    $('.is--primary').attr('disabled', true);
                    $('#wcpPayolutionTermsAccept').show();
                }
            }
            else {
                $('#wcpPayolutionTermsAccept').hide();
                $('#wcp-birthdate').val("");
                if ($('#wcp-day').is(":visible") == true) {
                    $('#wcpPayolutionAging').show();
                    $('.is--primary').attr('disabled', true);
                }
            }
        };

        function setFinancialInstitution() {
            var paymentForm = $('#confirm--form');
            paymentForm.append('<input type="hidden" name="financialInstitution" value="' + $('#financialInstitutions').val() + '" />');
        }

        window.onload = function() {
            $(document).ready(function() {
                if ( {$paymentName|json_encode} == 'wcp_invoice' || {$paymentName|json_encode} == 'wcp_installment')
                {
                    checkbirthday();
                }
                else if ( {$paymentName|json_encode} == 'wcp_ideal' || {$paymentName|json_encode} == 'wcp_eps')
                {
                    setFinancialInstitution();
                }
            });
        };
    </script>
{/block}

{block name='frontend_checkout_confirm_product_table' prepend}
    {if $wcpAdditional  eq 'financialInstitutions'}
        <div class="panel has--border is--rounded" id="wd_payment_fields">
            <div class="panel--title is--underline">
                <img src="{link file={$paymentLogo}}"/>{$wirecardAdditionalHeadline}
            </div>

            <div class="panel--body is--wide">
                <div class="wirecard--field">

                    {*<label for="ccard_cardholdername">{s name='WirecardFinancialInstitutions'}Finanzinstitut{/s}:</label>*}
                    <select name="financialInstitution" id="financialInstitutions" onchange="setFinancialInstitution()">

                        {foreach from=$financialInstitutions item=bank key=short}
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
    {elseif 'wcp_invoice' eq $paymentName || 'wcp_installment' eq $paymentName}
        <div class="panel has--border is--rounded" id="wd_payment_fields">
        <div class="panel--title is--underline">
            <img src="{link file={$paymentLogo}}"/>{$paymentDesc}
        </div>
    <div class="panel--body is--wide">
        <div class="payment--selection-label is--underline" name="birthdate">{s name="WirecardCheckoutPageBirthday"}Geburtsdatum{/s}</div>
        <div class="payment--form-group">
            <div class="row">
                <input type="hidden" name="birthdate" id="wcp-birthdate" value="" />
                <select name="days" id="wcp-day" onchange="checkbirthday();" required>
                    <option value="0">-</option>
                    {foreach from=$days item=v}
                        <option value="{$v}" {if ($bDay == $v)}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
                <select name="months" id="wcp-month" onchange="checkbirthday()" required>
                    <option value="0">-</option>
                    {foreach from=$months item=v}
                        <option value="{$v}" {if ($bMonth == $v)}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
                <select name="years" id="wcp-year" onchange="checkbirthday()" required>
                    <option value="0">-</option>
                    {foreach from=$years item=v}
                        <option value="{$v}" {if ($bYear == $v)}selected="selected"{/if}>{$v}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear" style="content:''; clear:both; float:none;"></div>
            <span id="wcpPayolutionAging" style="color:red;font-weight:bold;display:none;">
		                    {s name="WirecardCheckoutPageBirthdayInformation"}Sie müssen mindestens 18 Jahre alt sein, um dieses Zahlungsmittel nutzen zu können.{/s}
		                </span>
        </div>
        {if $payolutionTerms}
            <div class="payment--selection-label is--underline">
                {s name="WirecardCheckoutPagePayolutionTermsHeader"}Payolution Konditionen{/s}
            </div>
                <ul class="list--checkbox list--unstyled">
                    <li class="block-group row--tos">
					            <span class="column--checkbox">
						            <input type="checkbox" required="required" aria-required="true" id="wcpInvoiceTermsChecked" onchange="checkbirthday()" name="wcpInvoiceTermsChecked">
					            </span>
                        <span class="column--checkbox">
						            <label for="wcpInvoiceTermsChecked">{if $wcpPayolutionLink1}
                                            {s name="WirecardCheckoutPagePayolutionConsent1"}Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine {/s}
                                            {$wcpPayolutionLink1}
                                            {s name="WirecardCheckoutPagePayolutionLink"}Bewilligung{/s}
                                            {$wcpPayolutionLink2}
                                            {s name="WirecardCheckoutPagePayolutionConsent2"} kann ich jederzeit mit Wirkung für die Zukunft widerrufen.{/s}
                                        {else}
                                            {s name="WirecardCheckoutPagePayolutionConsent1"}Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine {/s}
                                            {s name="WirecardCheckoutPagePayolutionLink"}Bewilligung{/s}
                                            {s name="WirecardCheckoutPagePayolutionConsent2"} kann ich jederzeit mit Wirkung für die Zukunft widerrufen.{/s}
                                        {/if}
						            </label>
					            </span>
                    </li>
                </ul>
            <span id="wcpPayolutionTermsAccept" style="color:red;font-weight:bold;display:none;">
                            {s name="WirecardCheckoutPagePayolutionTermsAccept"}Bitte akzeptieren Sie die payolution Konditionen.{/s}
                        </span>
            <div class="clear" style="content:''; clear:both; float:none;"></div>
            <div class="wirecard--clearer"></div>
        {/if}
    </div>
        </div>
{/if}
{/block}