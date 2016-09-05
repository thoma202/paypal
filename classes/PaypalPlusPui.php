<?php
/**
 * Created by PhpStorm.
 * User: Sébastien
 * Date: 29/08/2016
 * Time: 10:54
 */


class PaypalPlusPui extends ObjectModel{

    public $id_paypal_plus_pui;
    public $id_order;
    public $pui_informations;

    public static $definition = array(
        'table' => 'paypal_plus_pui',
        'primary' => 'id_paypal_plus_pui',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'pui_informations' => array('type' => self::TYPE_STRING),
        ),
    );


    public function getByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('paypal_plus_pui');
        $sql->where('id_order = ' .$id_order);
        return Db::getInstance()->getRow($sql);
    }
}
