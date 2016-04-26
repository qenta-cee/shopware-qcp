<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and explicitly do not form part of the Wirecard CEE range
 * of products and services.
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed to third parties under
 * the same terms.
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any
 * errors occurring when used in an enhanced, customized shop system configuration.
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 * The customer uses the plugin at own risk. Wirecard CEE does not guarantee its full
 * functionality neither does Wirecard CEE assume liability for any disadvantage related
 * to the use of this plugin. Additionally Wirecard CEE does not guarantee its full
 * functionality for customized shop systems or installed plugins of other vendors of
 * plugins within the same shop system.
 * The customer is responsible for testing the plugin's functionality within its own shop
 * system before using it within a production environment of a shop system.
 * By installing the plugin to the shop system the customer agrees to the terms of use.
 * Please do not use these plugins if you do not agree to this terms of use!
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
        $oFrontendClient->setPaymentType($paymentType)
                        ->setAmount($amount)
                        ->setCurrency($currency)
                        ->setSuccessUrl($returnUrl)
                        ->setCancelUrl($returnUrl)
                        ->setFailureUrl($returnUrl)
                        ->setPendingUrl($returnUrl)
                        ->setConfirmUrl($confimUrl)
                        ->setDisplayText($confimUrl)
                        ->setConsumerData($this->getConsumerData($paymentType));

        $oFrontendClient->generateCustomerStatement(Shopware()->WirecardCheckoutPage()->getConfig()->getShopName());

        // add custom params, will be send back by wirecard
        foreach ($params as $k => $v)
            $oFrontendClient->$k = $v;

        Shopware()->WirecardCheckoutPage()->getLog()->Debug(__METHOD__ . ':' . print_r($oFrontendClient->getRequestData(),true));

        try {
            return $oFrontendClient->initiate();
        } catch (\Exception $e) {
            Shopware()->WirecardCheckoutPage()->getLog()->Err(__METHOD__ . ':' . $e->getMessage());
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
        return WirecardCEE_QPay_FrontendClient::generatePluginVersion(
            'Shopware',
            Shopware()->Config()->sVERSION,
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

        if(!Shopware()->WirecardCheckoutPage()->getConfig()->send_additional_data)
        {
            switch($paymentType)
            {
                case WirecardCEE_QPay_PaymentType::P24:
                    $consumerData->setEmail(Shopware()->WirecardCheckoutPage()->getUser('user')->email);
                    break;
                case WirecardCEE_QPay_PaymentType::INSTALLMENT:
                case WirecardCEE_QPay_PaymentType::INVOICE:
                    $consumerData->setEmail(Shopware()->WirecardCheckoutPage()->getUser('user')->email);
                    $consumerData->addAddressInformation($this->getAddress('billing'));
                    $birthday = $this->getDateObject(Shopware()->WirecardCheckoutPage()->getUser('billingaddress')->birthday);
                    if (FALSE !== $birthday) {
                        $consumerData = $consumerData->setBirthDate($birthday);
                    }
                    break;
            }
        }
        else
        {
            $consumerData->setEmail(Shopware()->WirecardCheckoutPage()->getUser('user')->email);
            $consumerData->addAddressInformation($this->getAddress('billing'));
            $consumerData->addAddressInformation($this->getAddress('shipping'));
            $birthday = $this->getDateObject(Shopware()->WirecardCheckoutPage()->getUser('billingaddress')->birthday);
            if (FALSE !== $birthday) {
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

}
