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
class Thebod_Shippingrates_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract
{
    /**
     * @var string Shipping Method Code
     */
    protected $_code = 'shippingrates';

    /**
     * returns notification mail address for given rate
     *
     * @param string $code
     * @return string
     */
    public function getNotificationMail($code)
    {
        $data = $this->getConfigData('shippingconfig');

        if (!is_array($data)) {
            $data = unserialize(base64_decode($data));
        }

        /* searches correct mail address */
        foreach ($data['code'] as $k => $v) {
            if (($this->_code . '_' . $v) == $code) {
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
    public function checkRate(array $rate, Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::getSingleton('checkout/session')->getQuoteId()) {
            return true;
        }

        if (!isset($rate['filter'])) {
            return true;
        }

        $filters = explode(';', $rate['filter']);
        $passed = true;
        foreach ($filters as $filter) {
            $filter = explode(':', $filter);
            $condition = $filter[0];
            $value = isset($filter[1]) && $filter[1] ? $filter[1] : false;

            if ($value === false) {
                continue;
            }

            if($this->getConfigData('with_tax')) {
                $packageValue = $request->getBaseSubtotalInclTax();
            } else {
                $packageValue= $request->getPackageValueWithDiscount();
            }

            switch ($condition) {
                case 'min_qty':
                    if ($request->getPackageQty() < $value) {
                        $passed = false;
                    }
                    break;

                case 'max_qty':
                    if ($request->getPackageQty() > $value) {
                        $passed = false;
                    }
                    break;

                case 'min_subtotal':
                    if ($packageValue < $value) {
                        $passed = false;
                    }
                    break;

                case 'max_subtotal':
                    if ($packageValue > $value) {
                        $passed = false;
                    }
                    break;

                case 'min_weight':
                    if ($request->getPackageWeight() < $value) {
                        $passed = false;
                    }
                    break;

                case 'max_weight':
                    if ($request->getPackageWeight() > $value) {
                        $passed = false;
                    }
                    break;

                case 'countries':
                    $dest = strtolower($request->getDestCountryId());
                    if ($value[0] == '!') {
                        $exclude = true;
                        $value[0] = ' ';   // will be removed with trim() ;-)
                    } else {
                        $exclude = false;
                    }
                    $allowed = explode(',', strtolower($value));
                    foreach ($allowed as $k => $v) {
                        $allowed[$k] = trim($v);
                    }

                    if ($exclude) {
                        if (in_array($dest, $allowed) && count($allowed)) {
                            $passed = false;
                        }
                    } else {
                        if (!in_array($dest, $allowed) && count($allowed)) {
                            $passed = false;
                        }
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
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $rates = $this->getRates($this->getConfigData('shippingconfig'));

        $result = Mage::getModel('shipping/rate_result');
        foreach ($rates as $rate) {
            if ($this->checkRate($rate, $request)) {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));

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
    public function getRates($data)
    {
        if (!is_array($data)) {
            $data = unserialize(base64_decode($data));
        }

        $methods = Mage::helper('shippingrates')->rearrangeShippingRates($data);
        $rates   = array();
        foreach ($methods as $method) {
            $code   = trim($method['code']);
            $price  = trim($method['price']);
            $filter = trim($method['filter']);
            $title  = nl2br(trim($method['description']));

            $rates[] = array(
                'code'   => $code,
                'title'  => $title,
                'price'  => $price,
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
            $this->_code => $this->getConfigData('name')
        );
        return $allowedMethods;
    }

    public function isActive()
    {
        $data = unserialize(base64_decode($this->getConfigData('shippingconfig')));

        if (!$data) {
            return false;
        }
        return parent::isActive();
    }
}
