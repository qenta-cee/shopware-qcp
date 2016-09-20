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

{/block}
