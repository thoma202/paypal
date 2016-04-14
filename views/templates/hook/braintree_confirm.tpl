<div id="braintree_confirm">
    <p>{l s='Total of the transaction (taxes incl.) :' mod='paypal'} <span class="bold">{$price|escape:'htmlall':'UTF-8'}</span></p>
    <p>{l s='Your order ID is :' mod='paypal'}
        <span class="bold">
            {if isset($order.reference)}
                {$order.reference|escape:'htmlall':'UTF-8'}
            {else}
                {$order.id|intval}
            {/if}
		</span>
    </p>
    <p>{l s='Your PayPal transaction ID is :' mod='paypal'} <span class="bold">{$transaction_id|escape:'htmlall':'UTF-8'}</span></p>
</div>
<br/>