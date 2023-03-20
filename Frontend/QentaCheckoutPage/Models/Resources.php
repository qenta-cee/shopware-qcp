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
 * class responsible for communication with Resources e.G. Components/Models
 *
 * Resources of the plugin:
 *  - Returns singleton instances of classes
 *  - Get and set session variables
 *  - Returns internal short names of payment methods
 *  - Returns user data as object
 */
class Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Resources
{
    /**
     * @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Basket
     */
    protected $oBasket;

    /**
     * @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Config
     */
    protected $oConfig;

    /**
     * @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Page
     */
    protected $oPage;

    /**
     * @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Transaction
     */
    protected $oTransaction;

    /**
     * @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_PaymentMethods
     */
    protected $oPaymentMethods;

    /**
     * @var Shopware_Plugins_Frontend_QentaCheckoutPage_Components_Loader
     */
    protected $oLoader;

    protected $aPaymentStatus = Array(
        'SUCCESS' => 12,
        'PENDING' => 19,
        'FAILURE' => 21,
        'CANCEL' => 35
    );

    /**
     * @return Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Basket
     */
    public function getBasket()
    {
        if(!$this->oBasket)
        {
            $this->oBasket = new Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Basket();
        }
        return $this->oBasket;
    }

    /**
     * @return Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Config
     */
    public function getConfig()
    {
        if(!$this->oConfig)
        {
            $this->oConfig = new Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Config();
        }
        return $this->oConfig;
    }

    /**
     * @return Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Page
     */
    public function getPage()
    {
        if(!$this->oPage)
        {
            $this->oPage = new Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Page();
        }
        return $this->oPage;
    }

    public function getLoader()
    {
        if(!$this->oLoader)
        {
            $this->oLoader = new Shopware_Plugins_Frontend_QentaCheckoutPage_Components_Loader();
        }
        return $this->oLoader;
    }

    /**
     * @return Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Transaction
     */
    public function getTransaction()
    {
        if(!$this->oTransaction)
        {
            $this->oTransaction = new Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Transaction();
        }
        return $this->oTransaction;
    }

    /**
     * @return Shopware_Plugins_Frontend_QentaCheckoutPage_Models_PaymentMethods
     */
    public function getPaymentMethods()
    {
        if(!$this->oPaymentMethods)
        {
            $this->oPaymentMethods = new Shopware_Plugins_Frontend_QentaCheckoutPage_Models_PaymentMethods();
        }
        return $this->oPaymentMethods;
    }

    /**
     * Save value of session variable
     *
     * Our kind of session management:
     * Save session variable in array with prefix of the plugin name
     *
     * @param null $var
     * @param null $val
     */
    public function __set($var = null, $val = null)
    {
        Shopware()->Session()->offsetSet(Shopware_Plugins_Frontend_QentaCheckoutPage_Bootstrap::NAME . '_' . $var, serialize($val));
    }

    /**
     * Returns value of session variable
     * @param null $var
     * @return null
     */
    public function __get($var = null)
    {
        if (!empty($var) && Shopware()->Session()->has(Shopware_Plugins_Frontend_QentaCheckoutPage_Bootstrap::NAME . '_' . $var)) {
            return unserialize(Shopware()->Session()->offsetGet(Shopware_Plugins_Frontend_QentaCheckoutPage_Bootstrap::NAME . '_' . $var));
        } else {
            return null;
        }
    }

    /**
     * @param $sStatus
     * @return int
     */
    public function getPaymentStatusId($sStatus)
    {
        if(array_key_exists($sStatus, $this->aPaymentStatus))
        {
            return $this->aPaymentStatus[$sStatus];
        }
        return $this->aPaymentStatus[QentaCEE_Stdlib_ReturnFactoryAbstract::STATE_FAILURE];
    }

    /**
     * Returns given part of user data as object.
     *
     * @param string $key
     * @return null|object
     */
    public function getUser($key = '')
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData']['additional'][$key])) {
            return (object)Shopware()->Session()->sOrderVariables['sUserData']['additional'][$key];
        }
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'][$key])) {
            return (object)Shopware()->Session()->sOrderVariables['sUserData'][$key];
        } elseif (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return (object)Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return null;
        }
    }

    /**
     * Returns short name of payment methods without prefix
     * example: saved shortname qenta_ccard returns ccard
     *
     * @return null|string
     */
    public function getPaymentShortName()
    {
        $aPaymentType = $this->getPaymentMethods()->getOneByFullName(Shopware()->Container()->get('QentaCheckoutPage')->getUser('payment')->name);
        return $aPaymentType['call'];
    }

    /**
     * @return string
     */
    public function getOrderDescription()
    {
        return $this->getTransactionId();
    }

    /**
     * @return string
     */
    public function getOrderReference()
    {
        return $this->getTransactionId();
    }

    /**
     * @return string
     */
    public function getCustomerStatement()
    {
        return $this->createTransactionUniqueId(7);
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        if($this->transactionId == null)
        {
            $this->transactionId = $this->createTransactionUniqueId();
        }
        return $this->transactionId;
    }

    /**
     * returns a uniq String with default length 10.
     *
     * @param int $length
     * @return string
     */
    public function createTransactionUniqueId($length = 10)
    {
        $tid = '';

        $alphabet = "023456789abcdefghikmnopqrstuvwxyzABCDEFGHIKMNOPQRSTUVWXYZ";

        for ($i = 0; $i < $length; $i++)
        {
            $c = substr($alphabet, mt_rand(0, strlen($alphabet) - 1), 1);

            if ((($i % 2) == 0) && !is_numeric($c))
            {
                $i--;
                continue;
            }
            if ((($i % 2) == 1) && is_numeric($c))
            {
                $i--;
                continue;
            }

            $alphabet = str_replace($c, '', $alphabet);
            $tid .= $c;
        }

        return $tid;
    }
}
