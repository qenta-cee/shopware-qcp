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

use Shopware\Components\CSRFWhitelistAware;

/**
 * controller class handling Qenta Checkout Page Requests^
 *
 * QentaCheckoutPage Controller
 */
class Shopware_Controllers_Frontend_QentaCheckoutPage extends Shopware_Controllers_Frontend_Payment implements CSRFWhitelistAware
{
    /**
     * Use Bootstrap init
     * Called by any actions
     */
    public function init()
    {
    }

    /**
     * Index action
     * Different view for qcp and other payment methods
     */
    public function indexAction()
    {
        $basket = Shopware()->Modules()->Basket();
        $basketQuantities = $basket->sCheckBasketQuantities();
        if (!empty($basketQuantities['hideBasket'])) {
            return $this->redirect(array('controller' => 'checkout'));
        }

        /** @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Page $oPageModel */
        $oPageModel = Shopware()->Container()->get('QentaCheckoutPage')->getPage();

        $sPaymentType = Shopware()->Container()->get('QentaCheckoutPage')->getPaymentShortName();
        $fAmount = $this->getAmount();
        $sCurrency = $this->getCurrencyShortName();

        /** @var \Shopware\Components\Routing\Router $router */
        $router = $this->Front()->Router();
        $sReturnUrl = $router->assemble(
            array(
                'action' => 'return',
                'sUseSSL' => true
            )
        );

        $sConfigmrUrl = $router->assemble(
            array(
                'action' => 'confirm',
                'forceSecure' => true
            )
        );

        $aParams = Array(
            'sCoreId' => Shopware()->Session()->get('sessionId'),
            '__shop' => Shopware()->Shop()->getId(),
            'wQentaCheckoutPageId' => Shopware()->Container()->get('QentaCheckoutPage')->getTransactionId(),
            'displayText' => Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->display_text
        );

        $checkoutUrl = $this->Front()->Router()->assemble(
            Array('controller' => 'checkout', 'action' => 'confirm', 'sUseSSL' => true)
        );
        $bUseIframe = (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->use_iframe == 1);

        $sOrderVariables = Shopware()->Session()->sOrderVariables;
        $existingOrder = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->findByNumber($sOrderVariables['sOrderNumber']);
        if ($existingOrder[0] instanceof \Shopware\Models\Order\Order && isset($_SESSION["qcp_redirect_url"])) {
            $sRedirectUrl = $_SESSION["qcp_redirect_url"];
            unset($_SESSION["qcp_redirect_url"]);
        } else {

            /** @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Transaction $oTransaction */
            $oTransaction = Shopware()->Container()->get('QentaCheckoutPage')->getTransaction();

            $oTransaction->create($aParams['wQentaCheckoutPageId'],
                $oTransaction->generateHash($aParams['wQentaCheckoutPageId'], $fAmount, $sCurrency),
                Shopware()->Container()->get('QentaCheckoutPage')->getPaymentShortName(),
                $_SESSION);

            $oResponse = $oPageModel->initiatePayment($sPaymentType, $fAmount, $sCurrency, $sReturnUrl, $sConfigmrUrl, $aParams);
            if ($oResponse === null) {
                $this->redirect($checkoutUrl);
                return;
            }

            if($oResponse->hasFailed())
            {
                Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: '. __METHOD__ . ':' . $oResponse->getError()->getConsumerMessage());
                Shopware()->Container()->get('QentaCheckoutPage')->qenta_message = $oResponse->getError()->getConsumerMessage();
                Shopware()->Container()->get('QentaCheckoutPage')->qenta_action = 'failure';
                //if an error occurs we should not show followup page in iframe.
                $bUseIframe = false;
                $sRedirectUrl = $checkoutUrl;
            }
            else
            {
                $_SESSION["qcp_redirect_url"] = $oResponse->getRedirectUrl();
                $sRedirectUrl = $oResponse->getRedirectUrl();
            }
        }

        if($bUseIframe)
        {
            $this->View()->loadTemplate('responsive/frontend/qenta_checkout_page/index.tpl');
            $this->View()->assign(
                'redirectUrl',
                $sRedirectUrl
            );
        }
        else
        {
            $this->redirect($sRedirectUrl);
        }
    }

    /**
     * server2server request, since 5.5.7 we have no session available anymore
     */
    public function confirmAction()
    {
        /** @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Transaction $oTransaction */
        $oTransaction = Shopware()->Container()->get('QentaCheckoutPage')->getTransaction();

        try {
            Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

            $post = $this->Request()->getPost();
            Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: ' . __METHOD__ . '--' . __LINE__ . ':' . print_r($post,
                    1));

