{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


{*Displaying a button or the iframe*}
<div class="row braintree-row-payment">
	<div class="col-xs-12">
		<p class="payment_module">
			<form action="{$braintreeSubmitUrl}" id="braintree-form" method="post">
				<div id="block-card-number">
					<label for="card-number">Card Number</label>
					<div id="card-number"></div>
				</div>

				<div id="block-cvv">
					<label for="cvv">CVV</label>
					<div id="cvv"></div>
				</div>

				<div id="block-expiration-date">
					<label for="expiration-date">Expiration Date</label>
					<div id="expiration-date"></div>
				</div>


				<input type="hidden" name="deviceData" id="deviceData"/>
				<input type="hidden" name="client_token" value="{$braintreeToken}">
				<input type="hidden" name="liabilityShifted" id="liabilityShifted"/>
				<input type="hidden" name="liabilityShiftPossible" id="liabilityShiftPossible"/>
				<input type="hidden" name="payment_method_nonce" id="payment_method_nonce"/>

			<input type="submit" value="{l s='Pay' mod='paypal'}"  id="braintree_submit"/>
			</form>
		</p>
	</div>
</div>

{literal}
	<script src="https://js.braintreegateway.com/js/braintree-2.24.0.min.js"></script>
	<script type="text/javascript">
		braintree.setup("{/literal}{$braintreeToken}{literal}", "custom", {
			id: "braintree-form",
			hostedFields: {
				number: {
					selector: "#card-number"
				},
				cvv: {
					selector: "#cvv"
				},
				expirationDate: {
					selector: "#expiration-date"
				}
			},
			dataCollector: {
				kount: {environment: {/literal}{if $sandbox_mode}'sandbox'{else}'production'{/if}{literal}}
			},
			onReady : function(braintreeInstance) {
				//On remplit un champ hidden deviceData du fomulaire avec braintreeInstance.deviceData
				alert(braintreeInstance.deviceData);
				$('#deviceData').val(braintreeInstance.deviceData);
			},
			onError : function(error) {
				$.fancybox.open([
					{
						type: 'inline',
						autoScale: true,
						minHeight: 30,
						content: '<p class="braintree-error">' + error.message + '</p>'
					}
				]);
			},
			onPaymentMethodReceived: function (obj) {
				if (obj.type == 'CreditCard') {

					var client = new braintree.api.Client({clientToken: "{/literal}{$braintreeToken}{literal}"});
					client.verify3DS({
								amount: {/literal}{$braintreeAmount}{literal},
								creditCard: obj.nonce
							},
							function (error, response) {
								if (!error) {
									$('#payment_method_nonce').val(response.nonce);
									$('#liabilityShifted').val(response.verificationDetails.liabilityShifted);
									$('#liabilityShiftPossible').val(response.verificationDetails.liabilityShiftPossible);
								}
								else
								{
									$.fancybox.open([
										{
											type: 'inline',
											autoScale: true,
											minHeight: 30,
											content: '<p class="braintree-error">' + error.message + '</p>'
										}
									]);
								}
								$('#braintree-form').submit();
							});
				}
				else {
					$('#braintree-form').submit();
				}
			}
		});

		var client = new braintree.api.Client({
			clientToken: "{/literal}{$braintreeToken}{literal}"
		});

	</script>
{/literal}
