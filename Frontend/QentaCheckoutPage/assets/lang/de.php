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
    'languageId' => 1,
    'locale' => 'de_DE',
    'snippets' => array(
        'frontend/checkout/return' => array(
            'QentaCheckoutPagePaymentRedirectHeader' => 'Weiterleitung',
            'QentaCheckoutPagePaymentRedirectText' => 'Sie werden nun weitergeleitet.',
            'QentaCheckoutPagePaymentRedirectLinkText' => 'Falls Sie nicht weitergeleitet werden, klicken Sie bitte',
            'QentaCheckoutPagePaymentRedirectLink' => 'hier',
        ),
        'frontend/checkout/finish' => array(
            'QentaCheckoutPageMessageActionPending' => 'Ihre Zahlung wurde vom Finanzinstitut noch nicht best&auml;tigt.',
        ),
        'frontend/checkout/confirm' => array(
            'QentaMessageActionCancel' => 'Der Zahlungsvorgang wurde von Ihnen abgebrochen.',
            'QentaMessageActionFailure' => 'W&auml;hrend des Zahlungsvorgangs ist ein Fehler aufgetreten. Bitte versuchen Sie es noch einmal oder w&auml;hlen eine andere Zahlungsart aus.',
            'QentaCheckoutPagePayolutionTermsHeader' => 'Payolution Konditionen',
            'QentaCheckoutPagePayolutionConsent1' => 'Mit der Übermittlung jener Daten an payolution, die für die Abwicklung von Zahlungen mit Kauf auf Rechnung und die Identitäts- und Bonitätsprüfung erforderlich sind, bin ich einverstanden. Meine ',
            'QentaCheckoutPagePayolutionConsent2' => ' kann ich jederzeit mit Wirkung für die Zukunft widerrufen.',
            'QentaCheckoutPagePayolutionLink' => 'Einwilligung',
            'QentaCheckoutPageBirthday' => 'Geburtsdatum',
            'QentaCheckoutPageBirthdayInformation' => 'Sie müssen mindestens 18 Jahre alt sein, um dieses Zahlungsmittel nutzen zu können.',
            'QentaCheckoutPagePayolutionTermsAccept' => 'Bitte akzeptieren Sie die payolution Konditionen.',
        ),
    )
);
