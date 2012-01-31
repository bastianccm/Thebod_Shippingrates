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
class Thebod_Shippingrates_Block_Adminhtml_Config extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);

        $html = '<div id="shippingconfig_template" style="display:none">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</div>';

        $html .= $this->_getAddRowButtonHtml('shippingconfig_container', 'shippingconfig_template', $this->__('Add'));

        $html .= '<ul id="shippingconfig_container">';
        $html .= '<li style="width: 500px;">
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 100px;display: inline-block;">' . $this->__('code') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 30px;display: inline-block;">' . $this->__('price') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 150px;display: inline-block;">' . $this->__('description') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 100px;display: inline-block;">' . $this->__('notification mail (optional)') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 100px;display: inline-block;">' . $this->__('filter') . '</span>
        </li>';

        if ($this->_getValue('code')) {
            foreach ($this->_getValue('code') as $k => $v) {
                if ($k) {
                    $html .= $this->_getRowTemplateHtml($k);
                }
            }
        }
        $html .= '</ul>';

        return $html;
    }

    protected function _getRowTemplateHtml($key = 0) {
        $html = '<li style="display: block; width: 550px;">';
        $html .= '<div style="float: left;">';
        $html .= '<input class="input-text ' . $this->_getDisabled() . '" style="vertical-align: top; width: 100px; margin: 0 6px;" name="' . $this->getElement()->getName() . '[code][]" value="' . $this->_getValue('code/' . $key) . '"/>';
        $html .= '<input class="input-text ' . $this->_getDisabled() . '" style="vertical-align: top; width: 30px; margin: 0 6px;" name="' . $this->getElement()->getName() . '[price][]" value="' . $this->_getValue('price/' . $key) . '"/>';
        $html .= '<textarea onfocus="this.style.height=\'100px\'" onblur="this.style.height=\'16px\'" style="vertical-align: top; width: 150px; margin: 0 6px; height:16px;" name="' . $this->getElement()->getName() . '[description][]" class="' . $this->_getDisabled() . '">' . $this->_getValue('description/' . $key) . '</textarea>';
        $html .= '<input class="input-text ' . $this->_getDisabled() . '" style="vertical-align: top; width: 100px; margin: 0 6px;" name="' . $this->getElement()->getName() . '[email][]" value="' . $this->_getValue('email/' . $key) . '" />';
        $html .= '<input style="display: none" name="' . $this->getElement()->getName() . '[filter][]" value="' . $this->_getValue('filter/' . $key) . '"/>';
        $html .= '<button class="scalable show-hide v-middle ' . $this->_getDisabled() . '" type="button"><span></span></button>&nbsp;';
        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    protected function _getDisabled() {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key) {
        return $this->getElement()->getData('value/' . $key);
    }

    protected function _getAddRowButtonHtml($container, $template, $title = 'Add') {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('add ' . $this->_getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.insert($('" . $container . "'), {bottom: $('" . $template . "').innerHTML})")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    protected function _getRemoveRowButtonHtml($selector = 'li', $title = 'Delete') {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('delete ' . $this->_getDisabled())
                    ->setOnClick("Element.remove($(this).up('" . $selector . "'))")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }

}