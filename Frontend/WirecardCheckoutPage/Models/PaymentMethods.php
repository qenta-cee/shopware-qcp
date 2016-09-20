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
 * Class storing all possible WCP payment methods for Shopware
 */
class Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_PaymentMethods
{
    protected $paymentMethods = array(
        'SELECT' => array(
            'name' => 'select',
            'description' => 'Auswahl auf Wirecard Checkout Page',
            'call' => WirecardCEE_QPay_PaymentType::SELECT,
            'translation' => Array( 'description' => 'Selection within wirecard checkout page', 'additionalDescription' => '')
        ),

        'CCARD' => array(
            'name' => 'ccard',
            'description' => 'Wirecard Kreditkarte',
            'call' => WirecardCEE_QPay_PaymentType::CCARD,
            'translation' => Array('description' => 'Wirecard Credit Card', 'additionalDescription' => '')
        ),

        'CCARD-MOTO' => array(
            'name' => 'ccard-moto',
            'description' => 'Wirecard Kreditkarte (Backoffice)',
            'call' => WirecardCEE_QPay_PaymentType::CCARD_MOTO,
            'translation' => Array('description' => 'Wirecard Credit Card (backoffice)', 'additionalDescription' => '')
        ),
        
        'EPS' => array(
            'name' => 'eps',
            'description' => 'Wirecard eps Online-&Uuml;berweisung',
            'call' => WirecardCEE_QPay_PaymentType::EPS,
            'translation' => Array('description' => 'Wirecard eps Online Bank Transfer', 'additionalDescription' => '')
        ),
        'IDEAL' => array(
            'name' => 'ideal',
            'description' => 'Wirecard iDEAL',
            'call' => WirecardCEE_QPay_PaymentType::IDL,
            'translation' => Array('description' => 'Wirecard iDEAL', 'additionalDescription' => '')

        ),
        'GIROPAY' => array(
            'name' => 'giropay',
            'description' => 'Wirecard giropay',
            'call' => WirecardCEE_QPay_PaymentType::GIROPAY,
            'translation' => Array('description' => 'Wirecard giropay', 'additionalDescription' => '')
        ),
        'SOFORTUEBERWEISUNG' => array(
            'name' => 'sofortueberweisung',
            'description' => 'Wirecard sofort&uuml;berweisung (PIN/TAN)',
            'call' => WirecardCEE_QPay_PaymentType::SOFORTUEBERWEISUNG,
            'translation' => Array('description' => 'Wirecard sofortbanking (PIN/TAN)', 'additionalDescription' => '')
        ),
        'BANCONTACT_MISTERCASH' => array(
            'name' => 'bancontact_mistercash',
            'description' => 'Wirecard Bancontact/Mister Cash',
            'call' => WirecardCEE_QPay_PaymentType::BMC,
            'translation' => Array('description' => 'Wirecard Bancontact/Mister Cash', 'additionalDescription' => '')
        ),
        'PRZELEWY24' => array(
            'name' => 'przelewy24',
            'description' => 'Wirecard Przelewy24',
            'call' => WirecardCEE_QPay_PaymentType::P24,
            'translation' => Array('description' => 'Wirecard Przelewy24', 'additionalDescription' => '')
        ),
        'MONETA' => array(
            'name' => 'moneta',
            'description' => 'Wirecard moneta.ru',
            'call' => WirecardCEE_QPay_PaymentType::MONETA,
            'translation' => Array('description' => 'Wirecard moneta.ru', 'additionalDescription' => '')
        ),
        'POLI' => array(
            'name' => 'poli',
            'description' => 'Wirecard POLi',
            'call' => WirecardCEE_QPay_PaymentType::POLI,
            'translation' => Array('description' => 'Wirecard POLi', 'additionalDescription' => '')
        ),
        'PBX' => array(
            'name' => 'pbx',
            'description' => 'Wirecard paybox',
            'call' => WirecardCEE_QPay_PaymentType::PBX,
            'translation' => Array('description' => 'Wirecard Mobile Phone Invoicing', 'additionalDescription' => '')
        ),
        'PSC' => array(
            'name' => 'psc',
            'description' => 'Wirecard paysafecard / Cash-Ticket',
            'call' => WirecardCEE_QPay_PaymentType::PSC,
            'translation' => Array('description' => 'Wirecard paysafecard / Cash-Ticket', 'additionalDescription' => '')
        ),
        'QUICK' => array(
            'name' => 'quick',
            'description' => 'Wirecard @Quick',
            'call' => WirecardCEE_QPay_PaymentType::QUICK,
            'translation' => Array('description' => 'Wirecard @Quick', 'additionalDescription' => '')
        ),
        'PAYPAL' => array(
            'name' => 'paypal',
            'description' => 'Wirecard PayPal',
            'call' => WirecardCEE_QPay_PaymentType::PAYPAL,
            'translation' => Array('description' => 'Wirecard PayPal', 'additionalDescription' => '')
        ),
        'ELV' => array(
            'name' => 'elv',
            'description' => 'Wirecard Lastschriftverfahren',
            'call' => WirecardCEE_QPay_PaymentType::ELV,
            'translation' => Array('description' => 'Wirecard Direct Debit', 'additionalDescription' => '')
        ),
        'SEPA-DD' => array(
            'name' => 'sepa-dd',
            'description' => 'Wirecard SEPA Lastschrift',
            'call' => WirecardCEE_QPay_PaymentType::SEPADD,
            'translation' => Array('description' => 'Wirecard SEPA Direct Debit', 'additionalDescription' => '')
        ),
        'INVOICE' => array(
            'name' => 'invoice',
            'description' => 'Wirecard Rechnung',
            'template' => 'wcp_invoice.tpl',
            'call' => WirecardCEE_QPay_PaymentType::INVOICE,
            'translation' => Array('description' => 'Wirecard Invoice', 'additionalDescription' => '')
        ),
        'INSTALLMENT' => array(
            'name' => 'installment',
            'description' => 'Wirecard Ratenzahlung',
            'template' => 'wcp_installment.tpl',
            'call' => WirecardCEE_QPay_PaymentType::INSTALLMENT,
            'translation' => Array('description' => 'Wirecard Installment', 'additionalDescription' => '')
        ),
        'MPASS' => array(
            'name' => 'mpass',
            'description' => 'Wirecard mpass',
            'call' => WirecardCEE_QPay_PaymentType::MPASS,
            'translation' => Array('description' => 'Wirecard mpass', 'additionalDescription' => '')
        ),
        'SKRILLDIRECT' => array(
            'name' => 'skrilldirect',
            'description' => 'Wirecard Skrill Direct',
            'call' => WirecardCEE_QPay_PaymentType::SKRILLDIRECT,
            'translation' => Array('description' => 'Wirecard Skrill Direct', 'additionalDescription' => '')
        ),
        'SKRILLWALLET' => array(
            'name' => 'skrillwallet',
            'description' => 'Wirecard Skrill Digital Wallet',
            'call' => WirecardCEE_QPay_PaymentType::SKRILLWALLET,
            'translation' => Array('description' => 'Wirecard Skrill Digital Wallet', 'additionalDescription' => '')
        ),
        'EKONTO' => array(
            'name' => 'ekonto',
            'description' => 'Wirecard eKonto',
            'call' => WirecardCEE_QPay_PaymentType::EKONTO,
            'translation' => Array('description' => 'Wirecard eKonto', 'additionalDescription' => '')
        ),
        'TRUSTLY' => array(
            'name' => 'trustly',
            'description' => 'Wirecard Trustly',
            'call' => WirecardCEE_QPay_PaymentType::TRUSTLY,
            'translation' => Array('description' => 'Wirecard Trustly', 'additionalDescription' => '')
        ),
        'TATRAPAY' => array(
            'name' => 'tatrapay',
            'description' => 'Wirecard TatraPay',
            'call' => WirecardCEE_QPay_PaymentType::TATRAPAY,
            'translation' => Array('description' => 'Wirecard TatraPay', 'additionalDescription' => '')
        ),
        'EPAY' => array(
            'name' => 'epay',
            'description' => 'Wirecard ePay.bg',
            'call' => WirecardCEE_QPay_PaymentType::EPAYBG,
            'translation' => Array('description' => 'Wirecard ePay.bg', 'additionalDescription' => '')
        ),
        'VOUCHER' => array(
            'name' => 'voucher',
            'description' => 'Wirecard Gutschein',
            'call' => WirecardCEE_QPay_PaymentType::VOUCHER,
            'translation' => Array('description' => 'Wirecard Voucher', 'additionalDescription' => '')
        ),
    );

    public function getList()
    {
        return $this->paymentMethods;
    }

    public function getPaymentMethodName($id = 0)
    {
        $cacheId = 'wirecardcheckoutpage_paymentmethods';
        if (Shopware()->Cache()->test($cacheId)) {
            $paymentmeans = Shopware()->Cache()->load($cacheId);
        } else {
            $sql = Shopware()->Db()
                ->select()
                ->from(
                    's_core_paymentmeans',
                    array(
                        'id',
                        'name'
                    )
                );
            $paymentmeans = Shopware()->Db()->fetchPairs($sql);
            Shopware()->Cache()->save(
                $paymentmeans,
                $cacheId,
                array(
                    'Shopware_Plugin'
                ),
                86400
            );
        }
        return (isset($paymentmeans[$id])) ? $paymentmeans[$id] : $paymentmeans;
    }

    /**
     * @param $sFullName
     * @return array
     */
    public function getOneByFullName($sFullName)
    {
        $sPaymentType = (is_null($sFullName)) ? null : strtoupper(substr(
                $sFullName,
                strpos($sFullName, '_') + 1
            ));
        if(array_key_exists($sPaymentType, $this->paymentMethods))
        {
            return $this->paymentMethods[$sPaymentType];
        }
        return Array();
    }
}