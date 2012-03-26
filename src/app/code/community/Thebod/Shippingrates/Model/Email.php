<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Thebod
 * @package     Thebod_Shippingrates
 * @copyright   Copyright (c) 2012 Bastian Ike (http://thebod.de/)
 * @author      Bastian Ike <b-ike@b-ike.de>
 * @license     http://creativecommons.org/licenses/by/3.0/ CC-BY 3.0
 */
class Thebod_Shippingrates_Model_Email extends Mage_Core_Model_Abstract
{
    /**
     * Sends notification mail for selected shipping rate
     *
     * The code for this functionality is based on Mage_Sales_Model_Order::sendNewOrderEmail()
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     * @throws Exception
     * @see Mage_Sales_Model_Order
     */
    public function sendEmailNotification($order)
    {
        if (strncmp($order->getShippingMethod(), 'shippingrates_', 14) != 0) {
            return false;
        }

        $notificationMail = $order->getShippingCarrier()->getNotificationMail($order->getShippingMethod());
        if (!strlen(trim($notificationMail))) {
            return false;
        }

        $storeId = $order->getStore()->getId();

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');

        $emailInfo->addTo($notificationMail);

        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
            array(
                'order'        => $order,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        return true;
    }
}