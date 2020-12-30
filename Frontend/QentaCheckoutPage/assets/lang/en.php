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

return array(
    'languageId' => 2,
    'locale' => 'en_US',
    'snippets' => array(
        'frontend/checkout/return' => array(
            'QentaCheckoutPagePaymentRedirectHeader' => 'Redirect',
            'QentaCheckoutPagePaymentRedirectText' => 'You will be redirected in a moment.',
            'QentaCheckoutPagePaymentRedirectLinkText' => 'If the redirect does not work please click',
            'QentaCheckoutPagePaymentRedirectLink' => 'here',
        ),
        'frontend/checkout/finish' => array(
            'QentaCheckoutPageMessageActionPending' => 'The financial institution has not yet approved your payment.',
        ),
        'frontend/checkout/confirm' => array(
            'QentaMessageActionCancel' => 'The payment process has been canceled.',
            'QentaMessageActionFailure' => 'An error occurred during the payment process. Please try again or choose a different payment method.',
            'QentaCheckoutPagePayolutionTermsHeader' => 'Payolution Terms',
            'QentaCheckoutPagePayolutionConsent1' => 'I agree that the data which are necessary for the liquidation of purchase on account and which are used to complete the identy and credit check are transmitted to payolution. My ',
            'QentaCheckoutPagePayolutionConsent2' => ' can be revoked at any time with effect for the future.',
            'QentaCheckoutPagePayolutionLink' => 'consent',
            'QentaCheckoutPageBirthday' => 'Date of birth',
            'QentaCheckoutPageBirthdayInformation' => 'You must be at least 18 years of age to use this payment method.',
            'QentaCheckoutPagePayolutionTermsAccept' => 'Please accept the payolution terms.',
        ),
    ),
);
