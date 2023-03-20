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
 * class using the QentaCheckoutPage Frontend interface
 */
class Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Page
{
    /**
     * Initialize Qenta library with common user and
     * plugin config parameter
     *
     * @param Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Config $oConfig
     * @return string|QentaCEE_QPay_FrontendClient
     */
    protected function getFrontendClient(Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Config $oConfig)
    {
        $oInit = new QentaCEE_QPay_FrontendClient(array(
            'CUSTOMER_ID' => $oConfig->customerid,
            'SHOP_ID'     => $oConfig->shopid,
            'SECRET'      => $oConfig->secret,
            'LANGUAGE'    => substr(Shopware()->Container()->get('shop')->getLocale()->getLocale(), 0, 2)
        ));
        $oInit->setPluginVersion($this->getPluginVersion());

        $oInit->setMaxRetries($oConfig->max_retries);
        $oInit->setOrderReference(Shopware()->Container()->get('QentaCheckoutPage')->getOrderReference());
        $oInit->setOrderDescription(Shopware()->Container()->get('QentaCheckoutPage')->getOrderDescription());
        $oInit->setImageUrl($oConfig->getImageUrl());
        $oInit->setServiceUrl($oConfig->service_url);

        if ($oConfig->CONFIRM_MAIL == 1) {
            $oInit->setConfirmMail(Shopware()->Config()->mail);
        }

        return $oInit;
    }

    /**
     * @param $paymentType
     * @param $amount
     * @param $currency
     * @param $returnUrl
     * @param $confimUrl
     * @param array $params
     * @return QentaCEE_QPay_Response_Initiation
     */
    public function initiatePayment($paymentType, $amount, $currency, $returnUrl, $confimUrl, $params = array())
    {
        $oFrontendClient = $this->getFrontendClient(Shopware()->Container()->get('QentaCheckoutPage')->getConfig());
        if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->FINANCIAL_INSTITUTION_SELECTION_ENABLED
            && in_array(strtolower($paymentType),
                Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->getPaymentsFinancialInstitution())
        ) {
            $oFrontendClient->setFinancialInstitution(Shopware()->Container()->get('QentaCheckoutPage')->financialInstitution);
        }
        $email = (string) Shopware()->Container()->get('QentaCheckoutPage')->getUser('user')->email;

        $oFrontendClient->setPaymentType($paymentType)
                        ->setAmount($amount)
                        ->setCurrency($currency)
                        ->setSuccessUrl($returnUrl)
                        ->setCancelUrl($returnUrl)
                        ->setFailureUrl($returnUrl)
                        ->setPendingUrl($returnUrl)
                        ->setConfirmUrl($confimUrl)
                        ->setDisplayText($confimUrl)
                        ->setConsumerData($this->getConsumerData($paymentType))
                        ->createConsumerMerchantCrmId($email);

        if (Shopware()->Session()->offsetGet('qcpConsumerDeviceId') != null) {
            $oFrontendClient->consumerDeviceId = Shopware()->Session()->offsetGet('qcpConsumerDeviceId');
            //default set to null, but no effect
            Shopware()->Session()->offsetSet('qcpConsumerDeviceId', null);
        }

