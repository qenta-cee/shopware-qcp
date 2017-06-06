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
 * class representing the WirecardCheckoutPage configuration.
 */
class Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Config
{
	/**
	 * List of payment methods with required financial institution
	 *
	 * @var array
	 */
	private static $paymentsFinancialInstitution = array(
		'eps',
		'idl'
	);

    /**
     * Returns shop name
     *
     * @return string
     */
    public function getShopName()
    {
        return $this->SHOP_PREFIX;
    }


    /**
     * Returns umage url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->IMAGE_URL;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return Shopware()->Locale()->getLanguage();
    }

    public function getPrefix($type)
    {
        switch ($type) {
            case 'description':
                return 'WCP ';
            case 'name':
                return 'wcp_';
        }
    }

    public function saveResponseTo()
    {
        switch ($this->WIRECARD_SAVERESPONSE) {
            case 2:
                return 'internalcomment'; break;
            case 3:
                return 'attribute1'; break;
            case 4:
                return 'attribute2'; break;
            case 5:
                return 'attribute3'; break;
            case 6:
                return 'attribute4'; break;
            case 7:
                return 'attribute5'; break;
            case 8:
                return 'attribute6'; break;
            default:
                return false; break;
        }
    }

    public function setAsTransactionID()
    {
        switch($this->USE_AS_TRANSACTION_ID) {
            case 2:
                return 'gatewayReferenceNumber';
                break;
            case 1:
            default:
                return 'orderNumber';
                break;
        }
    }

    public function getDbTables()
    {
        $aReturn = array();
        //array('1' => array('locale' => 'de_DE', 'snippets' => array()));
        foreach($this->getLanguageDefinition() AS $iLanguage => $aLanguage)
        {
            if(array_key_exists('snippets', $aLanguage))
            {
                foreach($aLanguage['snippets'] AS $sNamespace => $aTranslations)
                {
                    foreach($aTranslations AS $sKey => $sValue)
                    {
                        $sql = sprintf('SELECT id FROM s_core_snippets WHERE namespace="%s" AND shopID = 1 AND localeID = "%s" AND name = "%s";', $sNamespace, $iLanguage, $sKey);
                        $aResult = Shopware()->Db()->fetchAll($sql);
                        if(count($aResult))
                        {
                            $id = $aReturn[0]['id'];
                            $sql = sprintf('Update s_core_snippets SET value="%s" WHERE id="%s";', $sValue, $id);
                        }
                        else
                        {
                            $sql = sprintf('INSERT INTO s_core_snippets SET namespace = "%s",
                                                                            shopID = "%s",
                                                                            localeID = "%s",
                                                                            name = "%s",
                                                                            value = "%s"', $sNamespace, 1, $iLanguage, $sKey, $sValue);
                        }
                        $aReturn[] = $sql;
                    }
                }
            }
        }

        return $aReturn;
    }

    /**
     * Returns value of given plugin configure parameter
     *
     * @param string $var
     *
     * @return string
     * @throws Enlight_Exception
     */
    public function __get($var = null)
    {
        static $config = null;
        if (is_null($config)) {
            $config = Shopware()->Plugins()
                ->Frontend()
                ->WirecardCheckoutPage()
                ->pluginConfig();
        }
        $var = strtoupper($var);
        if (isset($config->$var)) {
            return $config->$var;
        } else if($var == 'SHOPID' || $var == 'DISPLAY_TEXT' || $var == 'SHOP_PREFIX' || $var == 'IMAGE_URL') {
            //optional field shopId would cause exception if not configured
            return '';
        } else {
            throw new Enlight_Exception('No config variable ' . $var . ' found');
        }
    }

    /**
     * @return array
     */
    protected function getLanguageDefinition()
    {
        $aLanguages = Array();
        $oDirectoryIterator = new DirectoryIterator($this->getLanguageDirectory());
        foreach($oDirectoryIterator AS $oFileInfo)
        {
            if($oFileInfo->isFile())
            {
                $aLanguageDefinitation = include($oFileInfo->getPathname());
                if(array_key_exists('languageId', $aLanguageDefinitation))
                {
                    $iLanguage = $aLanguageDefinitation['languageId'];
                    unset($aLanguageDefinitation['languageId']);
                    $aLanguages[$iLanguage] = $aLanguageDefinitation;
                }
            }
        }
        return $aLanguages;
    }

    /**
     * @return string
     */
    protected function getLanguageDirectory()
    {
        return realpath($this->getAssetDirectory() . '/lang');
    }

    /**
     * @return string
     */
    protected function getAssetDirectory()
    {
        return realpath(__DIR__ . '/../assets');
    }

	/**
	 * Returns payment methods with required financial institutions
	 *
	 * @return array
	 */
	public function getPaymentsFinancialInstitution()
	{
		return self::$paymentsFinancialInstitution;
	}
}
