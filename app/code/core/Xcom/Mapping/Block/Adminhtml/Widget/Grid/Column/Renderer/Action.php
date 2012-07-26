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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Xcom
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Mapping_Block_Adminhtml_Widget_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '&nbsp';
        }

        $isMapped = $this->_getIsMapped($row);

        foreach ($actions as $key => $action) {
            if (is_array($action)
                && isset($action['is_mapped'])
                && $action['is_mapped'] != $isMapped) {
                unset($actions[$key]);
            }
        }

        if (sizeof($actions) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action){
                if ( !is_array($action) ) {
                    continue;
                }
                if (isset($action['attributes']) && is_array($action['attributes'])) {
                    foreach($action['attributes'] as $attribute) {
                        if ($attrValue = $this->_prepareAdditionalParams($attribute, $row)) {
                            $action['url']['params'][$attribute] = $attrValue;
                        }
                    }
                }
                // Modify URL base for custom attributes.
                if (is_null($row->getMappingAttributeId())) {
                    $urlBase = explode('/', $action['url']['base']);
                    if ($urlBase[count($urlBase)-1]=='value') {
                        array_pop($urlBase);
                        array_push($urlBase, 'valuecustom');
                        $action['url']['base'] = implode('/', $urlBase);
                    }
                    unset($urlBase);
                }
                return $this->_toLinkHtml($action, $row);
            }
        }

        $out = '<select class="action-select" onchange="varienGridAction.execute(this);">'
             . '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action){
            $i++;
            if ( is_array($action) ) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }

    /**
     * Get is mapped flag.
     *
     * @param Varien_Object $row
     * @return boolean
     */
    protected function _getIsMapped(Varien_Object $row)
    {
        if ($getter = $this->getColumn()->getIsMappedGetter()) {
            if (is_string($getter)) {
                $result = $row->$getter();
            } elseif (is_callable($getter)) {
                $result = call_user_func($getter, $row);
            }
        } else {
            $result = $row->getData($this->getColumn()->getIndex());
        }

        if (!empty($result)) {
            return true;
        }
        return false;
    }

    /**
     * Returns additional value.
     *
     * @param mixed $attribute
     * @param Varien_Object $row
     * @return mixed
     */
    protected function _prepareAdditionalParams($attribute, $row)
    {
        if ($row->getData($attribute)) {
            return $row->getData($attribute);
        }
        return '';
    }

}
