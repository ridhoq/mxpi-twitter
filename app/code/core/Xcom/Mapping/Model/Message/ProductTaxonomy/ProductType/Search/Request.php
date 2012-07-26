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

class Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Search_Request
    extends Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_topic = 'productTaxonomy/productType/search';
        $this->_schemaRecordName = 'SearchProductType';
        $this->_isOnBehalfOfTenant = false;
    }

    /**
     * Prepare data for request.
     * Data options are used:
     *  - array product_class_id
     *  - string language
     *  - string country
     *  - array channel_codes (channelCode1, channelCode2)
     *
     * @param null|Varien_Object $data
     * @return Xcom_Xfabric_Model_Message_Request
     */
    protected function _prepareData(Varien_Object $data = null)
    {
        $messageData = array(
            'criteria' => array(
                'productClassId' => $data->getData('product_class_id')
            ),
            'locale'   => $this->_getLocaleRecord($data),
            'filter'   => $this->_getFilterRecord($data),
        );
        $this->setMessageData($messageData);
        return parent::_prepareData($data);
    }
}
