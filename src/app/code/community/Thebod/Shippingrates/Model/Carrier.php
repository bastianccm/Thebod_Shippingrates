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
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Thebod_Shippingrates_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract {
    protected $_code = 'shippingrates';

    /**
     * returns notification mail address for given rate
     *
     * @param string $code
     * @return string
     */
    public function getNotificationMail($code) {
        $data = $this->getConfigData('shippingconfig');

        if(!is_array($data)) {
            $data = unserialize(base64_decode($data));
        }

        foreach($data['code'] as $k => $v) {
            if($this->_code . '_' . $v == $code) {
                return $data['email'][$k];
            }
        }
    }

    /**
     * applies filters for rate on request
     *
     * @param array $rate
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean
     */
    public function checkRate(array $rate, Mage_Shipping_Model_Rate_Request $request) {
        if(!Mage::getSingleton('checkout/session')->hasQuote()) {
            return true;
        }

        if(!isset($rate['filter'])) {
            return true;
        }

        $filter = explode(';', $rate['filter']);
        $passed = true;
        foreach($filter as $f) {
            $f = explode(':', $f);
            $condition = $f[0];
            $value = isset($f[1]) && $f[1] ? $f[1] : false;

            if($value === false) {
                continue;
            }

            switch($condition) {
                case 'min_qty':
                    if($request->getPackageQty() < $value) {
                        $passed = false;
                    }
                    break;

                case 'max_qty':
                    if($request->getPackageQty() > $value) {
                        $passed = false;
                    }
                    break;

                case 'min_subtotal':
                    Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
                    $subtotal = Mage::getSingleton('checkout/session')->getQuote()->getSubtotal();
                    if($subtotal < $value) {
                        $passed = false;
                    }
                    break;

                case 'max_subtotal':
                    Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
                    $subtotal = Mage::getSingleton('checkout/session')->getQuote()->getSubtotal();
                    if($subtotal > $value) {
                        $passed = false;
                    }
                    break;

                case 'min_weight':
                    if($request->getPackageWeight() < $value) {
                        $passed = false;
                    }
                    break;

                case 'max_weight':
                    if($request->getPackageWeight() > $value) {
                        $passed = false;
                    }
                    break;
            }
        }
        return $passed;
    }

    /**
     * collect shipping rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if(!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $rates = $this->getRate($this->getConfigData('shippingconfig'));

        foreach($rates as $rate) {
            if($this->checkRate($rate, $request)) {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));

                //$method->setMethod($this->_code . '_' . $rate['code']);
                $method->setMethod($rate['code']);
                $method->setMethodTitle($rate['title']);

                $method->setPrice($rate['price']);

                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * return shipping table rates
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return array
     */
    public function getRate($data) {
        $rates = array();
        $methods = array();

        if(!is_array($data)) {
            $data = unserialize(base64_decode($data));
        }

        /* rearrange array */
        /* $data: array(code => array('', 'a', 'b'), price => array('', 10, 20), description => array('', 'desc1', 'desc2')) */
        /* $key: code, then price, then data - $value: array*/
        foreach($data as $key => $value) {
            /* $value: array, $methodid: id of this method, $methodvalue: value of this entry */
            foreach($value as $methodid => $methodvalue) {
                /* we ignore this if methodid == 0 */
                if($methodid) {
                    /* methods = array(methodid => array(code => 'a', price => 10, description => 'desc1')) ... */
                    $methods[$methodid][$key] = $methodvalue;
                }
            }
        }

        foreach($methods as $method) {
            $code = trim($method['code']);
            $price = trim($method['price']);
            $filter = trim($method['filter']);
            $title = nl2br(trim($method['description']));

            $title = str_replace(array('=>', '<='), array('<strong>', '</strong>'), $title);

            $rates[] = array(
                'code' => $code,
                'title' => $title,
                'price' => $price,
                'filter' => $filter,
            );
        }

        krsort($rates);

        return $rates;
    }

    /**
     * return allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowedMethods = array(
            $this->_code => $this->getConfigData('name'),
        );

        return $allowedMethods;
    }
}
