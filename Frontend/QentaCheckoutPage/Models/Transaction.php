<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Qenta Payment CEE GmbH
 * (abbreviated to Qenta CEE) and are explicitly not part of the Qenta CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Qenta CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Qenta CEE does not guarantee their full
 * functionality neither does Qenta CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Qenta CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

/**
 * helper class for managing transaction data
 */
class Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Transaction
{

    /**
     * @param $qentaCheckoutPageId
     * @param $hash
     * @param $method
     * @param $sessionData
     *
     * @throws Zend_Db_Adapter_Exception
     */
    public function create($qentaCheckoutPageId, $hash, $method, $sessionData)
    {
        $sql = 'INSERT INTO `qenta_checkout_page` '
               . '(`uniqueId`, `hash`, `state`, `orderdate`, `method`, `transactionId`, `session`, `remoteAddr`) '
               . 'VALUES '
               . '(:uniqueId, :hash, :state, :orderdate, :method, :transactionId, :sessiondata, :remoteAddr) '
               . 'ON DUPLICATE KEY UPDATE '
               . '`hash` = :hash, '
               . '`state` = :state, '
               . '`orderdate` = :orderdate, '
               . '`transactionId` =  :transactionId, '
               . '`method` = :method, '
               . '`session` = :sessiondata, '
               . '`remoteAddr` = :remoteAddr';

        Shopware()->Db()->query(
            $sql,
            array(
                ':uniqueId'      => $qentaCheckoutPageId,
                ':hash'          => $hash,
                ':orderdate'     => date('Y-m-d H:i:s'),
                ':state'         => 'progress',
                ':transactionId' => uniqid(),
                ':method'        => $method,
                ':sessiondata'   => serialize($sessionData), // store session data for server2server request
                ':remoteAddr'    => $_SERVER['REMOTE_ADDR']
            )
        );

    }

    /**
     * @param $qentaCheckoutPageId
     *
     * @return mixed
     */
    public function read($qentaCheckoutPageId)
    {
        $sql = Shopware()->Db()->select()
            ->from('qenta_checkout_page')
            ->where('uniqueId = ?', array($qentaCheckoutPageId));

        return Shopware()->Db()->fetchRow($sql);
    }

    /**
     * @param $qentaCheckoutPageId
     * @param array $update
     *
     * @throws Zend_Db_Adapter_Exception
     */
    public function update($qentaCheckoutPageId, $update)
    {
        Shopware()->Db()->update(
            'qenta_checkout_page',
            $update,
            "uniqueId = '$qentaCheckoutPageId'"
        );
    }

    /**
     * generates a internal hash to validate returned payment
     *
     * @param $id
     * @param $amount
     * @param $currencycode
     *
     * @return string
     */
    public function generateHash($id, $amount, $currencycode)
    {
        return md5(
            Shopware()->QentaCheckoutPage()->getConfig()->SECRET . '|' . $id . '|' . $amount . '|' . $currencycode
        );
    }

}
