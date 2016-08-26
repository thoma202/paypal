<?php
/**
 * Created by PhpStorm.
 * User: Sébastien
 * Date: 04/04/2016
 * Time: 10:05
 */

include_once(_PS_MODULE_DIR_.'paypal/paypal.php');

class PayPalPlusPatchModuleFrontController extends ModuleFrontController
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
        $this->ajax = true;
    }

    public function postProcess()
    {
        if(Tools::getValue('id_cart') && Tools::getValue('id_payment'))
        {
            $cart = new Cart(Tools::getValue('id_cart'));
            $address_delivery = new Address($cart->id_address_delivery);
            $ppplus = new CallApiPaypalPlus();
            $result = $ppplus->patch(Tools::getValue('id_payment'),$address_delivery);
        }
    }

}