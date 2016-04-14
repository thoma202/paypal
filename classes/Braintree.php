<?php

include_once _PS_MODULE_DIR_.'paypal/api/sdk/braintree/lib/Braintree.php';

class PrestaBraintree{

    /**
     * initialize config of braintree
     */
    private function initConfig()
    {
        Braintree_Configuration::merchantId(Configuration::get('PAYPAL_BRAINTREE_MERCHANT_ID'));
        Braintree_Configuration::publicKey(Configuration::get('PAYPAL_BRAINTREE_PUBLIC_KEY'));
        Braintree_Configuration::privateKey(Configuration::get('PAYPAL_BRAINTREE_PRIVATE_KEY'));
        Braintree_Configuration::environment((Configuration::get('PAYPAL_SANDBOX')?'sandbox':'production'));
    }

    /**
     * @param $id_account_braintree
     * @return bool
     */
    public function createToken($id_account_braintree)
    {
        try{
            $this->initConfig();

            $clientToken = Braintree_ClientToken::generate([
                'merchantAccountId'=>$id_account_braintree,
            ]);
            return $clientToken;
        }catch(Exception $e){
            PrestaShopLogger::addLog($e->getCode().'=>'.$e->getMessage());
            return false;
        }
    }

    /**
     * @param $id_account_braintree
     * @return mixed
     */
    public function sale($cart,$id_account_braintree,$token_payment,$device_data)
    {

        $this->initConfig();

        $address_billing = new Address($cart->id_address_invoice);
        $country_billing = new Country($address_billing->id_country);
        $address_shipping = new Address($cart->id_address_delivery);
        $country_shipping = new Country($address_shipping->id_country);

        try{
            $data = [
                'amount'                => $cart->getOrderTotal(),
                'paymentMethodNonce'    => $token_payment,
                'merchantAccountId'     => $id_account_braintree,
                'orderId'               => $cart->id,
                'billing' => [
                    'firstName'         => $address_billing->firstname,
                    'lastName'          => $address_billing->lastname,
                    'company'           => $address_billing->company,
                    'streetAddress'     => $address_billing->address1,
                    'extendedAddress'   => $address_billing->address2,
                    'locality'          => $address_billing->city,
                    'postalCode'        => $address_billing->postcode,
                    'countryCodeAlpha2' => $country_billing->iso_code,
                ],
                'shipping' => [
                    'firstName'         => $address_shipping->firstname,
                    'lastName'          => $address_shipping->lastname,
                    'company'           => $address_shipping->company,
                    'streetAddress'     => $address_shipping->address1,
                    'extendedAddress'   => $address_shipping->address2,
                    'locality'          => $address_shipping->city,
                    'postalCode'        => $address_shipping->postcode,
                    'countryCodeAlpha2' => $country_shipping->iso_code,
                ],
                "deviceData"            => $device_data,

                'options' => [
                    'submitForSettlement' => true,//Configuration::get('PAYPAL_CAPTURE_OR_DIRECT'),
                    'three_d_secure' => [
                        'required' => Configuration::get('PAYPAL_USE_3D_SECURE')
                    ]
                ]
            ];
            $result = Braintree_Transaction::sale($data);
            if(($result instanceof Braintree_Result_Successful) && $result->success)
            {
                return $result->transaction->id;
            }

        }catch(Exception $e){
            return false;
        }

        return true;
    }

    public function saveTransaction($data)
    {
        Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'paypal_braintree`(`id_cart`,`nonce_payment_token`,`client_token`,`datas`)
			VALUES ('.$data['id_cart'].',\''.$data['nonce_payment_token'].'\',\''.$data['client_token'].'\',\''.$data['datas'].'\')');
        return Db::getInstance()->Insert_ID();
    }

    public function updateTransaction($braintree_id,$transaction,$id_order)
    {
        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'paypal_braintree` set transaction=\''.$transaction.'\', id_order = '.$id_order.' WHERE id_paypal_braintree = '.$braintree_id);
    }

    public function checkStatus($id_cart)
    {
        echo '<pre>';
        $this->initConfig();
        try{
            $collection = Braintree_Transaction::search(
                array(
                    Braintree_TransactionSearch::orderId()->is($id_cart)
                )
            );

            $transaction = Braintree_Transaction::find($collection->_ids[0]);

        }catch(Exception $e){
            PrestaShopLogger::addLog($e->getCode().'=>'.$e->getMessage());
            return false;
        }
        return ($transaction instanceof Braintree_Transaction);
    }

    public function check3DSecure()
    {

    }

    public function cartStatus($id_cart)
    {

        $sql = 'SELECT *
FROM '._DB_PREFIX_.'paypal_braintree
WHERE id_cart = '.$id_cart;

        $result = Db::getInstance()->getRow($sql);
        if(!empty($result['id_paypal_braintree']))
        {
            if(!empty($result['id_order']))
            {
                return 'alreadyUse';
            }
            return 'alreadyTry';
        }
        else
        {
            return false;
        }

    }

    public function getTransactionId($id_order)
    {
        $result = Db::getInstance()->getValue('SELECT transaction FROM `'._DB_PREFIX_.'paypal_braintree` WHERE id_order = '.$id_order);
        return $result;
    }

    public function refund($transactionId,$amount)
    {
        $this->initConfig();
        try{
            $result = Braintree_Transaction::refund($transactionId,$amount);

            var_dump($result);
            if($result->success)
            {
                return true;
            }
            elseif(true)
            {
                var_dump($result->errors->_nested);
                die;
            }
        }catch (Exception $e){
            var_dump($e);
            die;
            PrestaShopLogger::addLog($e->getCode().'=>'.$e->getMessage());
            return false;
        }
    }



}