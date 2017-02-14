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
 * Class storing all possible WCP payment methods for Shopware
 */
class Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_PaymentMethods
{
    protected $paymentMethods = array(
        'SELECT' => array(
            'name' => 'select',
            'description' => 'Auswahl auf Wirecard Checkout Page',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::SELECT,
            'translation' => Array( 'description' => 'Selection within wirecard checkout page', 'additionalDescription' => '')
        ),
        'CCARD' => array(
            'name' => 'ccard',
            'description' => 'Wirecard Kreditkarte',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::CCARD,
            'translation' => Array('description' => 'Wirecard Credit Card', 'additionalDescription' => '')
        ),

        'CCARD-MOTO' => array(
            'name' => 'ccard-moto',
            'description' => 'Wirecard Kreditkarte (Backoffice)',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::CCARD_MOTO,
            'translation' => Array('description' => 'Wirecard Credit Card (backoffice)', 'additionalDescription' => '')
        ),

        //Masterpass disabled because of compatibility issues with WCS plugin
        /*'MASTERPASS' => array(
            'name' => 'masterpass',
            'description' => 'Wirecard Masterpass',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::MASTERPASS,
            'translation' => Array('description' => 'Wirecard Masterpass', 'additionalDescription' => '')
        ),*/

        'EPS' => array(
            'name' => 'eps',
            'description' => 'Wirecard eps Online-&Uuml;berweisung',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::EPS,
            'translation' => Array('description' => 'Wirecard eps Online Bank Transfer', 'additionalDescription' => '')
        ),
        'IDEAL' => array(
            'name' => 'ideal',
            'description' => 'Wirecard iDEAL',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::IDL,
            'translation' => Array('description' => 'Wirecard iDEAL', 'additionalDescription' => '')

        ),
        'GIROPAY' => array(
            'name' => 'giropay',
            'description' => 'Wirecard giropay',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::GIROPAY,
            'translation' => Array('description' => 'Wirecard giropay', 'additionalDescription' => '')
        ),
        'SOFORTUEBERWEISUNG' => array(
            'name' => 'sofortueberweisung',
            'description' => 'Wirecard sofort&uuml;berweisung (PIN/TAN)',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::SOFORTUEBERWEISUNG,
            'translation' => Array('description' => 'Wirecard sofortbanking (PIN/TAN)', 'additionalDescription' => '')
        ),
        'BANCONTACT_MISTERCASH' => array(
            'name' => 'bancontact_mistercash',
            'description' => 'Wirecard Bancontact/Mister Cash',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::BMC,
            'translation' => Array('description' => 'Wirecard Bancontact/Mister Cash', 'additionalDescription' => '')
        ),
        'PRZELEWY24' => array(
            'name' => 'przelewy24',
            'description' => 'Wirecard Przelewy24',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::P24,
            'translation' => Array('description' => 'Wirecard Przelewy24', 'additionalDescription' => '')
        ),
        'MONETA' => array(
            'name' => 'moneta',
            'description' => 'Wirecard moneta.ru',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::MONETA,
            'translation' => Array('description' => 'Wirecard moneta.ru', 'additionalDescription' => '')
        ),
        'POLI' => array(
            'name' => 'poli',
            'description' => 'Wirecard POLi',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::POLI,
            'translation' => Array('description' => 'Wirecard POLi', 'additionalDescription' => '')
        ),
        'PBX' => array(
            'name' => 'pbx',
            'description' => 'Wirecard paybox',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::PBX,
            'translation' => Array('description' => 'Wirecard Mobile Phone Invoicing', 'additionalDescription' => '')
        ),
        'PSC' => array(
            'name' => 'psc',
            'description' => 'Wirecard paysafecard / Cash-Ticket',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::PSC,
            'translation' => Array('description' => 'Wirecard paysafecard / Cash-Ticket', 'additionalDescription' => '')
        ),
        'QUICK' => array(
            'name' => 'quick',
            'description' => 'Wirecard @Quick',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::QUICK,
            'translation' => Array('description' => 'Wirecard @Quick', 'additionalDescription' => '')
        ),
        'PAYPAL' => array(
            'name' => 'paypal',
            'description' => 'Wirecard PayPal',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::PAYPAL,
            'translation' => Array('description' => 'Wirecard PayPal', 'additionalDescription' => '')
        ),
        'ELV' => array(
            'name' => 'elv',
            'description' => 'Wirecard Lastschriftverfahren',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::ELV,
            'translation' => Array('description' => 'Wirecard Direct Debit', 'additionalDescription' => '')
        ),
        'SEPA-DD' => array(
            'name' => 'sepa-dd',
            'description' => 'Wirecard SEPA Lastschrift',
            'template' => '',
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
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::MPASS,
            'translation' => Array('description' => 'Wirecard mpass', 'additionalDescription' => '')
        ),
        'SKRILLDIRECT' => array(
            'name' => 'skrilldirect',
            'description' => 'Wirecard Skrill Direct',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::SKRILLDIRECT,
            'translation' => Array('description' => 'Wirecard Skrill Direct', 'additionalDescription' => '')
        ),
        'SKRILLWALLET' => array(
            'name' => 'skrillwallet',
            'description' => 'Wirecard Skrill Digital Wallet',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::SKRILLWALLET,
            'translation' => Array('description' => 'Wirecard Skrill Digital Wallet', 'additionalDescription' => '')
        ),
        'EKONTO' => array(
            'name' => 'ekonto',
            'description' => 'Wirecard eKonto',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::EKONTO,
            'translation' => Array('description' => 'Wirecard eKonto', 'additionalDescription' => '')
        ),
        'TRUSTLY' => array(
            'name' => 'trustly',
            'description' => 'Wirecard Trustly',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::TRUSTLY,
            'translation' => Array('description' => 'Wirecard Trustly', 'additionalDescription' => '')
        ),
        'TATRAPAY' => array(
            'name' => 'tatrapay',
            'description' => 'Wirecard TatraPay',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::TATRAPAY,
            'translation' => Array('description' => 'Wirecard TatraPay', 'additionalDescription' => '')
        ),
        'EPAY' => array(
            'name' => 'epay',
            'description' => 'Wirecard ePay.bg',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::EPAYBG,
            'translation' => Array('description' => 'Wirecard ePay.bg', 'additionalDescription' => '')
        ),
        'VOUCHER' => array(
            'name' => 'voucher',
            'description' => 'Wirecard Gutschein',
            'template' => '',
            'call' => WirecardCEE_QPay_PaymentType::VOUCHER,
            'translation' => Array('description' => 'Wirecard Voucher', 'additionalDescription' => '')
        )
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