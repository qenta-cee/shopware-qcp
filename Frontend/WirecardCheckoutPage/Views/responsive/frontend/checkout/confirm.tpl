{extends file="parent:frontend/checkout/confirm.tpl"}

{namespace name="frontend/checkout/confirm"}

{block name="frontend_index_header_javascript" append}
    <script type="text/javascript">
        window.onload = function() {
            $(document).ready(function() {
                checkBirthday();
            });
        };

        function checkBirthday() {
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
                $('.is--primary').attr('disabled', false);
            }
            else {
                $('#wcp-birthdate').val("");
                if($('#wcp-day').is(":visible") == true ) {
                    $('.is--primary').attr('disabled', true);
                }
            }
        }

    </script>
{/block}


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

{block name='frontend_checkout_confirm_product_table' prepend}
    {if $sUserData.additional.payment.name == 'wcp_invoice' || $sUserData.additional.payment.name == 'wcp_installment'}
<div class="information--panel-item">
    <div class="tos--panel panel has--border">
    <div class="panel--title primary is--underline" name="birthdate">{s name="WirecardCheckoutPageBirthday"}Geburtsdatum{/s}</div>
        <div class="panel--body is--wide">
        <div class="row">
            <input type="hidden" name="birthdate" id="wcp-birthdate" value="" />
            <div class="col-xs-1">
                <select name="days" id="wcp-day" class="form-control days input-sm" onchange="checkBirthday()" required>
                    <option value="">-</option>
                    {foreach from=$days item=v}
                        <option value="{$v}" {if ($bDay == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-xs-1">
                <select name="months" id="wcp-month" class="form-control months input-sm" onchange="checkBirthday()" required>
                    <option value="">-</option>
                    {foreach from=$months key=k item=v}
                        <option value="{$k}" {if ($bMonth == $k)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-xs-1">
                <select name="years" id="wcp-year" class="form-control years input-sm" onchange="checkBirthday()" required>
                    <option value="">-</option>
                    {foreach from=$years item=v}
                        <option value="{$v}" {if ($bYear == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
                    {/foreach}
                </select>
            </div>
        </div>
            {s name="WirecardCheckoutPageBirthdayInformation"}Sie müssen mindestens 18 Jahre alt sein, um dieses Zahlungsmittel nutzen zu können.{/s}
        </div>
        </div>
    </div>
    {/if}
    {if $wcpPayolutionTerms}
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
                                {if $wcpPayolutionLink1}
                                    {s name="WirecardCheckoutPagePayolutionConsent1"}Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine {/s}
                                    {$wcpPayolutionLink1}
                                    {s name="WirecardCheckoutPagePayoltuionLink"}Bewilligung{/s}
                                    {$wcpPayolutionLink2}
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
