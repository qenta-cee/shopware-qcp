{namespace name='frontend/WirecardCheckoutPage/payment'}


{if $sUserData.additional.payment.name == 'wcp_installment' }
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
				$('#wcpPayolutionAging').hide();
				$('.is--primary').attr('disabled', false);
			}
			else {
				$('#wcp-birthdate').val("");
				if($('#wcp-day').is(":visible") == true ) {
					$('#wcpPayolutionAging').show();
					$('.is--primary').attr('disabled', true);
				}
			}
		}
	</script>

<div class="payment--content">
	<div class="payment--selection-label is--underline" name="birthdate">{s name="WirecardCheckoutPageBirthday"}Geburtsdatum{/s}</div>
	<div class="payment--form-group">
		<div class="row">
			<input type="hidden" name="birthdate" id="wcp-birthdate" value="" />
			<div class="column--quantity">
				<select name="days" id="wcp-day" class="form-control days input-sm" onchange="checkBirthday()" required>
					<option value="">-</option>
					{foreach from=$days item=v}
						<option value="{$v}" {if ($bDay == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
					{/foreach}
				</select>
			</div>
			<div class="column--quantity">
				<select name="months" id="wcp-month" class="form-control months input-sm" onchange="checkBirthday()" required>
					<option value="">-</option>
					{foreach from=$months key=k item=v}
						<option value="{$k}" {if ($bMonth == $k)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
					{/foreach}
				</select>
			</div>
			<div class="column--quantity">
				<select name="years" id="wcp-year" class="form-control years input-sm" onchange="checkBirthday()" required>
					<option value="">-</option>
					{foreach from=$years item=v}
						<option value="{$v}" {if ($bYear == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="clear" style="content:''; clear:both; float:none;"></div>
		<span id="wcpPayolutionAging" style="color:red;font-weight:bold;display:none;">
		{s name="WirecardCheckoutPageBirthdayInformation"}Sie müssen mindestens 18 Jahre alt sein, um dieses Zahlungsmittel nutzen zu können.{/s}
		</span>
	</div>
</div>
{/if}
{if $wcpPayolutionTerms && $sUserData.additional.payment.name == 'wcp_installment' }
	<div class="payment--form-group">
		<div class="payment--selection-label is--underline">
			{s name="WirecardCheckoutPagePayolutionTermsHeader"}Payolution Konditionen{/s}
		</div>
		<div class="payment--form-group">
			<ul class="list--checkbox list--unstyled">
				<li class="block-group row--tos">
					<span class="column--checkbox">
						<input type="checkbox" required="required" aria-required="true" id="wcpPayolutionTermsChecked" name="wcpPayolutionTermsChecked">
					</span>
					<span class="column--checkbox">
						<label for="wcpPayolutionTermsChecked">
							{if $wcpPayolutionLink1}
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
		</div>
	</div>
{/if}