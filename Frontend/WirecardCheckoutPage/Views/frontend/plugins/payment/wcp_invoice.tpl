{namespace name='frontend/WirecardCheckoutPage/payment'}

	<script type="text/javascript">
		window.onload = function() {
			$(document).ready(function() {
				checkBirthday();
			});
		};

		function checkBirthday() {
			var m = $('#wcp-invoice-month').val();
			var d = $('#wcp-invoice-day').val();

			var dateStr = $('#wcp-invoice-year').val() + '-' + m + '-' + d;
			var minAge = 18;

			var birthdate = new Date(dateStr);
			var year = birthdate.getFullYear();
			var today = new Date();
			var limit = new Date((today.getFullYear() - minAge), today.getMonth(), today.getDate());
			if (birthdate < limit) {
				$('#wcp-invoice-birthdate').val(dateStr);
				$('.is--primary').attr('disabled', false);
			}
			else {
				$('#wcp-invoice-birthdate').val("");
				if($('#wcp-invoice-day').is(":visible") == true ) {
					$('.is--primary').attr('disabled', true);
				}
			}
		}

	</script>

<div class="payment--content">
	<div class="payment--selection-label is--underline" name="birthdate">{s name="WirecardCheckoutPageBirthday"}Geburtsdatum{/s}</div>
	<div class="payment--form-group">
		<div class="row">
			<input type="hidden" name="birthdate" id="wcp-invoice-birthdate" value="" />
			<div class="column--quantity">
				<select name="days" id="wcp-invoice-day" class="form-control days input-sm" onchange="checkBirthday()" required>
					<option value="">-</option>
					{foreach from=$days item=v}
						<option value="{$v}" {if ($bDay == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
					{/foreach}
				</select>
			</div>
			<div class="column--quantity">
				<select name="months" id="wcp-invoice-month" class="form-control months input-sm" onchange="checkBirthday()" required>
					<option value="">-</option>
					{foreach from=$months key=k item=v}
						<option value="{$k}" {if ($bMonth == $k)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
					{/foreach}
				</select>
			</div>
			<div class="column--quantity">
				<select name="years" id="wcp-invoice-year" class="form-control years input-sm" onchange="checkBirthday()" required>
					<option value="">-</option>
					{foreach from=$years item=v}
						<option value="{$v}" {if ($bYear == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="clear" style="content:''; clear:both; float:none;"></div>
		{s name="WirecardCheckoutPageBirthdayInformation"}Sie müssen mindestens 18 Jahre alt sein, um dieses Zahlungsmittel nutzen zu können.{/s}
	</div>
</div>
{if $wcpPayolutionTerms && $sUserData.additional.payment.name == 'wcp_invoice' }
	<div class="payment--form-group">
		<div class="payment--selection-label is--underline">
			{s name="WirecardCheckoutPagePayolutionTermsHeader"}Payolution Konditionen{/s}
		</div>
		<div class="payment--form-group">
					<ul class="list--checkbox list--unstyled">
						<li class="block-group row--tos">
                        <span class="column--checkbox">
                            <input type="checkbox" required="required" aria-required="true" id="wcpInvoiceTermsChecked" name="wcpInvoiceTermsChecked">
                        </span>
							<span class="column--checkbox">
                            <label for="wcpInvoiceTermsChecked">
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
{/if}