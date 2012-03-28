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
class Thebod_Shippingrates_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Rearranges the complete shipping rates array
     *
     * @param array $data array(
     *     code  => array('', 'a', 'b'),
     *     price => array('', 10, 20),
     *     description => array('', 'desc1', 'desc2')
     * )
     * @return array array(
     *     methodid => array(
     *         code => 'a',
     *         price => 10,
     *         description => 'desc1'
     *     )
     * )
     */
    public function rearrangeShippingRates($data)
    {
        $methods = array();
        foreach ($data as $key => $value) {
            /*
             * $methodId => id of this method
             * $methodValue => value of this entry
             */
            foreach ($value as $methodId => $methodValue) {
                /* we ignore this if $methodId == 0 */
                if ($methodId) {
                    $methods[$methodId][$key] = $methodValue;
                }
            }
        }

        return $methods;
    }
}