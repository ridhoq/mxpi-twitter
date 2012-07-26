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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml grid item renderer
 *
 * @category   Xcom
 * @package    Xcom_Xfabric
 */

class Xcom_Xfabric_Block_Adminhtml_Widget_Grid_Column_Renderer_Text extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
     /**
     * Renders grid column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function _getValue(Varien_Object $row)
    {
        $defaultValue = $this->getColumn()->getDefault();
        $data = parent::_getValue($row);
        $data = is_null($data) ? $defaultValue : unserialize($data);
        if (is_string($data)) {
            return htmlspecialchars($data);
        }
        $result = '';
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $val = $this->getValueToString($val);
                $result .= $key . ": " . $val . "\n";
            }
        }
        return nl2br($result);
    }

    /**
     * Render object to string.
     * 
     * @param string $object
     */
    private function getObjectToString($object = null)
    {
        if (is_null($object) || !is_object($object)) {
            return '';
        }

        $objectName = get_class($object);
        $objectVars = method_exists($object, 'hasData') ? $object->getData() : array();

        $result = '';
        if (is_array($objectVars) && count($objectVars)) {
            $result = '<ul>';
            foreach ($objectVars as $key => $val) {
                $val = $this->getValueToString($val);
                $result .= '<li>' . $key . ": " . $val . '</li>';
            }
            $result .= "</ul>";
        }
        return $result;
    }

    /**
     * Render array to string.
     * 
     * @param string $object
     */
    private function getArrayToString($object = null)
    {
        if (is_null($object) || !is_array($object)) {
            return '';
        }
        $result = '';
        if (count($object)) {
            $result = '<ul>';
            foreach ($object as $key => $val) {
                $val = $this->getValueToString($val);
                $result .= '<li>' . $key . ": " . $val . '</li>';
            }
            $result .= "</ul>";
        }
        return $result;
    }

    /**
     * Render mixed to string.
     * 
     * @param string $object
     */
    private function getValueToString($object = null)
    {
        if (is_null($object)) {
            return '';
        }
        if (is_object($object)) {
            return $this->getObjectToString($object);
        }
        if (is_array($object)) {
            return $this->getArrayToString($object);
        }
        return htmlspecialchars((string)$object);
    }
}