        if ($paymentType == \QentaCEE_QPay_PaymentType::MASTERPASS) {
            $oFrontendClient->setShippingProfile('NO_SHIPPING');
        }
        if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->SEND_BASKET_DATA
            || ($paymentType == QentaCEE_QPay_PaymentType::INVOICE && Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->INVOICE_PROVIDER != 'payolution')
            || ($paymentType == QentaCEE_QPay_PaymentType::INSTALLMENT && Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->INSTALLMENT_PROVIDER != 'payolution')
        ) {
            $oFrontendClient->setBasket($this->getShoppingBasket());
        }
        if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->ENABLE_DUPLICATE_REQUEST_CHECK){
            $oFrontendClient->setDuplicateRequestCheck(true);
        }

        $customerStatement = sprintf('%9s', substr(Shopware()->Config()->get('ShopName'), 0, 9));
        if ($paymentType != \QentaCEE_QPay_PaymentType::POLI) {
            $customerStatement .= ' ' . Shopware()->Container()->get('QentaCheckoutPage')->wQentaCheckoutPageId;
        }
        $oFrontendClient->setCustomerStatement($customerStatement);

        // add custom params, will be send back by qenta
        foreach ($params as $k => $v)
            $oFrontendClient->$k = $v;

        Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: '.__METHOD__ . ':' . print_r($oFrontendClient->getRequestData(),true));

        try {
            return $oFrontendClient->initiate();
        } catch (\Exception $e) {
            Shopware()->Container()->get('pluginlogger')->error('QentaCheckoutPage: '.__METHOD__ . ':' . $e->getMessage());
            Shopware()->Container()->get('QentaCheckoutPage')->qenta_action = 'failure';
            Shopware()->Container()->get('QentaCheckoutPage')->qenta_message = $e->getMessage();
        }

        return null;
    }

    /**
     * Returns version of this plugin
     *
     * @return string
     */
    protected function getPluginVersion()
    {
        $shopversion = '';

        if(defined('Shopware::VERSION')){
            $shopversion = Shopware::VERSION;
        } else {
            $shopversion = Shopware()->Container()->get('shopware.release')->getVersion();
        }

        if ( ! strlen($shopversion)) {
            $shopversion = '>5.7.0';
        }

        return QentaCEE_QPay_FrontendClient::generatePluginVersion(
            'Shopware',
            $shopversion,
            Shopware_Plugins_Frontend_QentaCheckoutPage_Bootstrap::NAME,
            Shopware()->Plugins()->Frontend()->QentaCheckoutPage()->getVersion()
        );
    }

    /**
     * Returns desription of customer - will be displayed in Qenta backend
     * @return string
     */
    public function getUserDescription()
    {
        return sprintf('%s %s %s',
            Shopware()->Container()->get('QentaCheckoutPage')->getUser('user')->email,
            Shopware()->Container()->get('QentaCheckoutPage')->getUser('billingaddress')->firstname,
            Shopware()->Container()->get('QentaCheckoutPage')->getUser('billingaddress')->lastname
        );
    }

    /**
     * Returns customer object
     *
     * @param $paymentType
     * @return QentaCEE_Stdlib_ConsumerData
     */
    public function getConsumerData($paymentType)
    {
        $consumerData = new QentaCEE_Stdlib_ConsumerData();
        $consumerData->setIpAddress($_SERVER['REMOTE_ADDR']);
        $consumerData->setUserAgent($_SERVER['HTTP_USER_AGENT']);

        if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->send_additional_data
            || $paymentType == QentaCEE_QPay_PaymentType::INSTALLMENT
            || $paymentType == QentaCEE_QPay_PaymentType::INVOICE
            || $paymentType == QentaCEE_QPay_PaymentType::P24
        ) {
            $consumerData->setEmail(Shopware()->Container()->get('QentaCheckoutPage')->getUser('user')->email);
            $consumerData->addAddressInformation($this->getAddress('billing'));
            $consumerData->addAddressInformation($this->getAddress('shipping'));

            $userData = Shopware()->Session()->sOrderVariables['sUserData'];
            $birthday = $userData['additional']['user']['birthday'];
            if (!empty($birthday)) {
                $birthday = $this->getDateObject($birthday);
                if (false !== $birthday) {
                    $consumerData = $consumerData->setBirthDate($birthday);
                }
            }
        }

        return $consumerData;
    }


    /**
     * Returns address object
     *
     * @param string $type
     * @return QentaCEE_Stdlib_ConsumerData_Address
     */
    protected function getAddress($type = 'billing')
    {
        $prefix = $type . 'address';
        switch ($type) {
            case 'shipping':
                $address = new QentaCEE_Stdlib_ConsumerData_Address(QentaCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING);
                break;

            default:
                $address = new QentaCEE_Stdlib_ConsumerData_Address(QentaCEE_Stdlib_ConsumerData_Address::TYPE_BILLING);
                break;
        }
        $address->setFirstname(Shopware()->Container()->get('QentaCheckoutPage')->getUser($prefix)->firstname);
        $address->setLastname(Shopware()->Container()->get('QentaCheckoutPage')->getUser($prefix)->lastname);
        $address->setAddress1(Shopware()->Container()->get('QentaCheckoutPage')->getUser($prefix)->street . ' ' . Shopware()->Container()->get('QentaCheckoutPage')->getUser($prefix)->streetnumber);
        $address->setZipCode(Shopware()->Container()->get('QentaCheckoutPage')->getUser($prefix)->zipcode);
        $address->setCity(Shopware()->Container()->get('QentaCheckoutPage')->getUser($prefix)->city);
        switch ($type) {
            case 'billing':
                $address->setCountry(Shopware()->Container()->get('QentaCheckoutPage')->getUser('country')->countryiso);
                $address->setPhone(Shopware()->Container()->get('QentaCheckoutPage')->getUser($prefix)->phone);
                break;
            case 'shipping':
                $address->setCountry(Shopware()->Container()->get('QentaCheckoutPage')->getUser('countryShipping')->countryiso);
                break;
        }
        return $address;
    }

    /**
     * Returns DateTime object of customer's birthday
     * @param string $date
     * @return bool|DateTime
     */
    protected function getDateObject($date = '')
    {
        $birthday = new DateTime($date);
        $error = $birthday->getLastErrors();
        if (0 == $error['warning_count'] && 0 == $error['error_count']) {
            return $birthday;
        }
        else {
            return FALSE;
        }
    }

    /**
     * Returns basket including basket items
     *
     * @return QentaCEE_Stdlib_Basket
     */
    protected function getShoppingBasket()
    {
        $basket = new QentaCEE_Stdlib_Basket();
        $basketContent = Shopware()->Session()->sOrderVariables['sBasket'];

        // Shopware uses fix precision (2) for number_format
        foreach ( $basketContent['content'] as $cart_item_key => $cart_item) {
            $item = new QentaCEE_Stdlib_Basket_Item($cart_item['articleID']);
            $item->setUnitGrossAmount($cart_item['price'])
                 ->setUnitNetAmount(number_format($cart_item['netprice'], 2, '.', ''))
                 ->setUnitTaxAmount(number_format($cart_item['price'] - $cart_item['netprice'], 2, '.', ''))
                 ->setUnitTaxRate($cart_item['tax_rate'])
                 ->setDescription( substr( strip_tags( $cart_item['additional_details']['description']), 0, 127 ) )
                 ->setName(isset($cart_item['additional_details']['articleName']) ? $cart_item['additional_details']['articleName'] : 'Surcharge')
                 ->setImageUrl( isset($cart_item['image']) ? $cart_item['image']['source'] : '' );

            $basket->addItem( $item, $cart_item['quantity']);
        }

        if (isset($basketContent['sShippingcosts']) && $basketContent['sShippingcosts'] > 0) {
            $item = new QentaCEE_Stdlib_Basket_Item('shipping');
            $item->setUnitGrossAmount($basketContent['sShippingcostsWithTax'])
                 ->setUnitNetAmount($basketContent['sShippingcostsNet'])
                 ->setUnitTaxRate($basketContent['sShippingcostsTax'])
                 ->setUnitTaxAmount($basketContent['sShippingcostsWithTax'] - $basketContent['sShippingcostsNet'])
                 ->setName('Shipping')
                 ->setDescription('Shipping');
            $basket->addItem($item);
        }

        return $basket;
    }

}
