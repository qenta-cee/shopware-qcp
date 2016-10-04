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

use Shopware\Components\CSRFWhitelistAware;

/**
 * controller class handling Wirecard Checkout Page Requests^
 *
 * WirecardCheckoutPage Controller
 */
class Shopware_Controllers_Frontend_WirecardCheckoutPage extends Shopware_Controllers_Frontend_Payment implements CSRFWhitelistAware
{
    /**
     * Index action
     * Different view for wcp and other payment methods
     */
    public function indexAction()
    {
        /** @var Shopware_Plugins_Frontend_WirecardCheckoutPage_Models_Page $oPageModel */
        $oPageModel = Shopware()->WirecardCheckoutPage()->getPage();

        $sPaymentType = Shopware()->WirecardCheckoutPage()->getPaymentShortName();
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
                'forceSecure' => true,
                'appendSession' => true
            )
        );
        $aParams = Array(
            'sCoreId' => Shopware()->SessionID(),
            '__shop' => Shopware()->Shop()->getId(),
            'wWirecardCheckoutPageId' => Shopware()->WirecardCheckoutPage()->getTransactionId(),
            'displayText' => Shopware()->WirecardCheckoutPage()->getConfig()->display_text
        );

        $checkoutUrl = $this->Front()->Router()->assemble(
            Array('controller' => 'checkout', 'action' => 'confirm', 'sUseSSL' => true)
        );

        $oResponse = $oPageModel->initiatePayment($sPaymentType, $fAmount, $sCurrency, $sReturnUrl, $sConfigmrUrl, $aParams);
        if ($oResponse === null) {
            $this->redirect($checkoutUrl);
            return;
        }

        if($oResponse->hasFailed())
        {
            Shopware()->WirecardCheckoutPage()->getLog()->Debug(__METHOD__ . ':' . $oResponse->getError());
            Shopware()->WirecardCheckoutPage()->wirecard_message = $oResponse->getError();
            Shopware()->WirecardCheckoutPage()->wirecard_action = 'failure';
            //if an error occurs we should not show followup page in iframe.
            $bUseIframe = false;
            $sRedirectUrl = $checkoutUrl;
        }
        else
        {
            $bUseIframe = (Shopware()->WirecardCheckoutPage()->getConfig()->use_iframe == 1);
            $sRedirectUrl = $oResponse->getRedirectUrl();
        }

        if($bUseIframe)
        {
            if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
                $this->View()->loadTemplate('responsive/frontend/wirecard_checkout_page/index.tpl');
            } else {
            $this->View()->loadTemplate('frontend/checkout/wirecard_page.tpl');
            }

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

    public function confirmAction()
    {
        try {
            Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

            $post = $this->Request()->getPost();
            Shopware()->WirecardCheckoutPage()->getLog()->Debug(__METHOD__ . '--' . __LINE__ . ':' . print_r($post, 1));

            $paymentUniqueId = $this->Request()->getParam('wWirecardCheckoutPageId');
            if (Shopware()->WirecardCheckoutPage()->getConfig()->setAsTransactionID() == 'gatewayReferenceNumber') {
                $sTransactionIdField = 'gatewayReferenceNumber';
            } else {
                $sTransactionIdField = 'orderNumber';
            }
            $transactionId = $this->Request()->getParam($sTransactionIdField, $paymentUniqueId);

            $return = WirecardCEE_QPay_ReturnFactory::getInstance($post,
                Shopware()->WirecardCheckoutPage()->getConfig()->SECRET);
            $paymentState = Shopware()->WirecardCheckoutPage()->getPaymentStatusId($return->getPaymentState());

            if (!$return->validate()) {
                throw new WirecardCEE_QPay_Exception_InvalidResponseException(json_encode($return->getMessage()));
            }

            $oOrder = $this->getOrderByUniqueId($paymentUniqueId);
            if (!empty($oOrder) && $oOrder->temporaryID == $oOrder->transactionID && $paymentUniqueId != $transactionId) {
                $this->updateTransactionIdByUniqueId($paymentUniqueId, $transactionId);
            }

            // data for confirm mail
            $sOrderVariables = Shopware()->Session()->sOrderVariables;
            $userData = Shopware()->Session()->sOrderVariables['sUserData'];
            $basketData = Shopware()->Session()->sOrderVariables['sBasket'];

            $shop = Shopware()->Shop();
            $mainShop = $shop->getMain() !== null ? $shop->getMain() : $shop;
            $details = $basketData['content'];

            $context = array(
                'sOrderDetails' => $details,
                'billingaddress' => $userData['billingaddress'],
                'shippingaddress' => $userData['shippingaddress'],
                'additional' => $userData['additional'],

                'sShippingCosts' => $sOrderVariables['sShippingcosts'],
                'sAmount' => $sOrderVariables['sAmount'],
                'sAmountNet' => $sOrderVariables['sAmountNet'],

                'sOrderNumber' => $sOrderVariables['sOrderNumber'],
                'sComment' => $sOrderVariables['sComment'],
                'sCurrency' => $sOrderVariables['sSYSTEM']->sCurrency['currency'],
                'sLanguage' => $shop->getId(),

                'sSubShop' => $mainShop->getId(),
                'sNet' => $sOrderVariables['sNet'],
                'sTaxRates' => $sOrderVariables['sTaxRates'],
            );

            $sUser = array(
                'billing_salutation' => $userData['billingaddress']['salutation'],
                'billing_firstname' => $userData['billingaddress']['firstname'],
                'billing_lastname' => $userData['billingaddress']['lastname']
            );

            $bSendEmail = false;

            switch ($return->getPaymentState()) {
                case WirecardCEE_QPay_ReturnFactory::STATE_SUCCESS:
                    $sOrderNumber = $this->saveOrder(
                        $transactionId,
                        $paymentUniqueId,
                        $paymentState,
                        $bSendEmail
                    );

                    if (!$sOrderNumber) {
                        throw new Enlight_Exception(sprintf('Unabled to save order (%s) with transactionId %s. Shopware orderState: %s',
                            $sOrderNumber, $paymentUniqueId, $paymentState));
                    }

                    $context['sOrderDay'] = date("d.m.Y");
                    $context['sOrderTime'] = date("H:i");
                    $context['sOrderNumber'] = Shopware()->Session()->sOrderVariables['sOrderNumber'];

                    // Sending confirm mail for successfull order
                    $mail = Shopware()->TemplateMail()->createMail('sORDER', $context);
                    $mail->addTo($userData['additional']['user']['email']);

                    try {
                        $mail->send();
                    } catch (\Exception $e) {
                        $variables = Shopware()->Session()->offsetGet('sOrderVariables');
                        $variables['sOrderNumber'] = $context['sOrderNumber'];
                        $variables['confirmMailDeliveryFailed'] = true;
                        Shopware()->Session()->offsetSet('sOrderVariables', $variables);
                    }
                    if (Shopware()->WirecardCheckoutPage()->getConfig()->saveResponseTo()) {
                        $this->saveComments($return, $sOrderNumber);
                    }
                    break;
                case WirecardCEE_QPay_ReturnFactory::STATE_PENDING:
                    $sOrderNumber = $this->saveOrder(
                        $transactionId,
                        $paymentUniqueId,
                        $paymentState,
                        $bSendEmail
                    );

                    if (!$sOrderNumber) {
                        throw new Enlight_Exception(sprintf('Unabled to save order (%s) with transactionId %s. Shopware orderState: %s',
                            $sOrderNumber, $paymentUniqueId, $paymentState));
                    }

                    //only send pendingmail if configured
                    if (Shopware()->WirecardCheckoutPage()->getConfig()->SEND_PENDING_MAILS) {
                        $existingOrder = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->findByNumber($sOrderVariables['sOrderNumber']);
                        $status = $existingOrder[0]->getPaymentStatus();

                        $orderDate = 'dd.mm.yyyy';

                        if ($details != null) {
                            $orderDate = $details[0]['datum'];
                        }
                        $sOrder = array(
                            'ordernumber' => $sOrderVariables['sOrderNumber'],
                            'status_description' => $status->getName(),
                            'ordertime' => $orderDate
                        );

                        $pendingContext = array(
                            'sUser' => $sUser,
                            'sOrder' => $sOrder
                        );

                        // Sending information mail for successfull order
                        $mail = Shopware()->TemplateMail()->createMail('sORDERSTATEMAIL1', $pendingContext);
                        $mail->addTo($userData['additional']['user']['email']);

                        try {
                            $mail->send();
                        } catch (\Exception $e) {
                            $variables = Shopware()->Session()->offsetGet('sOrderVariables');
                            $variables['sOrderNumber'] = $sOrderVariables['sOrderNumber'];
                            $variables['confirmMailDeliveryFailed'] = true;
                            Shopware()->Session()->offsetSet('sOrderVariables', $variables);
                        }
                    }
                    if (Shopware()->WirecardCheckoutPage()->getConfig()->saveResponseTo()) {
                        $this->saveComments($return, $sOrderNumber);
                    }
                    break;
                case WirecardCEE_QPay_ReturnFactory::STATE_CANCEL:
                    break;
                case WirecardCEE_QPay_ReturnFactory::STATE_FAILURE:
                    $sOrderVariables = Shopware()->Session()->sOrderVariables;
                    $existingOrder = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')->findByNumber($sOrderVariables['sOrderNumber']);
                    if($existingOrder[0]->getPaymentStatus()->getName() === 'delayed'){
                        Shopware()->Models()->remove($existingOrder[0]);
                        Shopware()->Models()->flush();

                        $status = $existingOrder[0]->getPaymentStatus();
                        $orderDate = 'dd.mm.yyyy';

                        if($details != null){
                            $orderDate = $details[0]['datum'];
                        }
                        $sOrder = array(
                            'ordernumber' => $sOrderVariables['sOrderNumber'],
                            'status_description' => $status->getName(),
                            'ordertime' => $orderDate
                        );

                        $pendingContext = array (
                            'sUser' => $sUser,
                            'sOrder' => $sOrder
                        );

                        // Sending information mail for failed order after pending
                        $mail = Shopware()->TemplateMail()->createMail('sORDERSTATEMAIL4', $pendingContext);
                        $mail->addTo($userData['additional']['user']['email']);

                        try {
                            $mail->send();
                        } catch (\Exception $e) {
                            $variables = Shopware()->Session()->offsetGet('sOrderVariables');
                            $variables['sOrderNumber'] = $context['sOrderNumber'];
                            $variables['confirmMailDeliveryFailed'] = true;
                            Shopware()->Session()->offsetSet('sOrderVariables', $variables);
                        }
                    }
                    break;
            }
            $update['data'] = serialize($post);

        } catch (Exception $e) {
            Shopware()->WirecardCheckoutPage()->getLog()->Debug(__METHOD__ . '.--' . __LINE__ . ':' . $e->getMessage());
            print WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(htmlspecialchars($e->getMessage()));
            return;
        }

        print WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString();
        return;
    }

    /**
     * Browser return to the shop
     */
    public function returnAction()
    {
        $paymentUniqueId = $this->Request()->getParam('wWirecardCheckoutPageId');

        $result = $this->getOrderByUniqueId($paymentUniqueId);
        if(!$result->cleared)
        {
            $result = new stdClass();
            $result->cleared = false;
        }
        $post = $this->Request()->getPost();
        Shopware()->WirecardCheckoutPage()->getLog()->Debug(__METHOD__ . '--' . __LINE__ . ':' . print_r($post, 1));

        try {
            $return = WirecardCEE_QPay_ReturnFactory::getInstance(
                $post,
                Shopware()->WirecardCheckoutPage()->getConfig()->SECRET
            );

            switch ($return->getPaymentState()) {
                case WirecardCEE_QPay_ReturnFactory::STATE_SUCCESS:
                    /** @var $return WirecardCEE_QPay_Return_Success */
                $sRedirectUrl = $this->Front()->Router()->assemble(
                    Array('controller' => 'checkout', 'action' => 'finish', 'sUseSSL' => true)
                );
                break;

                case WirecardCEE_QPay_ReturnFactory::STATE_PENDING:
                    /** @var $return WirecardCEE_QPay_Return_Pending */
                $sRedirectUrl = $this->Front()->Router()->assemble(
                    Array('controller' => 'checkout', 'action' => 'finish', 'sUseSSL' => true, 'pending' => true)
                );
                break;

                case WirecardCEE_QPay_ReturnFactory::STATE_CANCEL:
                    /** @var $return WirecardCEE_QPay_Return_Cancel */
                $sRedirectUrl = $this->Front()->Router()->assemble(
                    Array('controller' => 'checkout', 'action' => 'confirm', 'sUseSSL' => true)
                );
                Shopware()->WirecardCheckoutPage()->wirecard_action = 'cancel';
                break;

                case WirecardCEE_QPay_ReturnFactory::STATE_FAILURE:
            default:
                    /** @var $return WirecardCEE_QPay_Return_Failure */
                    Shopware()->WirecardCheckoutPage()->wirecard_message = $return->getErrors()->getConsumerMessage();
                    Shopware()->WirecardCheckoutPage()->wirecard_action = 'external_error';
                $sRedirectUrl = $this->Front()->Router()->assemble(
                    Array('controller' => 'checkout', 'action' => 'confirm', 'sUseSSL' => true)
                );
        }
        } catch (Exception $e) {
            Shopware()->WirecardCheckoutPage()->getLog()->Err(__METHOD__ . ':' . $e->getMessage());
            Shopware()->WirecardCheckoutPage()->wirecard_action = 'failure';
        }

        //reset transactionId
        Shopware()->WirecardCheckoutPage()->transactionId = null;

        $bUseIframe = (Shopware()->WirecardCheckoutPage()->getConfig()->use_iframe == 1);

        if($bUseIframe)
        {
            if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
                $this->View()->loadTemplate('responsive/frontend/wirecard_checkout_page/return.tpl');
            } else {
            $this->View()->loadTemplate('frontend/checkout/wirecard_return.tpl');
            }

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
     * @param WirecardCEE_Stdlib_Return_Success $return
     * @param null $orderNumber
     * @internal param null $transactionId
     */
    protected function saveComments(WirecardCEE_Stdlib_Return_Success $return = null, $orderNumber = null)
    {
        $comments = array();
        $gatewayReferenceNumber ='';
        foreach ($return->getReturned() as $name => $value) {
            if ($name == 'sCoreId' || $name == 'wWirecardCheckoutPageId') {
                continue;
            }
            if($name == 'gatewayReferenceNumber'){
                $gatewayReferenceNumber = $value;
            }
            $comments[] = sprintf('%s: %s', $name, $value);
        }

        $field = Shopware()->WirecardCheckoutPage()->getConfig()->saveResponseTo();
        Shopware()->WirecardCheckoutPage()->getLog()->Debug('Comment field:' . $field);
        if ($field == 'internalcomment') {

            Shopware()->WirecardCheckoutPage()->getLog()->Debug('Saving internal comment');
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

            Shopware()->WirecardCheckoutPage()->getLog()->Debug('Saving attribute');
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
