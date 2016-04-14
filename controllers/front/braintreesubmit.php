<?php
/**
 * Created by PhpStorm.
 * User: Sébastien
 * Date: 04/04/2016
 * Time: 10:05
 */

include_once(_PS_MODULE_DIR_.'paypal/classes/Braintree.php');
include_once(_PS_MODULE_DIR_.'paypal/paypal.php');

class PayPalBraintreeSubmitModuleFrontController extends ModuleFrontController
{


    public function __construct()
    {
        parent::__construct();

        if (class_exists('Context')) {
            $this->context = Context::getContext();
        } else {
            global $smarty, $cookie;
            $this->context = new StdClass();
            $this->context->smarty = $smarty;
            $this->context->cookie = $cookie;
        }
    }

    public function postProcess()
    {
        global $useSSL;
        $useSSL = true;


        $paypal = new PayPal();
        $braintree = new PrestaBraintree();
        $id_account_braintree = $paypal->set_good_context();

        if(empty($this->context->cart->id))
        {
            $paypal->reset_context();
            $this->redirectFailedPayment();
        }

        
        $cart_status = $braintree->cartStatus($this->context->cart->id);
        switch($cart_status) {
            case 'alreadyTry':

                $response = $braintree->checkStatus($this->context->cart->id);
                if ($response) {
                    $paypal->validateOrder($this->context->cart->id, Configuration::get('PS_OS_PAYMENT'), $this->context->cart->getOrderTotal(), $paypal->displayName, $paypal->l('Payment accepted.'));
                    $order_id = Order::getOrderByCartId($this->context->cart->id);
                    $this->redirectConfirmation($paypal->id,$this->context->cart->id,$order_id,Context::getContext()->customer->secure_key);
                } else {
                    $paypal->reset_context();
                    $this->redirectFailedPayment();
                }
                break;
            case 'alreadyUse':
                $order_id = Order::getOrderByCartId($this->context->cart->id);
                $this->redirectConfirmation($paypal->id,$this->context->cart->id,$order_id,Context::getContext()->customer->secure_key);
                break;
            default:
                $id_braintree_presta = $braintree->saveTransaction(array('id_cart' => $this->context->cart->id, 'nonce_payment_token' => Tools::getValue('payment_method_nonce'), 'client_token' => Tools::getValue('client_token'), 'datas' => Tools::getValue('deviceData')));
                
                $transaction_id = $braintree->sale($this->context->cart, $id_account_braintree, Tools::getValue('payment_method_nonce'), Tools::getValue('deviceData'));
                if (!$transaction_id) {
                    $paypal->reset_context();
                    $this->redirectFailedPayment();
                }
                $paypal->validateOrder($this->context->cart->id, Configuration::get('PS_OS_PAYMENT'), $this->context->cart->getOrderTotal(), $paypal->displayName, $paypal->l('Payment accepted.'));
                $paypal->reset_context();
                $order_id = Order::getOrderByCartId($this->context->cart->id);
                $braintree->updateTransaction($id_braintree_presta,$transaction_id,$order_id);
                $this->redirectConfirmation($paypal->id,$this->context->cart->id,$order_id,Context::getContext()->customer->secure_key);
                break;
        }
    }

    public function redirectFailedPayment()
    {
        Tools::redirect($this->context->link->getPageLink('order.php'));
    }

    public function redirectConfirmation($id_paypal,$id_cart,$id_order,$key)
    {
        Tools::redirect($this->context->link->getPageLink('order-confirmation.php?id_module='.$id_paypal.'&id_cart='.$id_cart.'&id_order='.$id_order.'&key='.Context::getContext()->customer->secure_key));
    }
}