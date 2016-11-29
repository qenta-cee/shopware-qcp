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
 * class used for logging requests
 *
 * Logging class
 */
class Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Log
    extends Zend_Log
{
    public function __construct(Zend_Log_Writer_Abstract $writer = null)
    {
        parent::__construct($writer);
        $this->addPriority('TABLE', 8);
        $this->addPriority('EXCEPTION', 9);
        $this->addPriority('DUMP', 10);
        $this->addPriority('TRACE', 11);
        switch(Shopware()->WirecardCheckoutPage()->getConfig()->logType())
        {
            case 2:
                $logdir = realpath(__DIR__ . '/../log');
                if(is_writable($logdir))
                {
                    $writer = new Zend_Log_Writer_Stream(sprintf('%s/wcp_%s.log', $logdir, date('Y-m-d')));
                }
                else
                {
                    $writer = new Zend_Log_Writer_Null();
                }
                break;
            case 3:
                $writer = Zend_Log_Writer_Db::factory(array(
                    'db' => Shopware()->Db(),
                    'table' => 's_core_log',
                    'columnmap' => array(
                        'key'       => 'priorityName',
                        'text'      => 'message',
                        'datum'     => 'date',
                        'value2'    => 'remote_address',
                        'value3'    => 'user_agent',
                    )
                ));
                $writer->addFilter(self::ERR);
                break;
            case 4:
                $mail = clone Shopware()->Mail();
                $mail->addTo(Shopware()->Config()->Mail);
                $writer = new Zend_Log_Writer_Mail($mail);
                $writer->setSubjectPrependText('Fehler  "'.Shopware()->Config()->Shopname.'" aufgetreten!');
                $writer->addFilter(self::WARN);
                break;

            default:
                $writer = new Zend_Log_Writer_Null();
                break;
        }
        $this->addWriter($writer);

    }
}