            $paymentUniqueId = $this->Request()->getParam('wQentaCheckoutPageId');
            $sCoreId = $this->Request()->getParam('sCoreId');
            if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->setAsTransactionID() == 'gatewayReferenceNumber') {
                $sTransactionIdField = 'gatewayReferenceNumber';
            } else {
                $sTransactionIdField = 'orderNumber';
            }
            $transactionId = $this->Request()->getParam($sTransactionIdField, $paymentUniqueId);

            $transactionData = $oTransaction->read($paymentUniqueId);
            if (!is_array($transactionData)) {
                Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: ' . __METHOD__ . ':invalid transaction data');
                die(QentaCEE_QPay_ReturnFactory::generateConfirmResponseString('invalid transaction data'));
            }

            $sessionData = unserialize($transactionData['session']);
            if($sessionData === false) {
                Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: '. __METHOD__ . ':Validation error: invalid session data');
                die(QentaCEE_QPay_ReturnFactory::generateConfirmResponseString('Validation error: invalid session data'));
            }

            // restore session
            $_SESSION = $sessionData;
            if (is_array($sessionData) && isset($sessionData['Shopware'])) {
                Shopware()->Session()->offsetSet('sOrderVariables', $sessionData['Shopware']['sOrderVariables']);
                Shopware()->Session()->offsetSet('sQentaConfirmMail', $sessionData['Shopware']['sQentaConfirmMail']);
            }

            // restore remote address
            $_SERVER['REMOTE_ADDR'] = $transactionData['remoteAddr'];

            $return       = QentaCEE_QPay_ReturnFactory::getInstance($post,
                Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->SECRET);
            $paymentState = Shopware()->Container()->get('QentaCheckoutPage')->getPaymentStatusId($return->getPaymentState());

            if ( ! $return->validate()) {
                Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: ' . __METHOD__ . ':Validation error: invalid response');
                die(QentaCEE_QPay_ReturnFactory::generateConfirmResponseString('Validation error: invalid response'));
            }

            $update = array('state' => strtolower($return->getPaymentState()));

            $oOrder = $this->getOrderByUniqueId($paymentUniqueId);
            if ( ! empty($oOrder) && $oOrder->temporaryID == $oOrder->transactionID && $paymentUniqueId != $transactionId) {
                $this->updateTransactionIdByUniqueId($paymentUniqueId, $transactionId);
            }

            // data for confirm mail
            $sOrderVariables = Shopware()->Session()->sOrderVariables;

            $message = null;
            switch ($return->getPaymentState()) {
                case QentaCEE_QPay_ReturnFactory::STATE_SUCCESS:

                    /** @var QentaCEE_QPay_Return_Success $return */
                    $update['orderNumber'] = $return->getOrderNumber();

                    $existingOrder = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->findByNumber($sOrderVariables['sOrderNumber']);
                    if ($existingOrder[0] instanceof \Shopware\Models\Order\Order) {
                        $sOrderNumber = $this->savePaymentStatus(
                            $transactionId,
                            $paymentUniqueId,
                            $paymentState,
                            false
                        );
                        //load confirmmail if pending mail confirmation is deactivated
                        if (isset(Shopware()->Session()->sQentaConfirmMail)) {
                            Shopware()->Session()->sQentaConfirmMail->send();
                            unset(Shopware()->Session()->sQentaConfirmMail);
                        }
                    } else {
                        $sOrderNumber = $this->saveOrder(
                            $transactionId,
                            $paymentUniqueId,
                            $paymentState,
                            false
                        );

                        if (!$sOrderNumber) {
                            throw new Enlight_Exception(sprintf('Unabled to save order (%s) with transactionId %s. Shopware orderState: %s',
                                $sOrderNumber, $paymentUniqueId, $paymentState));
                        }

                        Shopware()->Container()->get('dbal_connection')->delete(
                            's_order',
                            ['temporaryID' => $sCoreId, 'ordernumber' => '0']
                        );

                        $update['orderId'] = $sOrderNumber;
                    }

                    if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->saveResponseTo()) {
                        $this->saveComments($return, Shopware()->Session()->sOrderVariables['sOrderNumber']);
                    }
                    break;
                case QentaCEE_QPay_ReturnFactory::STATE_PENDING:
                    /** @var QentaCEE_QPay_Return_Pending $return */

                    //Set qentaState for pending mail check
                    Shopware()->Session()->sOrderVariables['qentaState'] = 'pending';
                    $sendMail = false;
                    if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->SEND_PENDING_MAILS) {
                        $sendMail = true;
                    }
                    $sOrderNumber = $this->saveOrder(
                        $transactionId,
                        $paymentUniqueId,
                        $paymentState,
                        $sendMail
                    );

                    if (!$sOrderNumber) {
                        throw new Enlight_Exception(sprintf('Unabled to save order (%s) with transactionId %s. Shopware orderState: %s',
                            $sOrderNumber, $paymentUniqueId, $paymentState));
                    }

                    Shopware()->Container()->get('dbal_connection')->delete(
                        's_order',
                        ['temporaryID' => $sCoreId, 'ordernumber' => '0']
                    );

                    $update['orderId'] = $sOrderNumber;

                    if (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->saveResponseTo()) {
                        $this->saveComments($return, Shopware()->Session()->sOrderVariables['sOrderNumber']);
                    }
                    break;
                case QentaCEE_QPay_ReturnFactory::STATE_FAILURE:
                    $sOrderVariables = Shopware()->Session()->sOrderVariables;
                    $existingOrder   = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->findByNumber($sOrderVariables['sOrderNumber']);

                    if (($existingOrder[0] instanceof \Shopware\Models\Order\Order) && $existingOrder[0]->getPaymentStatus()->getId() !== Shopware()->Container()->get('QentaCheckoutPage')->getPaymentStatusId(QentaCEE_QPay_ReturnFactory::STATE_PENDING)) {
                        Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: ' . __METHOD__ . ': do not modify payment status as the order is in a final state!');
                        die(QentaCEE_QPay_ReturnFactory::generateConfirmResponseString('Can not overwrite payment status as the order is in a final state!'));
                    } else if ($existingOrder[0] instanceof \Shopware\Models\Order\Order) {
                        $status = $existingOrder[0]->getPaymentStatus();
                        if ($status->getId() === Shopware()->Container()->get('QentaCheckoutPage')->getPaymentStatusId(QentaCEE_QPay_ReturnFactory::STATE_PENDING)) {
                            // save existing order for failed payment
                            $sOrderNumber = $this->savePaymentStatus(
                                $transactionId,
                                $paymentUniqueId,
                                $paymentState,
                                false
                            );
                            if (isset(Shopware()->Session()->sQentaConfirmMail)) {
                                unset(Shopware()->Session()->sQentaConfirmMail);
                            }
                        }
                    }
                    $errors = array();
                    foreach ( $return->getErrors() as $error ) {
                        $errors[] = $error->getConsumerMessage();
                        $message  = $error->getConsumerMessage();
                    }

                    break;
                case QentaCEE_QPay_ReturnFactory::STATE_CANCEL:
                    break;
                default:
            }
            $update['data'] = serialize($post);
            $update['session'] = serialize($_SESSION); // save back ev. modified sessiondata
            $oTransaction->update($paymentUniqueId, $update);

        } catch (Exception $e) {
            Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: ' . __METHOD__ . '.--' . __LINE__ . ':' . $e->getMessage());
            die(QentaCEE_QPay_ReturnFactory::generateConfirmResponseString(htmlspecialchars($e->getMessage())));
        }

        die(QentaCEE_QPay_ReturnFactory::generateConfirmResponseString($message));
    }

    /**
     * Browser return to the shop
     */
    public function returnAction()
    {
        $paymentUniqueId = $this->Request()->getParam('wQentaCheckoutPageId');

        /** @var Shopware_Plugins_Frontend_QentaCheckoutPage_Models_Transaction $oTransaction */
        $oTransaction = Shopware()->Container()->get('QentaCheckoutPage')->getTransaction();
        $transactionData = $oTransaction->read($paymentUniqueId);

        // write back modified sessiondata, might be modified by the confirm (server2server) request
        $savedSessionData = unserialize($transactionData['session']);
        if (is_array($savedSessionData) && isset($savedSessionData['Shopware'])) {
            //$_SESSION = $savedSessionData;
            Shopware()->Session()->offsetSet('sOrderVariables', $savedSessionData['Shopware']['sOrderVariables']);
            Shopware()->Session()->offsetSet('sQentaConfirmMail', $savedSessionData['Shopware']['sQentaConfirmMail']);
        }

        $result = $this->getOrderByUniqueId($paymentUniqueId);
        if(!$result->cleared)
        {
            $result = new stdClass();
            $result->cleared = false;
        }
        $post = $this->Request()->getPost();
        Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: '.__METHOD__ . '--' . __LINE__ . ':' . print_r($post, 1));

        try {
            $return = QentaCEE_QPay_ReturnFactory::getInstance(
                $post,
                Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->SECRET
            );

            switch ($return->getPaymentState()) {
                case QentaCEE_QPay_ReturnFactory::STATE_SUCCESS:
                    /** @var $return QentaCEE_QPay_Return_Success */

                    Shopware()->Modules()->Basket()->sDeleteBasket();
                    $sRedirectUrl = $this->Front()->Router()->assemble(
                        Array('module' => 'frontend', 'controller' => 'checkout', 'action' => 'finish', 'sUseSSL' => true, 'sUniqueID' => $paymentUniqueId)
                    );
                break;

                case QentaCEE_QPay_ReturnFactory::STATE_PENDING:
                    /** @var $return QentaCEE_QPay_Return_Pending */

                    Shopware()->Modules()->Basket()->sDeleteBasket();
                    $sRedirectUrl = $this->Front()->Router()->assemble(
                        Array('module' => 'frontend', 'controller' => 'checkout', 'action' => 'finish', 'sUseSSL' => true, 'ispending' => true, 'sUniqueID' => $paymentUniqueId)
                    );
                break;

                case QentaCEE_QPay_ReturnFactory::STATE_CANCEL:
                    /** @var $return QentaCEE_QPay_Return_Cancel */

                    if(isset($_SESSION["qcp_redirect_url"])) {
                        unset($_SESSION["qcp_redirect_url"]);
                    }
                    $sRedirectUrl = $this->Front()->Router()->assemble(
                        Array('controller' => 'checkout', 'action' => 'confirm', 'sUseSSL' => true)
                    );
                    Shopware()->Container()->get('QentaCheckoutPage')->qenta_action = 'cancel';
                break;

                case QentaCEE_QPay_ReturnFactory::STATE_FAILURE:
            default:
                    /** @var $return QentaCEE_QPay_Return_Failure */
                    if(isset($_SESSION["qcp_redirect_url"])) {
                       unset($_SESSION["qcp_redirect_url"]);
                    }
                    Shopware()->Container()->get('QentaCheckoutPage')->qenta_message = $return->getErrors()->getConsumerMessage();
                    Shopware()->Container()->get('QentaCheckoutPage')->qenta_action = 'external_error';
                    $sRedirectUrl = $this->Front()->Router()->assemble(
                        Array('controller' => 'checkout', 'action' => 'confirm', 'sUseSSL' => true)
                    );
            }
        } catch (Exception $e) {
            Shopware()->Container()->get('pluginlogger')->error('QentaCheckoutPage: '.__METHOD__ . ':' . $e->getMessage());
            Shopware()->Container()->get('QentaCheckoutPage')->qenta_action = 'failure';
        }

        //reset transactionId
        Shopware()->Container()->get('QentaCheckoutPage')->transactionId = null;

        $bUseIframe = (Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->use_iframe == 1);

        if($bUseIframe)
        {
            $this->View()->loadTemplate('responsive/frontend/qenta_checkout_page/return.tpl');
            $this->View()->redirectUrl = $sRedirectUrl;
        }
        else
        {
            $this->redirect($sRedirectUrl);
        }
    }

    /**
     * Save return data
     *
     * @param QentaCEE_Stdlib_Return_Success $return
     * @param null $orderNumber
     * @internal param null $transactionId
     */
    protected function saveComments(QentaCEE_Stdlib_Return_ReturnAbstract $return = null, $orderNumber = null)
    {
        $comments = array();
        $comments[] = "------- Qenta Response Data --------";
        $comments[] = "---------------------------------------";
        $gatewayReferenceNumber ='';
        foreach ($return->getReturned() as $name => $value) {
            if ($name == 'sCoreId' || $name == 'wQentaCheckoutPageId') {
                continue;
            }
            if($name == 'gatewayReferenceNumber'){
                $gatewayReferenceNumber = $value;
            }
            $comments[] = sprintf('%s: %s', $name, $value);
        }
        $comments[] = "---------------------------------------";



        $field = Shopware()->Container()->get('QentaCheckoutPage')->getConfig()->saveResponseTo();
        Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: Comment field:' . $field);
        if ($field == 'internalcomment') {

            Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: Saving internal comment');
            Shopware()->Db()->update(
                's_order',
                array($field => implode("\n", $comments)),
                'ordernumber = \'' . $orderNumber . '\''
            );
        } else {
            $sql = Shopware()->Db()->select()
                ->from('s_order', array('id'))
                ->where('ordernumber = ?', array($orderNumber));
            $orderId = Shopware()->Db()->fetchOne($sql);

            Shopware()->Container()->get('pluginlogger')->info('QentaCheckoutPage: Saving attribute');
            Shopware()->Db()->update(
                's_order_attributes',
                array($field => implode("\n", $comments)),
                'orderID = ' . (int)$orderId
            );
        }
    }

    protected function getOrderByUniqueId($sTemporaryId)
    {
        $sql = Shopware()->Db()->select()
            ->from('s_order', array('id','cleared','temporaryID', 'transactionID'))
            ->where('temporaryID = ?', array($sTemporaryId));
        return json_decode(json_encode(Shopware()->Db()->fetchRow($sql)),FALSE);
    }

    protected function updateTransactionIdByUniqueId($sTemporaryId, $sTransactionId)
    {
        return Shopware()->Db()->update('s_order', array('transactionID' => $sTransactionId), "temporaryID = '$sTemporaryId'");
    }

    public function getWhitelistedCSRFActions()
    {
        return array(
            'confirm',
            'return'
        );
    }
}
