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

require_once __DIR__ . '/Components/CSRFWhitelistAware.php';

/**
 * WirecardCheckoutPage Bootstrap class
 *
 * This class is hooking into the bootstrap mechanism of Shopware.
 */
class Shopware_Plugins_Frontend_WirecardCheckoutPage_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var string
     */
    const CONTROLLER = "WirecardCheckoutPage";

    /**
     * Starting position for Wirecard CEE payment methods
     */
    const STARTPOSITION = 50;

    /**
     * Plugin name
     */
    const NAME = 'Shopware_5.WirecardCheckoutPage';

    public function getCapabilities()
    {
        return array(
            'install' => true,
            'enable' => true,
            'update' => true,
            'secureUninstall' => true
        );
    }

    /**
     * Returns the version of plugin as string.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.5.4';
    }

    /**
     * Returns the label of the plugin as string
     *
     * @return string
     */
    public function getLabel()
    {
        return "Wirecard Checkout Page";
    }

    /**
     * Informations about this plugin
     *
     * @return array
     */
    public function getInfo()
    {
        $image = dirname(__FILE__) . '/wirecard-logo.png';
        $imageData = base64_encode(file_get_contents($image));

        $src = 'data: '.mime_content_type($image).';base64,'.$imageData;

        return array(
            'version' => $this->getVersion(),
            'autor' => 'Wirecard',
            'copyright' => 'Wirecard',
            'label' => $this->getLabel(),
            'support' => 'http://www.wirecard.at/en/get-in-contact/',
            'link' => 'http://www.wirecard.at',
            'description' => '<img src="'.$src.'" /><div style="line-height: 1.6em"><h3>WIRECARD - YOUR FULL SERVICE PAYMENT PROVIDER - COMPREHENSIVE SOLUTIONS FROM ONE SINGLE SOURCE</h3>'
                . '<p>' . file_get_contents(dirname(__FILE__) . '/info.txt') . '</p></div>'
        );
    }

    public function install() {
        self::init();

        $this->createEvents();
        $this->createPayments();
        $this->createForm();
        $this->createTranslations();

        foreach (Shopware()->WirecardCheckoutPage()->getConfig()->getDbTables() as $sql) {
            Shopware()->Db()->exec($sql);
        }

        return array(
            'success' => true,
            'invalidateCache' => array('frontend', 'config', 'template', 'theme')
        );
    }

    /**
     * This derived method is called automatically each time the plugin will be reinstalled
     * (does not delete databases)
     *
     * @return array
     */
    public function secureUninstall()
    {
        /** @var \Shopware\Components\CacheManager $cacheManager */
        $cacheManager = $this->get('shopware.cache_manager');
        $cacheManager->clearThemeCache();

        return array(
            'success' => true,
            'invalidateCache' => array('frontend', 'config', 'template', 'theme')
        );
    }

    public function uninstall() {
        //TODO: uninstall Routine.. remove translations, remove snippets
        try {
            Shopware()->Db()->delete('s_core_paymentmeans', 'pluginID = ' . (int) $this->getId());
            Shopware()->Db()->delete('s_crontab', 'pluginID = ' . (int) $this->getId());

        } catch (Exception $e) {

        }

        return $this->secureUninstall();

    }

    public function update($version) {
        if (version_compare($version, '1.0.0', '<=')) {
            //removing paymentType click2pay
            Shopware()->Db()->delete('s_core_paymentmeans', 'name = "wcp_c2p"');
        }

        if (version_compare($version, '1.4.0', '<=')) {
            //removing old logging method
            $em = $this->get('models');
            $form = $this->Form();
            $wirecard_log = $form->getElement('WIRECARD_LOG');
            if ($wirecard_log !== null) {
                $em->remove($wirecard_log);
            }
            $wirecard_delete_log = $form->getElement('DELETELOG');
            if ($wirecard_delete_log !== null) {
                $em->remove($wirecard_delete_log);

            }
            $em->flush();
        }

        if (version_compare($version, '1.4.4', '<=')) {
            //remove deprecated paymenttypes
            Shopware()->Db()->delete('s_core_paymentmeans', 'name = "wcp_quick"');
            Shopware()->Db()->delete('s_core_paymentmeans', 'name = "wcp_elv"');
            Shopware()->Db()->delete('s_core_paymentmeans', 'name = "wcp_mpass"');
            Shopware()->Db()->delete('s_core_paymentmeans', 'name = "wcp_skrilldirect"');
            //removing unused restore basket
            $em = $this->get('models');
            $form = $this->Form();
            $restore_basket = $form->getElement('RESTORE_BASKET');
            if ($restore_basket !== null) {
                $em->remove($restore_basket);
            }
            $em->flush();
        }

        return $this->install();
    }

    /**
     * Plugin configuration form
     * @protected
     */
    protected function createForm()
    {
        $form = $this->Form();
        $i = 0;

        $form->setElement(
            'text',
            'CUSTOMERID',
            array(
                'label' => 'Kundennummer',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Ihre Wirecard-Kundennummer (customerId, im Format D2#####).',
                'required' => true,
                'order' => ++$i
            )
        );

        $form->setElement(
            'text',
            'SHOPID',
            array(
                'label' => 'Shop ID',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'description' => 'Shop-Kennung (shopId) bei mehreren Onlineshops.',
                'order' => ++$i
            )
        );

        $form->setElement(
            'text',
            'SECRET',
            array(
                'label' => 'Secret',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Geheime Zeichenfolge, die Sie von Wirecard erhalten haben, zum Signieren und Validieren von Daten zur Prüfung der Authentizität (Testmodus: B8AKTPWBRMNBV455FG6M2DANE99WU2).',
                'required' => true,
                'order' => ++$i
            )
        );

        $form->setElement(
            'text',
            'SERVICE_URL',
            array(
                'label' => 'URL zur Impressum-Seite',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'URL auf der Bezahlseite, die zur Impressum-Seite des Onlineshops führt.',
                'required' => true,
                'order' => ++$i
            )
        );

        $form->setElement(
            'text',
            'IMAGE_URL',
            array(
                'label' => 'URL des Bildes auf der Bezahlseite',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'URL zu einem Bild/Logo, das während des Bezahlprozesses in Wirecard Checkout Page angezeigt wird (vorzugsweise 95x65 px).',
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'checkbox',
            'CONFIRM_MAIL',
            array(
                'label' => 'Benachrichtigungsmail',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Benachrichtigung per E-Mail über Zahlungen Ihrer Kunden, falls ein Kommunikationsproblem zwischen Wirecard und Ihrem Onlineshop aufgetreten ist.',
                'required' => false,
                'order' => ++$i
            )
        );


        $form->setElement(
            'checkbox',
            'AUTO_DEPOSIT',
            array(
                'label' => 'Automatisches Abbuchen',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Automatisches Abbuchen der Zahlungen. Bitte kontaktieren Sie unsere Sales-Teams um dieses Feature freizuschalten.',
                'required' => false,
                'order' => ++$i
            )
        );


        $form->setElement(
            'numberfield',
            'MAX_RETRIES',
            array(
                'label' => 'Max. Versuche',
                'value' => -1,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Max. möglichen Bezahlversuche eines bestimmten Auftrags.',
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'text',
            'SHOP_PREFIX',
            array(
                'label' => 'Shop-Präfix im Buchungstext (Rechnung)',
                'value' => '',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Referenz zu Ihrem Onlineshop im Buchungstext für Ihren Kunden, max. 9 Zeichen.',
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'checkbox',
            'SEND_ADDITIONAL_DATA',
            array(
                'label' => 'Verrechnungsdaten des Konsumenten mitsenden',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Weiterleitung der Rechnungs- und Versanddaten des Kunden an den Finanzdienstleister.',
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'checkbox',
            'SEND_BASKET_DATA',
            array(
                'label' => 'Warenkorbdaten des Konsumenten mitsenden',
                'value' => 0,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Weiterleitung des Warenkorbs des Kunden an den Finanzdienstleister.',
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'checkbox',
            'USE_IFRAME',
            array(
                'label' => 'iFrame verwenden',
                'value' => 1,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Wirecard Checkout Page in iFrame anzeigen.',
                'required' => false,
                'order' => ++$i,
            )
        );

        $form->setElement(
            'select',
            'WIRECARD_SAVERESPONSE',
            array(
                'label' => 'Speichern der Bezahlprozess-Ergebnisse',
                'value' => 1,
                'store' => array(
                    array(1, 'Do not save'),
                    array(2, 'Internal commentfield'),
                    array(3, 'free text 1'),
                    array(4, 'free text 2'),
                    array(5, 'free text 3'),
                    array(6, 'free text 4'),
                    array(7, 'free text 5'),
                    array(8, 'free text 6'),
                ),
                'description' => 'Speichern aller Ergebnisse des Bezahlprozesses, d.h. jedes Aufrufs des Wirecard Checkout Servers der Bestätigungs-URL.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'select',
            'USE_AS_TRANSACTION_ID',
            array(
                'label' => 'Shopware transaction ID',
                'value' => 1,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'store' => array(
                    array(1, 'Wirecard order number'),
                    array(2, 'Gateway reference number')
                ),
                'description' => 'Als Shopware transaction ID wird entweder die shopinterne Bestellnummer oder die Referenznummer des Acquirers verwendet.',
                'required' => false,
                'order' => ++$i
            )
        );
        $form->setElement(
            'text',
            'DISPLAY_TEXT',
            array(
                'label' => 'Text auf der Bezahlseite',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Text, der während des Bezahlprozesses angezeigt wird, z.B. "Danke für Ihre Bestellung im xy-Shop".',
                'required' => false,
                'order' => ++$i
            )
        );
        $form->setElement(
            'checkbox',
            'SEND_PENDING_MAILS',
            array(
                'label' => 'Mail für Pendingstatus versenden',
                'value' => 0,
                'description' => 'Falls "Ja" gesetzt ist, werden Mails zu noch nicht bestätigten Zahlungen verschickt.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );
        $form->setElement(
            'checkbox',
            'ENABLE_DUPLICATE_REQUEST_CHECK',
            array(
                'label' => 'Überprüfung auf doppelte Anfragen',
                'value' => 0,
                'description' => 'Überprüfung auf mehrfache Anfragen seitens Ihres Kunden.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'checkbox',
            'PAYOLUTION_TERMS',
            array(
                'label' => 'Payolution Konditionen',
                'value' => 1,
                'description' => 'Anzeige der Checkbox mit den payolution-Bedingungen, die vom Kunden während des Bezahlprozesses bestätigt werden müssen, wenn Ihr Onlineshop als "Trusted Shop" zertifiziert ist.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'text',
            'PAYOLUTION_MID',
            array(
                'label' => 'Payolution mID',
                'value' => '',
                'description' => 'payolution-Händler-ID, bestehend aus dem Base64-enkodierten Firmennamen, die für den Link "Einwilligen" gesetzt werden kann.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'select',
            'INVOICE_PROVIDER',
            array(
                'label' => 'Provider für Kauf auf Rechnung',
                'value' => 'payolution',
                'store' => array(
                    array('payolution', 'payolution'),
                    array('ratepay', 'RatePay'),
                    array('wirecard', 'Wirecard')
                ),
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'select',
            'INVOICE_CURRENCY',
            array(
                'label' => 'Akzeptierte Währungen für Kauf auf Rechnung',
                'value' => '',
                'store' => 'base.Currency',
                'valueField' => 'currency',
                'multiSelect' => true,
                'description' => 'Bitte wählen Sie mindestens eine gültige Währung für Kauf auf Rechnung.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'select',
            'INSTALLMENT_PROVIDER',
            array(
                'label' => 'Provider für Kauf auf Raten',
                'value' => 'payolution',
                'store' => array(
                    array('payolution', 'payolution'),
                    array('ratepay', 'RatePay')
                ),
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'select',
            'INSTALLMENT_CURRENCY',
            array(
                'label' => 'Akzeptierte Währungen für Kauf auf Raten',
                'value' => '',
                'store' => 'base.Currency',
                'valueField' => 'currency',
                'multiSelect' => true,
                'description' => 'Bitte wählen Sie mindestens eine gültige Währung für Kauf auf Raten.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );
    }

    /**
     * addes the translations for admin interface to the database
     *
     * used in install but also could be used from outside later on.
     *
     * @return void
     */
    public function createTranslations()
    {
        $form = $this->Form();
        $translations = Array(
            'en_GB' => Array(
                'CUSTOMERID' => Array(
                    'label' => 'Customer ID',
                    'description' => 'Customer number you received from Wirecard (customerId, i.e. D2#####).'
                ),
                'SHOPID' => Array(
                    'label' => 'Shop ID',
                    'description' => 'Shop identifier (shopId) in case of more than one shop.'
                ),
                'SECRET' => Array(
                    'label' => 'Secret',
                    'description' => 'String which you received from Wirecard for signing and validating data to prove their authenticity. (Test mode: B8AKTPWBRMNBV455FG6M2DANE99WU2)'
                ),
                'SERVICE_URL' => Array(
                    'label' => 'URL to imprint page',
                    'description' => 'URL on the payment page which leads to the imprint page of the online shop.'
                ),
                'IMAGE_URL' => Array(
                    'label' => 'URL to image on payment page',
                    'description' => 'URL to an image/logo which is displayed during the payment process in Wirecard Checkout Page (95x65 px preferred)'
                ),
                'CONFIRM_MAIL' => Array(
                    'label' => 'Notification e-mail',
                    'description' => 'Receiving notification by e-mail regarding the orders of your consumers if an error occurred in the communication between Wirecard and your online shop.'
                ),
                'AUTO_DEPOSIT' => Array(
                    'label' => 'Automated deposit',
                    'description' => 'Enabling an automated deposit of payments. Please contact our sales teams to activate this feature.'
                ),
                'PAYOLUTION_TERMS' => Array(
                    'label' => 'Payolution terms',
                    'description' => 'If your online shop is certified by "Trusted Shops", display the corresponding checkbox with payolution terms for the consumer to agree with during the checkout process.'
                ),
                'PAYOLUTION_MID' => Array(
                    'label' => 'Payolution mID',
                    'description' => 'Your payolution merchant ID consisting of the base64-encoded company name which is used in the link for "consent" to the payolution terms.'
                ),
                'MAX_RETRIES' => Array(
                    'label' => 'Max. retries',
                    'description' => 'Maximum number of payment attempts regarding a certain order.'
                ),
                'SHOP_PREFIX' => Array(
                    'label' => 'Shop prefix in posting text (invoice)',
                    'description' => 'Reference to your online shop on your consumer\'s invoice, limited to 9 characters.'
                ),
                'SEND_ADDITIONAL_DATA' => Array(
                    'label' => 'Forward consumer data',
                    'description' => 'Forwarding shipping and billing data about your consumer to the respective financial service provider.'
                ),
                'SEND_BASKET_DATA' => Array(
                    'label' => 'Forward basket data',
                    'description' => 'Forwarding basket data to the respective financial service provider.'
                ),
                'USE_IFRAME' => Array(
                    'label' => 'Use iframe',
                    'description' => 'Display Wirecard Checkout Page in an iframe.',
                ),
                'WIRECARD_SAVERESPONSE' => Array(
                    'label' => 'Save payment process results',
                    'description' => 'Save all results regarding the payment process, i.e. each Wirecard Checkout Server response to the confirmation URL to the defined field.'
                ),
                'DISPLAY_TEXT' => Array(
                    'label' => 'Text on payment page',
                    'description' => 'Text displayed during the payment process, i.e. "Thank you for ordering in xy-shop".'
                ),
                'SEND_PENDING_MAILS' => Array(
                    'label' => 'Send Pendingstate mails',
                    'description' => 'Selecting "Yes", mails will be sent for pending orders'
                ),
                'ENABLE_DUPLICATE_REQUEST_CHECK' => Array(
                    'label' => 'Check for duplicate requests',
                    'description' => 'Checking duplicate requests made by your consumer.'
                ),
                'INVOICE_PROVIDER' => Array(
                    'label' => 'Invoice Provider'
                ),
                'INVOICE_CURRENCY' => Array(
                    'label' => 'Accepted currencies for Invoice',
                    'description' => 'Please select at least one currency to use Invoice.'
                ),
                'INSTALLMENT_PROVIDER' => Array(
                    'label' => 'Installment Provider'
                ),
                'INSTALLMENT_CURRENCY' => Array(
                    'label' => 'Accepted currencies for Installment',
                    'description' => 'Please select at least one currency to use Installment.'
                ),
            )
        );

        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');
        foreach ($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array('locale' => $locale));
            if ($localeModel === null) {
                continue;
            }
            foreach ($snippets AS $element => $snippet) {
                $elementModel = $form->getElement($element);
                if ($elementModel === null) {
                    continue;
                }
                $translationModel = new \Shopware\Models\Config\ElementTranslation();
                $translationModel->setLocale($localeModel);
                if (array_key_exists('label', $snippet)) {
                    $translationModel->setLabel($snippet['label']);
                }
                if (array_key_exists('description', $snippet)) {
                    $translationModel->setDescription($snippet['description']);
                }
                //no translations set yet. we can add new translations
                if(!$elementModel->hasTranslations())
                {
                    $elementModel->addTranslation($translationModel);
                }
            }
        }
    }


    /**
     * subscribe to several events
     */
    protected function createEvents()
    {
        // Returns pamynt controller path
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_' . self::CONTROLLER,
            'onGetControllerPathFrontend'
        );

        // Check while listing payment methods
        $this->subscribeEvent(
            'sAdmin::sManageRisks::after',
            'wRiskWirecardCheckoutPage',
            0
        );

        // Display additional data on checkout confirm page
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch'
        );

        // Save selected POST parameters (financial institutions)
        $this->subscribeEvent(
                'Enlight_Controller_Action_PreDispatch',
                'onPreDispatch'
        );

        // Subscribe the needed event for less merge and compression
        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Less',
            'addLessFiles'
        );

        // Prevent ordermail after pending
        $this->subscribeEvent(
            'Shopware_Modules_Order_SendMail_Send',
            'defineSending'
        );
    }

    /**
     * Provide the file collection for less
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function addLessFiles(Enlight_Event_EventArgs $args)
    {
        $less = new \Shopware\Components\Theme\LessDefinition(
        //configuration
            array(),

            //less files to compile
            array(
                __DIR__ . '/Views/responsive/frontend/_public/src/less/all.less'
            ),

            //import directory
            __DIR__
        );

        return new Doctrine\Common\Collections\ArrayCollection(array($less));
    }

    /**
     * Create and save payment methods
     */
    protected function createPayments()
    {
        $oConfig = Shopware()->WirecardCheckoutPage()->getConfig();

        $prefixName = $oConfig->getPrefix('name');

        $translation = new Shopware_Components_Translation();
        $aTranslations = array();
        $i = 80;
        foreach (Shopware()->WirecardCheckoutPage()->getPaymentMethods()->getList() as $pm) {
            $oPayment = $this->Payments()->findOneBy(array('name' => $prefixName . $pm['name']));
            if(!$oPayment) {
                $payment = array(
                    'name' => $prefixName . $pm['name'],
                    'description' => $pm['description'],
                    'action' => self::CONTROLLER,
                    'active' => (isset($pm['active'])) ? (int)$pm['active'] : 0,
                    'position' => $i,
                    'pluginID' => $this->getId(),
                    'additionalDescription' => strlen($pm['additionalDescription']) ? $pm['additionalDescription'] : 'Pay with Wirecard'
                );
                if (isset($pm['template']) && !is_null($pm['template'])) {
                    $payment['template'] = $pm['template'];
                }
                $oPayment = $this->createPayment($payment);
            } else {
                if (isset($pm['template']) && !is_null($pm['template'])) {
                    $oPayment->setTemplate($pm['template']);
                }
	            if (isset($pm['additionalDescription']) && strlen($pm['additionalDescription']) && !is_null($pm['additionalDescription'])) {
		            $additional = $oPayment->getAdditionalDescription();
		            if ( $additional === '' ) {
			            if ($oPayment->getTemplate() == 'wirecard_logos.tpl') {
				            $oPayment->setTemplate(null);
			            }
			            $oPayment->setAdditionalDescription($pm['additionalDescription']);
		            }
	            }
            }

            $aTranslations[$oPayment->getId()] = $pm['translation'];
            $i++;
        }
        $translation->write(2, 'config_payment', 1, $aTranslations,0);
    }

    /**
     * Shopware 4 compatibility mode
     *
     * @see Config.php
     */
    public function pluginConfig()
    {
        return $this->Config();
    }

    /**
     * Initial parameters called by bootstrap and controller
     *
     * @return Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Resources
     */
    public static function init()
    {
        // Register resource WirecardCheckoutPage
        // The instance is available with Shopware()->WirecardCheckoutPage()
        if (!Shopware()->Bootstrap()->issetResource('WirecardCheckoutPage')) {
            Shopware()->Bootstrap()->registerResource(
                'WirecardCheckoutPage',
                new Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Resources()
            );
        }
    }

    /**
     * Load namespaces with Shopware Loader
     */
    public function afterInit()
    {
        $this->registerCustomModels();
        $this->get('Loader')->registerNamespace('Shopware\\Plugins\\WirecardCheckoutPage', $this->Path());
        $this->get('Loader')->registerNamespace('WirecardCEE', $this->Path() . 'Components/WirecardCEE/');
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public static function onGetControllerPathFrontend(Enlight_Event_EventArgs $args)
    {
        Shopware_Plugins_Frontend_WirecardCheckoutPage_Bootstrap::init();
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/Controllers/Frontend/' . self::CONTROLLER . '.php';
    }

    /**
     * return encoded mId for PayolutionLink
     *
     * @return string
     */
    public function getPayolutionLink()
    {
        $mid = Shopware()->WirecardCheckoutPage()->getConfig()->PAYOLUTION_MID;
        if (strlen($mid) === 0) {
            return false;
        }

        $mId = urlencode(base64_encode($mid));

        return $mId;
    }

    /**
     * set confirmmail after ordercreation false (only for WirecardCheckoutSeamless)
     * @param Enlight_Event_EventArgs $args
     * @return bool
     */
    public function defineSending(Enlight_Event_EventArgs $args)
    {
        $userData = Shopware()->Session()->sOrderVariables['sUserData'];
        $additional = $userData['additional'];
        $paymentaction = $additional['payment']['action'];

        //only prevent confirmationmail for WirecardCheckoutPage payment action
        if($paymentaction == 'WirecardCheckoutPage') {
            return false;
        }
    }

    /**
     * Save selected POST paramter for payment methods with required
     * financial institutions in session
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPreDispatch(Enlight_Event_EventArgs $args)
    {
        $financialInstitution = $args->getSubject()->Request()->get('financialInstitution');
        if (isset($financialInstitution)) {
            self::init();
            Shopware()->WirecardCheckoutPage()->financialInstitution = $financialInstitution;
        }
    }

    /**
     * Display additional data for seamless payment methods and
     * payment methods with required
     *
     * @param Enlight_Controller_EventArgs|Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        // Display additional data
        if (!$args->getSubject()->Request()->isDispatched()
            || $args->getSubject()->Response()->isException()
            || 0 != strcmp('frontend', $args->getSubject()->Request()->getModuleName())
            || 0 != strcmp('checkout', $args->getSubject()->Request()->getControllerName()))
        {
            return;
        }

        /**@var $controller Shopware_Controllers_Frontend_Listing*/
        $controller = $args->getSubject();

        /** @var Enlight_View_Default $view */
        $view = $controller->View();

        switch($args->getSubject()->Request()->getActionName())
        {
            case 'shippingPayment':
                self::init();
                // do pre-check for invoice and installment
                if ( ! $this->isActivePayment('invoice')) {
                    $view->sPayments = $this->hidePayment($view->sPayments, 'wcp_invoice');
                }
                if ( ! $this->isActivePayment('installment')) {
                    $view->sPayments = $this->hidePayment($view->sPayments, 'wcp_installment');
                }

                $view->addTemplateDir($this->Path() . 'Views/common/');
                $view->addTemplateDir($this->Path() . 'Views/responsive/');

                // Output of common errors
                if (null != Shopware()->WirecardCheckoutPage()->wirecard_action) {
                    self::showErrorMessages($view);
                }
                break;
            case 'confirm':
                self::init();
                $view->addTemplateDir($this->Path() . 'Views/common/');
                $view->addTemplateDir($this->Path() . 'Views/responsive/');

                // Output of common errors
                if (null != Shopware()->WirecardCheckoutPage()->wirecard_action) {
                    self::showErrorMessages($view);
                }

                //redirect to payment choice if not-active payment was chosen (invoice/installment)
                $paymentName = Shopware()->Session()->sOrderVariables['sUserData']['additional']['payment']['name'];
                $view->paymentDesc = Shopware()->Session()->sOrderVariables['sUserData']['additional']['payment']['description'];
                $view->paymentName = $paymentName;
                $view->paymentLogo = 'frontend/_public/images/' . $paymentName . '.png';

                if ( ! $this->isActivePayment($paymentName)) {
                    $controller->forward('shippingPayment');
                }

                $user  = Shopware()->Session()->sOrderVariables['sUserData'];
                $birth = null;

                if ( ! is_null($user) && isset($user['additional']['user']['birthday'])) {
                    $birth = $user['additional']['user']['birthday'];
                } else if ( ! is_null($user) && isset($user['billingaddress']['birthday'])) {
                    $birth = $user['billingaddress']['birthday'];
                }

                // Values for datefields
                $view->years  = range(date('Y'), date('Y') - 100);
                $view->days   = range(1, 31);
                $view->months = range(1, 12);

                $birthday = array('-', '-', '-');
                if ($birth != null) {
                    $birthday = explode('-', $birth);
                }

                $view->bYear  = $birthday[0];
                $view->bMonth = $birthday[1];
                $view->bDay   = $birthday[2];

                if ((Shopware()->WirecardCheckoutPage()->getConfig()->INVOICE_PROVIDER == 'payolution' && $paymentName == 'wcp_invoice') ||
                        (Shopware()->WirecardCheckoutPage()->getConfig()->INSTALLMENT_PROVIDER == 'payolution' && $paymentName == 'wcp_installment')
                ) {
                    $view->payolutionTerms = Shopware()->WirecardCheckoutPage()->getConfig()->PAYOLUTION_TERMS;
                    if (Shopware()->WirecardCheckoutPage()->getConfig()->PAYOLUTION_TERMS) {
                        $view->wcpPayolutionLink1 = '<a id="wcp-payolutionlink" href="https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId='.$this->getPayolutionLink().'" target="_blank">';
                        $view->wcpPayolutionLink2 = '</a>';
                    }
                }

                if ($paymentName == 'wcp_eps') {
                    $view->financialInstitutions         = WirecardCEE_QPay_PaymentType::getFinancialInstitutions('EPS');
                    $view->wcpAdditional                 = 'financialInstitutions';
                    $view->financialInstitutionsSelected = Shopware()->WirecardCheckoutPage()->financialInstitution;
                }

                if ($paymentName == 'wcp_ideal') {
                    $view->financialInstitutions         = WirecardCEE_QPay_PaymentType::getFinancialInstitutions('IDL');
                    $view->wcpAdditional                 = 'financialInstitutions';
                    $view->financialInstitutionsSelected = Shopware()->WirecardCheckoutPage()->financialInstitution;
                }
                break;

            case 'finish':
                self::init();

                $view->addTemplateDir($this->Path() . 'Views/common/');
                $view->addTemplateDir($this->Path() . 'Views/responsive/');

                $view->wcpPendingPayment = $args->getSubject()->Request()->get('ispending');
                break;

            default:
                return;
        }
    }

    /**
     * Pre-check for invoice and installment payments
     *
     * @param $paymentName
     *
     * @return bool
     */
    private function isActivePayment($paymentName)
    {
        switch ($paymentName) {
            case 'wcp_invoice':
                $currencies = Shopware()->WirecardCheckoutPage()->getConfig()->INVOICE_CURRENCY;
                if (isset($currencies)) {
                    $currentCurrency = Shopware()->Shop()->getCurrency()->getCurrency();

                    foreach ($currencies as $currency) {
                        if ((string)$currency == (string)$currentCurrency) {
                            return true;
                        }
                    }
                    if (count($currencies)) {
                        return false;
                    }
                }

                return true;
            case 'wcp_installment':
                $currencies = Shopware()->WirecardCheckoutPage()->getConfig()->INSTALLMENT_CURRENCY;
                if (isset($currencies)) {
                    $currentCurrency = Shopware()->Shop()->getCurrency()->getCurrency();

                    foreach ($currencies as $currency) {
                        if ((string)$currency == (string)$currentCurrency) {
                            return true;
                        }
                    }
                    if (count($currencies)) {
                        return false;
                    }
                }

                return true;
            default:
                return true;
        }
    }

    /**
     * Remove payment from active payments
     *
     * @param $payments
     * @param $paymentName
     *
     * @return mixed
     */
    protected function hidePayment($payments, $paymentName)
    {
        if (is_array($payments)) {
            foreach ($payments as $key => $value) {
                if ($value['name'] == $paymentName) {
                    unset($payments[$key]);

                    return $payments;
                }
            }
        }

        return $payments;
    }

    /**
     * Display error messages for customer
     *
     * @param $view
     */
    protected static function showErrorMessages($view)
    {
        $view->wirecard_error = Shopware()->WirecardCheckoutPage()->wirecard_action;
        $view->wirecard_message = Shopware()->WirecardCheckoutPage()->wirecard_message;
        Shopware()->WirecardCheckoutPage()->wirecard_action = null;
        Shopware()->WirecardCheckoutPage()->wirecard_message = null;
    }

    /**
     * check of a cronjob has already been created.
     * @param $cronName
     * @return bool
     */
    protected function hasCronJob($cronName)
    {
        /** @var $cronManager Enlight_Components_Cron_Manager */
        $cronManager = Shopware()->Cron();
        //we have to do a workaround due to a bug in Shopware 5s Cron DBAL Adapter (http://jira.shopware.de/?ticket=SW-11682)
        foreach($cronManager->getAllJobs() AS $job) {
           if($job->getName() == $cronName) {
              return true;
           }
        }
        return false;
    }

}