<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
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
 * class using the WirecardCheckoutPage Frontend interface
 */
class Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Page
{
    /**
     * Initialize Wirecard library with common user and
     * plugin config parameter
     *
     * @param Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Config $oConfig
     * @return string|WirecardCEE_QPay_FrontendClient
     */
    protected function getFrontendClient(Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Config $oConfig)
    {
        $oInit = new WirecardCEE_QPay_FrontendClient(array(
            'CUSTOMER_ID' => $oConfig->customerid,
            'SHOP_ID'     => $oConfig->shopid,
            'SECRET'      => $oConfig->secret,
            'LANGUAGE'    => Shopware()->Locale()->getLanguage()
        ));
        $oInit->setPluginVersion($this->getPluginVersion());

        $oInit->setMaxRetries($oConfig->max_retries);
        $oInit->setOrderReference(Shopware()->WirecardCheckoutPage()->getOrderReference());
        $oInit->setOrderDescription(Shopware()->WirecardCheckoutPage()->getOrderDescription());
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
     * @return WirecardCEE_QPay_Response_Initiation
     */
    public function initiatePayment($paymentType, $amount, $currency, $returnUrl, $confimUrl, $params = array())
    {
        $oFrontendClient = $this->getFrontendClient(Shopware()->WirecardCheckoutPage()->getConfig());
        if (Shopware()->WirecardCheckoutPage()->getConfig()->FINANCIAL_INSTITUTION_SELECTION_ENABLED
            && in_array(strtolower($paymentType),
                Shopware()->WirecardCheckoutPage()->getConfig()->getPaymentsFinancialInstitution())
        ) {
            $oFrontendClient->setFinancialInstitution(Shopware()->WirecardCheckoutPage()->financialInstitution);
        }
        $email = (string) Shopware()->WirecardCheckoutPage()->getUser('user')->email;

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

        if (Shopware()->Session()->offsetGet('wcpConsumerDeviceId') != null) {
            $oFrontendClient->consumerDeviceId = Shopware()->Session()->offsetGet('wcpConsumerDeviceId');
            //default set to null, but no effect
            Shopware()->Session()->offsetSet('wcpConsumerDeviceId', null);
        }

        if ($paymentType == \WirecardCEE_QPay_PaymentType::MASTERPASS) {
            $oFrontendClient->setShippingProfile('NO_SHIPPING');
        }
        if (Shopware()->WirecardCheckoutPage()->getConfig()->SEND_BASKET_DATA
            || ($paymentType == WirecardCEE_QPay_PaymentType::INVOICE && Shopware()->WirecardCheckoutPage()->getConfig()->INVOICE_PROVIDER != 'payolution')
            || ($paymentType == WirecardCEE_QPay_PaymentType::INSTALLMENT && Shopware()->WirecardCheckoutPage()->getConfig()->INSTALLMENT_PROVIDER != 'payolution')
        ) {
            $oFrontendClient->setBasket($this->getShoppingBasket());
        }
        if (Shopware()->WirecardCheckoutPage()->getConfig()->ENABLE_DUPLICATE_REQUEST_CHECK){
            $oFrontendClient->setDuplicateRequestCheck(true);
        }

        $customerStatement = sprintf('%9s', substr(Shopware()->Config()->get('ShopName'), 0, 9));
        if ($paymentType != \WirecardCEE_QPay_PaymentType::POLI) {
            $customerStatement .= ' ' . Shopware()->WirecardCheckoutPage()->wWirecardCheckoutPageId;
        }
        $oFrontendClient->setCustomerStatement($customerStatement);

        // add custom params, will be send back by wirecard
        foreach ($params as $k => $v)
            $oFrontendClient->$k = $v;

        Shopware()->Pluginlogger()->info('WirecardCheckoutPage: '.__METHOD__ . ':' . print_r($oFrontendClient->getRequestData(),true));

        try {
            return $oFrontendClient->initiate();
        } catch (\Exception $e) {
            Shopware()->Pluginlogger()->error('WirecardCheckoutPage: '.__METHOD__ . ':' . $e->getMessage());
            Shopware()->WirecardCheckoutPage()->wirecard_action = 'failure';
            Shopware()->WirecardCheckoutPage()->wirecard_message = $e->getMessage();
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
        $shopversion = Shopware::VERSION;
        if ( ! strlen($shopversion)) {
            $shopversion = '>5.2.21';
        }

        return WirecardCEE_QPay_FrontendClient::generatePluginVersion(
            'Shopware',
            $shopversion,
            Shopware_Plugins_Frontend_WirecardCheckoutPage_Bootstrap::NAME,
            Shopware()->Plugins()->Frontend()->WirecardCheckoutPage()->getVersion()
        );
    }

    /**
     * Returns desription of customer - will be displayed in Wirecard backend
     * @return string
     */
    public function getUserDescription()
    {
        return sprintf('%s %s %s',
            Shopware()->WirecardCheckoutPage()->getUser('user')->email,
            Shopware()->WirecardCheckoutPage()->getUser('billingaddress')->firstname,
            Shopware()->WirecardCheckoutPage()->getUser('billingaddress')->lastname
        );
    }

    /**
     * Returns customer object
     *
     * @param $paymentType
     * @return WirecardCEE_Stdlib_ConsumerData
     */
    public function getConsumerData($paymentType)
    {
        $consumerData = new WirecardCEE_Stdlib_ConsumerData();
        $consumerData->setIpAddress($_SERVER['REMOTE_ADDR']);
        $consumerData->setUserAgent($_SERVER['HTTP_USER_AGENT']);

        if (Shopware()->WirecardCheckoutPage()->getConfig()->send_additional_data
            || $paymentType == WirecardCEE_QPay_PaymentType::INSTALLMENT
            || $paymentType == WirecardCEE_QPay_PaymentType::INVOICE
            || $paymentType == WirecardCEE_QPay_PaymentType::P24
        ) {
            $consumerData->setEmail(Shopware()->WirecardCheckoutPage()->getUser('user')->email);
            $consumerData->addAddressInformation($this->getAddress('billing'));
            $consumerData->addAddressInformation($this->getAddress('shipping'));

            $userData = Shopware()->Session()->sOrderVariables['sUserData'];
            $birthday = $userData['additional']['user']['birthday'];
            $birthday = $this->getDateObject($birthday);
            if (false !== $birthday) {
                $consumerData = $consumerData->setBirthDate($birthday);
            }

        }

        return $consumerData;
    }


    /**
     * Returns address object
     *
     * @param string $type
     * @return WirecardCEE_Stdlib_ConsumerData_Address
     */
    protected function getAddress($type = 'billing')
    {
        $prefix = $type . 'address';
        switch ($type) {
            case 'shipping':
                $address = new WirecardCEE_Stdlib_ConsumerData_Address(WirecardCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING);
                break;

            default:
                $address = new WirecardCEE_Stdlib_ConsumerData_Address(WirecardCEE_Stdlib_ConsumerData_Address::TYPE_BILLING);
                break;
        }
        $address->setFirstname(Shopware()->WirecardCheckoutPage()->getUser($prefix)->firstname);
        $address->setLastname(Shopware()->WirecardCheckoutPage()->getUser($prefix)->lastname);
        $address->setAddress1(Shopware()->WirecardCheckoutPage()->getUser($prefix)->street . ' ' . Shopware()->WirecardCheckoutPage()->getUser($prefix)->streetnumber);
        $address->setZipCode(Shopware()->WirecardCheckoutPage()->getUser($prefix)->zipcode);
        $address->setCity(Shopware()->WirecardCheckoutPage()->getUser($prefix)->city);
        switch ($type) {
            case 'billing':
                $address->setCountry(Shopware()->WirecardCheckoutPage()->getUser('country')->countryiso);
                $address->setPhone(Shopware()->WirecardCheckoutPage()->getUser($prefix)->phone);
                break;
            case 'shipping':
                $address->setCountry(Shopware()->WirecardCheckoutPage()->getUser('countryShipping')->countryiso);
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
     * @return WirecardCEE_Stdlib_Basket
     */
    protected function getShoppingBasket()
    {
        $basket = new WirecardCEE_Stdlib_Basket();
        $basketContent = Shopware()->Session()->sOrderVariables['sBasket'];

        // Shopware uses fix precision (2) for number_format
        foreach ( $basketContent['content'] as $cart_item_key => $cart_item) {
            $item = new WirecardCEE_Stdlib_Basket_Item($cart_item['articleID']);
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
            $item = new WirecardCEE_Stdlib_Basket_Item('shipping');
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
