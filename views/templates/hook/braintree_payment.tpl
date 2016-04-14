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
			<form action="{$braintreeSubmitUrl}" id="braintree-form" method="post" onsubmit="check3DSecure()">
				<label for="card-number">Card Number</label>
				<div id="card-number"></div>

				<label for="cvv">CVV</label>
				<div id="cvv"></div>

				<label for="expiration-date">Expiration Date</label>
				<div id="expiration-date"></div>
				<input type="hidden" name="deviceData" class="deviceData"/>
				<input type="hidden" name="client_token" value="{$braintreeToken}">
				<input type="submit" value="{l s='Pay' mod='paypal'}"  id="braintree_submit"/>
			</form>
		<button onclick="check3DSecure()">test</button>
		</p>
	</div>
</div>

{literal}
	<script src="https://js.braintreegateway.com/js/braintree-2.22.2.min.js"></script>
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
			onReady : function(braintreeInstance) {
				//On remplit un champ hidden deviceData du fomulaire avec braintreeInstance.deviceData
				$('.deviceData').val(braintreeInstance.deviceData);
			},
			onPaymentMethodReceived: function (obj) {
				if (obj.type == 'CreditCard') {
					var client = new braintree.api.Client({clientToken: "CLIENT-TOKEN-FROM-SERVER"});
					client.verify3DS({
								amount: 10.00,
								creditCard: obj.nonce
							},
							function (error, response) {
								if (!error) {
									alert(response.verificationDetails.liabilityShifted);
									var liabilityShifted = response.verificationDetails.liabilityShifted
									var liabilityShiftPossible = response.verificationDetails.liabilityShiftPossible
									// check liability shift status and post nonce to server
								}
								Form.submit
							});
				}
				else {
					Form.submit
				}
			}
		});

		var client = new braintree.api.Client({
			clientToken: "{/literal}{$braintreeToken}{literal}"
		});

		function check3DSecure()
		{
			client.verify3DS({
				amount: {/literal}{$braintreeAmount}{literal},
				clientToken: "{/literal}{$braintreeToken}{literal}"
			}, function (error, response) {
				console.debug(error);
				alert(error.message);
				alert(response);
			});
			return false;
		}

	</script>
{/literal}
