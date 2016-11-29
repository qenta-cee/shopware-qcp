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
 * Loader class responsible for autoloading WirecardCEE library classes
 *
 * Autoloader for libraries
 */
class Shopware_Plugins_Frontend_WirecardCheckoutPage_Components_Loader implements Zend_Loader_Autoloader_Interface
{

    /**
     * Prefix for classes which
     * should be included by this autoloader
     * @var string
     */
    const PREFIX = 'WirecardCEE_';

    public function __construct()
    {
        $this->addComponentsPath();
    }

    /**
     * Add library path to PHP include path
     */
    protected function addComponentsPath()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
    }

    /**
     * method used by shopware for autoloading
     *
     * @param string $class
     * @return bool|mixed
     */
    public function autoload($class)
    {
        if (!preg_match('/^' . self::PREFIX . '/', $class)) {
            return FALSE;
        }
        $fragment = str_replace('_', '/', trim($class, '_'));
        return include_once(dirname(__FILE__) . '/' . $fragment . '.php');
    }
}