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
        return '1.2.7';
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
        return array(
            'version' => $this->getVersion(),
            'autor' => 'Wirecard Central Eastern Europe GmbH',
            'copyright' => 'Wirecard Central Eastern Europe GmbH',
            'label' => $this->getLabel(),
            'support' => 'http://www.wirecard.at/en/get-in-contact/',
            'link' => 'http://www.wirecard.at',
            'description' => '<div style="line-height: 1.6em"><h1>Wirecard Checkout Page</h1><p>'
                . file_get_contents(dirname(__FILE__) . '/info.txt') . '</p>'
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
        if ($this->assertMinimumVersion('5')) {
            /** @var \Shopware\Components\CacheManager $cacheManager */
            $cacheManager = $this->get('shopware.cache_manager');
            $cacheManager->clearThemeCache();
        }

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
            'RESTORE_BASKET',
            array(
                'label' => 'Warenkorb wiederherstellen',
                'value' => 0,
                'description' => 'Falls "Ja" ausgewählt ist, wird der ursprüngliche Warenkorbinhalt wiederhergestellt, wenn der Konsument diesen während des Bezahlprozesses ändert. Falls "Nein" ausgewählt ist, wird die Bestellung nicht durchgeführt und auf den Status "Klärung notwedig" gesetzt.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
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
            'WIRECARD_LOG',
            array(
                'label' => 'Protokollierung',
                'value' => 1,
                'store' => array(
                    array(1, 'disable'),
                    array(2, 'file'),
                    array(5, 'FirePHP')
                ),
                'description' => 'Auswahl, auf welche Art und Weise das Protokoll zur Fehlersuche angelegt werden soll.',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'order' => ++$i
            )
        );

        $form->setElement(
            'numberfield',
            'DELETELOG',
            array(
                'label' => 'Protokoll löschen nach x Tagen',
                'value' => '14',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'required' => false,
                'description' => 'Logfiles werden nach der angegebenen Anzahl von Tagen gelöscht. Wird nur bei einer Protokollierung als Datei benötigt.',
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
                'RESTORE_BASKET' => Array(
                    'label' => 'Restore basket',
                    'description' => 'If set to "Yes", the original content of the shopping basket is restored if your consumer changed the basket during the payment process. If set to "No", the order is not executed and set to a clarification state.'
                ),
                'CONFIRM_MAIL' => Array(
                    'label' => 'Notification e-mail',
                    'description' => 'Receiving notification by e-mail regarding the orders of your consumers if an error occurred in the communication between Wirecard and your online shop.'
                ),
                'AUTO_DEPOSIT' => Array(
                    'label' => 'Automated deposit',
                    'description' => 'Enabling an automated deposit of payments. Please contact our sales teams to activate this feature.'
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
                'USE_IFRAME' => Array(
                    'label' => 'Use iframe',
                    'description' => 'Display Wirecard Checkout Page in an iframe.',
                ),
                'WIRECARD_SAVERESPONSE' => Array(
                    'label' => 'Save payment process results',
                    'description' => 'Save all results regarding the payment process, i.e. each Wirecard Checkout Server response to the confirmation URL to the defined field.'
                ),
                'WIRECARD_LOG' => Array(
                    'label' => 'Logging',
                    'description' => 'Selecting the method for logging of plugin related events to facilitate debugging.'
                ),
                'DELETELOG' => Array(
                    'label' => 'Keep logs for N days',
                    'description' => 'Logfiles will be deleted after the given number of days. Only necessary for logging as files.'
                ),
                'DISPLAY_TEXT' => Array(
                    'label' => 'Text on payment page',
                    'description' => 'Text displayed during the payment process, i.e. "Thank you for ordering in xy-shop".'
                )
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

        // Subscribe the needed event for less merge and compression
        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Less',
            'addLessFiles'
        );

        // Cronjob: delete old log files
        $this->subscribeEvent('WirecardCEEDeleteWCPLog', 'onRun');
        if(!$this->hasCronJob('Wirecard CEE WCP Logeintr&auml;ge l&ouml;schen')) {
            $this->createCronJob('Wirecard CEE WCP Logeintr&auml;ge l&ouml;schen', 'WirecardCEEDeleteWCPLog');
        }
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
     * Delete old log entries
     *
     * @param Shopware_Components_Cron_CronJob $job
     */
    public function onRun(Shopware_Components_Cron_CronJob $job)
    {
        self::init();
        $dir = Shopware()->WirecardCheckoutPage()->getConfig()->getLogDirectory();
        $deltime = strtotime(sprintf('-%d days', Shopware()->WirecardCheckoutPage()->getConfig()->DELETELOG));

        // Delete old log files
        if (is_dir($dir) && is_writable($dir)) {
            foreach (scandir($dir) as $item) {
                $file = $dir . '/' . $item;
                $stat = stat($file);
                if ($stat['mtime'] < $deltime) {
                    unlink($file);
                }
            }
        }
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
                $oPayment = $this->createPayment(
                    array(
                        'name' => $prefixName . $pm['name'],
                        'description' => $pm['description'],
                        'action' => self::CONTROLLER,
                        'active' => (isset($pm['active'])) ? (int)$pm['active'] : 0,
                        'position' => $i,
                        'pluginID' => $this->getId(),
                        'additionalDescription' => ''
                    )
                );
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

        // Autoloader for library
        Zend_Loader_Autoloader::getInstance()->pushAutoloader(Shopware()->WirecardCheckoutPage()->getLoader());
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
     * Riskmanagement: Don't show payment type invoice if
     * shipping and billing address are different or the customer
     * not to be of legal age
     * The payment method is displayed if return value is TRUE
     *
     * @param Enlight_Hook_HookArgs $args
     *
     * @return bool
     */
    public function wRiskWirecardCheckoutPage(Enlight_Hook_HookArgs $args)
    {
        if ($args->getReturn() == true) {
            return true;
        }

        self::init();
        $parameter = $args->getArgs();
        $payment = Shopware()->WirecardCheckoutPage()->getPaymentMethods()->getPaymentMethodName($parameter[0]);
        if (0 == strcmp($payment, 'wcp_invoice') || 0 == strcmp($payment, 'wcp_installment')) {
            // Looking for user data
            $user = Shopware()->Session()->sOrderVariables['sUserData'];
            if (is_null($user)
                || !isset($user['billingaddress']['birthday']) // No birthday given
            ) {
                return true;
            }

            // is birthday a valid date
            $date = explode("-", $user['billingaddress']['birthday']);
            if (false === checkdate($date[1], $date[2], $date[0])) {
                return true;
            }
            // Is customer to be of legal age
            if ((time() - strtotime($user['billingaddress']['birthday'] . ' +18 years')) < 0) {
                return true;
            }
        }

        return false;
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
            case 'confirm':
                self::init();
                $view->addTemplateDir($this->Path() . 'Views/common/');

                if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
                    $view->addTemplateDir($this->Path() . 'Views/responsive/');
                } else {
                    $view->addTemplateDir($this->Path() . 'Views/');
                }

                // Output of common errors
                if (null != Shopware()->WirecardCheckoutPage()->wirecard_action) {
                    self::showErrorMessages($view);
                }
                break;

            case 'finish':
                self::init();

                $view->addTemplateDir($this->Path() . 'Views/common/');
                if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
                    $view->addTemplateDir($this->Path() . 'Views/responsive/');
                } else {
                    $view->addTemplateDir($this->Path() . 'Views/');
                $view->extendsTemplate('frontend/checkout/wirecard_finish.tpl');
                }

                $view->pendingPayment = $args->getSubject()->Request()->get('pending');
                break;

            default:
                return;
        }
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
        if($this->assertMinimumVersion('5')) {
            foreach($cronManager->getAllJobs() AS $job) {
                if($job->getName() == $cronName) {
                    return true;
                }
            }
            return false;
        } else {
            return $cronManager->getJobByName($cronName) ? true : false;
        }
    }

}