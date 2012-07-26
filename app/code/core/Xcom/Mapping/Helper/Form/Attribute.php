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

class Xcom_Mapping_Helper_Form_Attribute extends Mage_Catalog_Helper_Data
{
    /**
     * get select
     *
     * @param $options
     * @return string
     */
    public function getSelect($options)
    {
        $select = Mage::app()->getLayout()->createBlock('xcom_mapping/form_element_select')
            ->setData($options);
        return $select->getElementHtml();
    }



    public function getSelectHtml($options, $form)
    {
        $default = array(
            'size'      => 10,
            'required'  => true,
            'class'     => 'required-entry select validate-select',
            'no_span'   => false
        );
        $options = array_merge($default, $options);
        $block = new Xcom_Mapping_Block_Form_Element_Select($options);
        $block->setForm($form);
        return $block->toHtml();
    }
}
