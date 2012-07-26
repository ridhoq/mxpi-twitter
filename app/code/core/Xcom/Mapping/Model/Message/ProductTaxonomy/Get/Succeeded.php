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

class Xcom_Mapping_Model_Message_ProductTaxonomy_Get_Succeeded
    extends Xcom_Xfabric_Model_Message_Response
{
    const CLASSES_LOADED_FLAG_PATH = 'xcom/mapping/product_classes/loaded';

    /**
     * init message
     */
    protected function _construct()
    {
        $this->_topic = 'productTaxonomy/getSucceeded';
        $this->_schemaRecordName = 'GetProductTaxonomySucceeded';
        parent::_construct();
    }

    /**
     * Process message body and store result in database
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_Get_Succeeded
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();

        $localeCode = 'en_US';
        if (!is_null($data['locale'])) {
            $localeCode = $data['locale']['language'] . '_' . $data['locale']['country'];
        }
        $this->setLocaleCode($localeCode);

        $this->_deleteProductClasses($data);

        foreach ($data['productTaxonomy']['productClasses'] as $productClass) {
            $this->saveProductClass($productClass);
        }

        $this->setLoadedFlagConfig();
        return $this;
    }

    /**
     * Clean product classes from database which are not present in given $data array.
     *
     * @param array $data
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_Get_Succeeded
     */
    protected function _deleteProductClasses(array $data)
    {
        $productClassIds = $this->_collectProductClassIds($data);

        $oldIds = Mage::getSingleton('xcom_mapping/product_class')->getCollection()
            ->addFieldToFilter('product_class_id', array('nin' => $productClassIds))
            ->setLocaleCode($this->getLocaleCode())
            ->getAllIds();

        Mage::getSingleton('xcom_mapping/product_class')->deleteByIds($oldIds);
        return $this;
    }

    /**
     * Collect product class ids from response.
     *
     * @param array $data
     * @return array
     */
    protected function _collectProductClassIds(array $data)
    {
        $result = array();
        foreach ($data['productTaxonomy']['productClasses'] as $productClass) {
           $result[] = $productClass['id'];
            if ($productClass['subClasses'] !== null) {
                foreach($productClass['subClasses'] as $productSubClass) {
                    $result[] = $productSubClass['id'];
                }
            }
        }
        return $result;
    }

    public function setLoadedFlagConfig()
    {
        Mage::getConfig()->saveConfig(self::CLASSES_LOADED_FLAG_PATH, 1);
        return $this;
    }

    /**
     * Save product class data to storage
     *
     * @param array $productClass
     * @param $parent
     * @return mixed
     */
    public function saveProductClass(array $productClass, $parent = null)
    {
        $classEntity = Mage::getModel('xcom_mapping/product_class');

        $data = array(
            'product_class_id'    => $productClass['id'],
            'name'  => $productClass['name'],
            'parent_product_class_id' => $parent,
            'locale_code' => $this->getLocaleCode()
        );

        $classEntity->setData($data);
        $classEntity->save();

        if ($productClass['subClasses'] !== null) {
            foreach ($productClass['subClasses'] as $subClass) {
                $this->saveProductClass($subClass, $classEntity->getId());
            }
        }
        return $classEntity->getId();
    }
}
