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
class Thebod_Shippingrates_Block_Adminhtml_Config extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var array Add buttons
     */
    protected $_addRowButtonHtml = array();

    /**
     * @var array Remove buttons
     */
    protected $_removeRowButtonHtml = array();

    /**
     * basic template for shipping rates configurator
     *
     * @todo move inline html into templates
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<div id="shippingconfig_template" style="display:none">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</div>';

        $html .= '
            <script type="text/javascript">
                /* sorry, i\'m not a js-wizard :( */

                var filter_btn;

                function shippingconfig_filter_show(btn) {
                    filter_btn = btn;
                    $(\'shippingconfig_filter_name\').update($(btn).up(1).down(\'.name\').value);
                    if($(btn).up(1).down(\'.filter\').value == \'\') {
                        $(btn).up(1).down(\'.filter\').value = \'min_qty:;max_qty:;min_subtotal:;max_subtotal:;min_weight:;max_weight:;countries:;payment:;\';
                    }

                    filter = $(btn).up(1).down(\'.filter\').value.split(\';\').each(function(e,i) {
                        e = e.split(\':\');
                        if($(\'shippingconfig_filter_\' + e[0])) {
                            $(\'shippingconfig_filter_\' + e[0]).value = e[1];
                        }
                    } );
                    $(\'shippingconfig_filter\').show();
                }

                function shippingconfig_filter_hide() {
                    btn = filter_btn;
                    $(\'shippingconfig_filter\').hide();

                    filter = \'min_qty:\' + $(\'shippingconfig_filter_min_qty\').value + \';\';
                    filter += \'max_qty:\' + $(\'shippingconfig_filter_max_qty\').value + \';\';
                    filter += \'min_subtotal:\' + $(\'shippingconfig_filter_min_subtotal\').value + \';\';
                    filter += \'max_subtotal:\' + $(\'shippingconfig_filter_max_subtotal\').value + \';\';
                    filter += \'min_weight:\' + $(\'shippingconfig_filter_min_weight\').value + \';\';
                    filter += \'max_weight:\' + $(\'shippingconfig_filter_max_weight\').value + \';\';
                    filter += \'countries:\' + $(\'shippingconfig_filter_countries\').value + \';\';
                    filter += \'payment:\' + $(\'shippingconfig_filter_payment\').value + \';\';

                    $(btn).up(1).down(\'.filter\').value = filter;

                }
            </script>
            <div id="shippingconfig_filter" style="width:500px;position:absolute;z-index:999;display: none;">
                <div class="entry-edit-head"><strong>' . $this->__('configure filter') . ' <span id="shippingconfig_filter_name"></span></strong></div>
                <div class="box">
                ' . $this->__('(empty fields will be ignored)') . '<br/>
                <table border="0">
                <tr>
                    <td>' . $this->__('min. QTY:') . '</td><td><input class="input-text" style="width: 100px;" id="shippingconfig_filter_min_qty"/></td>
                    <td>' . $this->__('max. QTY:') . '</td><td><input class="input-text" style="width: 100px;" id="shippingconfig_filter_max_qty"/></td>
                </tr>
                <tr>
                    <td>' . $this->__('min. subtotal:') . '</td><td><input class="input-text" style="width: 100px;" id="shippingconfig_filter_min_subtotal"/></td>
                    <td>' . $this->__('max. subtotal:') . '</td><td><input class="input-text" style="width: 100px;" id="shippingconfig_filter_max_subtotal"/></td>
                </tr>
                <tr>
                    <td>' . $this->__('min. weight:') . '</td><td><input class="input-text" style="width: 100px;" id="shippingconfig_filter_min_weight"/></td>
                    <td>' . $this->__('max. weight:') . '</td><td><input class="input-text" style="width: 100px;" id="shippingconfig_filter_max_weight"/></td>
                </tr>
                <tr>
                    <td colspan="2">' . $this->__('limit to countries: (e.g. "DE" or "DE,AT,CH"). Use ! as first character to exclude from specified countries (e.g. "!DE,AT"). You cannot combine include and exclude right now!') . '</td><td colspan="2"><input class="input-text" style="width: 100px;" id="shippingconfig_filter_countries"/></td>
                </tr>
                <tr>
                    <td colspan="2">' . $this->__('limit payment methods: (payment method code, comma-separated, e.g. "checkmo")') . '</td><td colspan="2"><input class="input-text" style="width: 100px;" id="shippingconfig_filter_payment"/></td>
                </tr>
                </table>
                <div style="text-align: right;">
                    <button onclick="shippingconfig_filter_hide()" type="button">' . $this->__('save filter') . '</button>
                </div>
                </div>
            </div>';

        $html .= $this->_getAddRowButtonHtml('shippingconfig_container', 'shippingconfig_template', $this->__('Add'));

        $html .= '<ul id="shippingconfig_container">';
        $html .= '<li style="width: 500px;">
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 100px;display: inline-block;">' . $this->__('code') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 30px;display: inline-block;">' . $this->__('price') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 150px;display: inline-block;">' . $this->__('description') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 100px;display: inline-block;">' . $this->__('notification mail (optional)') . '</span>
            <span style="border-left: dotted 1px #888; padding: 0 5px;margin: 0 2px;width: 100px;display: none;">' . $this->__('filter') . '</span>
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

    /**
     * build config line
     *
     * @param int $key
     * @return string
     */
    protected function _getRowTemplateHtml($key = 0)
    {
        $html = '<li style="display: block; width: 550px;">';
        $html .= '<div style="float: left;">';
        $html .= '<input class="name input-text ' . $this->_getDisabled() . '" style="vertical-align: top; width: 100px; margin: 0 6px;" name="' . $this->getElement()->getName() . '[code][]" value="' . $this->_getValue('code/' . $key) . '"/>';
        $html .= '<input class="input-text ' . $this->_getDisabled() . '" style="vertical-align: top; width: 30px; margin: 0 6px;" name="' . $this->getElement()->getName() . '[price][]" value="' . $this->_getValue('price/' . $key) . '"/>';
        $html .= '<textarea onfocus="this.style.height=\'100px\'" onblur="this.style.height=\'16px\'" style="vertical-align: top; width: 150px; margin: 0 6px; height:16px;" name="' . $this->getElement()->getName() . '[description][]" class="' . $this->_getDisabled() . '">' . $this->_getValue('description/' . $key) . '</textarea>';
        $html .= '<input class="input-text ' . $this->_getDisabled() . '" style="vertical-align: top; width: 100px; margin: 0 6px;" name="' . $this->getElement()->getName() . '[email][]" value="' . $this->_getValue('email/' . $key) . '" />';
        $html .= '<input style="display:none" class="filter" name="' . $this->getElement()->getName() . '[filter][]" value="' . $this->_getValue('filter/' . $key) . '" />';
        $html .= '<button class="show-hide v-middle ' . $this->_getDisabled() . '" type="button" onclick="shippingconfig_filter_show(this)"><span></span></button>&nbsp;';
        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    /**
     * sets 'disabled' mark for css class
     *
     * @return string
     */
    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    /**
     * config value getter
     *
     * @param string $key
     * @return string
     */
    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/' . $key);
    }

    /**
     * returns 'add' button html code
     *
     * @param string $container
     * @param string $template
     * @param string $title
     * @return string
     */
    protected function _getAddRowButtonHtml($container, $template, $title = 'Add')
    {
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

    /**
     * returns 'delete' button html code
     *
     * @param string $selector
     * @param string $title
     * @return array
     */
    protected function _getRemoveRowButtonHtml($selector = 'li', $title = 'Delete')
    {
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