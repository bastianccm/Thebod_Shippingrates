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
class Thebod_Shippingrates_Model_Observer
{
    /**
     * observer to send mail notification
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function checkoutTypeOnepageSaveOrderAfter(Varien_Event_Observer $observer)
    {
        /* @var $shippingModel Thebod_Shippingrates_Model_Email */
        $shippingModel = Mage::getModel('shippingrates/email');
        $shippingModel->sendEmailNotification($observer->getOrder());
    }

    /**
     * check payment filters
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('checkout/session')->hasQuote()) {
            return;
        }

        $shippingMethod = explode('_', $this->_getShippingMethod());
        $shippingConfig = $this->_getShippingConfig();

        $shippingCarrier = array_shift($shippingMethod);
        $shippingCode = implode('_', $shippingMethod);

        if ($shippingCarrier != 'shippingrates') {
            return;
        }

        $configKey = -1;
        foreach ($shippingConfig['code'] as $k => $v) {
            if($v == $shippingCode) {
                $configKey = $k;
            }
        }
        if ($configKey == -1) {
            return;
        }

        $checkResult = $observer->getEvent()->getResult();
        $method = $observer->getEvent()->getMethodInstance();

        $filter = explode(';', $shippingConfig['filter'][$configKey]);
        foreach ($filter as $k => $v) {
            $v = explode(':', $v);
            if (isset($v[1]) && strlen($v[1]) && ($v[0] == 'payment') && !in_array($method->getCode(), explode(',', $v[1]))) {
                $checkResult->isAvailable = false;
            }
        }
    }

    /**
     * @return Mage_Shipping_Model_Carrier_Abstract
     */
    protected function _getShippingMethod()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
    }

    /**
     * @return string
     */
    protected function _getShippingConfig()
    {
        $path = 'carriers/shippingrates/shippingconfig';

        return unserialize(base64_decode(Mage::getStoreConfig($path, Mage::app()->getStore())));
    }
}