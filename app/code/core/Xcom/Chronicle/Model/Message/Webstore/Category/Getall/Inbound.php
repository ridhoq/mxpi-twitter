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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Chronicle_Model_Message_Webstore_Category_Getall_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.webstore.v1/WebStoreMetadataProvision/GetAllCategory';
        $this->_schemaRecordName    = 'GetAllCategory';
        $this->_schemaVersion       = "1.0.0";

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Chronicle_Model_Message_Saleschannel_Sites_Inbound
     */
    public function process()
    {
        parent::process();

        $data = $this->getBody();
        if (!isset($data)) {
            $data = array();
        }

        if (!isset($data['webStoreId'])) {
            $data['webStoreId'] = '';
        }

        $webStoreId = $data['webStoreId'];

        try {
            if ($this->_validateSchema()) {
                $resultSet = $this->_processSearchCategories($webStoreId);

                if (!empty($resultSet['results'])) {
                    $response = $this->_generateSuccessMessage($webStoreId,  $resultSet['results']);
                    Mage::helper('xcom_xfabric')->send(
                        'com.x.webstore.v1/WebStoreMetadataProvision/GetAllCategorySucceeded',
                        $response
                    );
                } else {
                    $response = $this->_generateFailureMessage($webStoreId, $resultSet['error']);
                    Mage::helper('xcom_xfabric')->send(
                        'com.x.webstore.v1/WebStoreMetadataProvision/GetAllCategoryFailed',
                        $response
                    );
                }
            }
        }
        catch(Exception $ex) {
            Mage::logException($ex);
            $response = $this->_generateFailureMessage($webStoreId, $ex->getMessage());
            Mage::helper('xcom_xfabric')->send(
                'com.x.webstore.v1/WebStoreMetadataProvision/GetAllCategoryFailed',
                $response
            );
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function _processSearchCategories($webStoreId)
    {
        $parent     = Mage::app()->getStore($webStoreId)->getRootCategoryId();
        $category = Mage::getModel('catalog/category');
        $recursionLevel  = max(0, (int) Mage::app()->getStore($webStoreId)->getConfig('catalog/navigation/max_depth'));

        $categories = $category->getCategories($parent, $recursionLevel, false, true, true);

        $results = null;
        $error = null;

        if (!empty($categories)) {
            foreach ($categories as $cat) {
                if (in_array($webStoreId, $cat->getStoreIds())) {
                    $model = Mage::getModel("catalog/category")->setStoreId($webStoreId);
                    $model->getUrlInstance()->setStore($webStoreId);
                    $c = $model->load((int)$cat->getId());
                    $results[] = Mage::getModel('xcom_chronicle/message_webstore_category', $c)->toArray();
                }
            }
        } else {
            $error = "No categories available for given webStoreId.";
        }

        $resultSet = array(
            'results'  => $results,
            'error'    => $error,
        );

        return $resultSet;
    }

    /**
     * @param $webStoreId string
     * @param $destinationId string
     * @param array $categories
     * @return array
     */
    protected function _generateSuccessMessage($webStoreId, array $categories)
    {
        return array(
            'webStoreId' => $webStoreId,
            'categories' => $categories,
            'destination_id' => $this->getPublisherPseudonym(),
            'correlation_id' => $this->getCorrelationId(),
        );
    }

    /**
     * @param $webStoreId
     * @param $message
     * @return array
     */
    protected function _generateFailureMessage($webStoreId, $message)
    {
        return array(
            'webStoreId' => $webStoreId,
            'errors' => array(
                array(
                    'code' => '-1',
                    'message' => $message,
                    'parameters' => null
                )
            ),
            'destination_id' => $this->getPublisherPseudonym(),
            'correlation_id' => $this->getCorrelationId(),
        );
    }
}
