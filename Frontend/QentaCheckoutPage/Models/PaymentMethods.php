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
 * Class storing all possible WCP payment methods for Shopware
 */
class Shopware_Plugins_Frontend_QentaCheckoutPage_Models_PaymentMethods
{
    protected $paymentMethods = array(
        'SELECT' => array(
            'name' => 'select',
            'description' => 'Auswahl auf Qenta Checkout Page',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qenta-logo.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::SELECT,
            'translation' => Array( 'description' => 'Selection within wirecard checkout page', 'additionalDescription' => '')
        ),
        'CCARD' => array(
            'name' => 'ccard',
            'description' => 'Qenta Kreditkarte',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_ccard.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::CCARD,
            'translation' => Array('description' => 'Qenta Credit Card', 'additionalDescription' => '')
        ),

        'CCARD-MOTO' => array(
            'name' => 'ccard-moto',
            'description' => 'Qenta Kreditkarte - Post / Telefonbestellung',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_ccard.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::CCARD_MOTO,
            'translation' => Array('description' => 'Qenta Credit Card - Mail Order and Telephone Order', 'additionalDescription' => '')
        ),

        'MASTERPASS' => array(
            'name' => 'masterpass',
            'description' => 'Qenta Masterpass',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_masterpass.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::MASTERPASS,
            'translation' => Array('description' => 'Qenta Masterpass', 'additionalDescription' => '')
        ),

        'MAESTRO' => array(
            'name' => 'maestro',
            'description' => 'Qenta Maestro SecureCode',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_maestro.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::MAESTRO,
            'translation' => Array('description' => 'Qenta Maestro SecureCode', 'additionalDescription' => '')
        ),

        'EPS' => array(
            'name' => 'eps',
            'description' => 'Qenta eps Online-&Uuml;berweisung',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_eps.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::EPS,
            'translation' => Array('description' => 'Qenta eps Online Bank Transfer', 'additionalDescription' => '')
        ),
        'IDEAL' => array(
            'name' => 'ideal',
            'description' => 'Qenta iDEAL',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_ideal.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::IDL,
            'translation' => Array('description' => 'Qenta iDEAL', 'additionalDescription' => '')

        ),
        'GIROPAY' => array(
            'name' => 'giropay',
            'description' => 'Qenta giropay',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_giropay.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::GIROPAY,
            'translation' => Array('description' => 'Qenta giropay', 'additionalDescription' => '')
        ),
        'SOFORTUEBERWEISUNG' => array(
            'name' => 'sofortueberweisung',
            'description' => 'Qenta Sofort.',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_sofortueberweisung.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::SOFORTUEBERWEISUNG,
            'translation' => Array('description' => 'Qenta Online bank transfer.', 'additionalDescription' => '')
        ),
        'BANCONTACT_MISTERCASH' => array(
            'name' => 'bancontact_mistercash',
            'description' => 'Qenta Bancontact',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_bancontact_mistercash.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::BMC,
            'translation' => Array('description' => 'Qenta Bancontact', 'additionalDescription' => '')
        ),
        'PRZELEWY24' => array(
            'name' => 'przelewy24',
            'description' => 'Qenta Przelewy24',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_przelewy24.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::P24,
            'translation' => Array('description' => 'Qenta Przelewy24', 'additionalDescription' => '')
        ),
        'MONETA' => array(
            'name' => 'moneta',
            'description' => 'Qenta moneta.ru',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_moneta.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::MONETA,
            'translation' => Array('description' => 'Qenta moneta.ru', 'additionalDescription' => '')
        ),
        'POLI' => array(
            'name' => 'poli',
            'description' => 'Qenta POLi',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_poli.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::POLI,
            'translation' => Array('description' => 'Qenta POLi', 'additionalDescription' => '')
        ),
        'PBX' => array(
            'name' => 'pbx',
            'description' => 'Qenta paybox',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_pbx.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::PBX,
            'translation' => Array('description' => 'Qenta paybox', 'additionalDescription' => '')
        ),
        'PSC' => array(
            'name' => 'psc',
            'description' => 'Qenta paysafecard',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_psc.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::PSC,
            'translation' => Array('description' => 'Qenta paysafecard', 'additionalDescription' => '')
        ),
        'PAYPAL' => array(
            'name' => 'paypal',
            'description' => 'Qenta PayPal',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_paypal.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::PAYPAL,
            'translation' => Array('description' => 'Qenta PayPal', 'additionalDescription' => '')
        ),
        'SEPA-DD' => array(
            'name' => 'sepa-dd',
            'description' => 'Qenta SEPA Lastschrift',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_sepa-dd.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::SEPADD,
            'translation' => Array('description' => 'Qenta SEPA Direct Debit', 'additionalDescription' => '')
        ),
        'INVOICE' => array(
            'name' => 'invoice',
            'description' => 'Qenta Kauf auf Rechnung',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_invoice.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::INVOICE,
            'translation' => Array('description' => 'Qenta Invoice', 'additionalDescription' => '')
        ),
        'INSTALLMENT' => array(
            'name' => 'installment',
            'description' => 'Qenta Kauf auf Raten',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_installment.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::INSTALLMENT,
            'translation' => Array('description' => 'Qenta Installment', 'additionalDescription' => '')
        ),
        'SKRILLWALLET' => array(
            'name' => 'skrillwallet',
            'description' => 'Qenta Skrill Digital Wallet',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_skrillwallet.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::SKRILLWALLET,
            'translation' => Array('description' => 'Qenta Skrill Digital Wallet', 'additionalDescription' => '')
        ),
        'EKONTO' => array(
            'name' => 'ekonto',
            'description' => 'Qenta eKonto',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_ekonto.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::EKONTO,
            'translation' => Array('description' => 'Qenta eKonto', 'additionalDescription' => '')
        ),
        'TRUSTLY' => array(
            'name' => 'trustly',
            'description' => 'Qenta Trustly',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_trustly.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::TRUSTLY,
            'translation' => Array('description' => 'Qenta Trustly', 'additionalDescription' => '')
        ),
        'TATRAPAY' => array(
            'name' => 'tatrapay',
            'description' => 'Qenta TatraPay',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_tatrapay.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::TATRAPAY,
            'translation' => Array('description' => 'Qenta TatraPay', 'additionalDescription' => '')
        ),
        'EPAY' => array(
            'name' => 'epay',
            'description' => 'Qenta ePay.bg',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_epay.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::EPAYBG,
            'translation' => Array('description' => 'Qenta ePay.bg', 'additionalDescription' => '')
        ),
        'VOUCHER' => array(
            'name' => 'voucher',
            'description' => 'Qenta Gutschein',
            'additionalDescription' => '<img src="{link file=\'frontend/_public/images/qcp_voucher.png\'}" class="qenta-brand"/>&nbsp;',
            'call' => QentaCEE_QPay_PaymentType::VOUCHER,
            'translation' => Array('description' => 'Qenta Voucher', 'additionalDescription' => '')
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