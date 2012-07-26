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

class Xcom_Chronicle_Model_Message_Product extends Varien_Object
{
    protected $_locale;
    protected $_currency;
    protected $_product;

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function __construct(Mage_Catalog_Model_Product $product)
    {

        // Get the locale of this product.  Should probably do this for all stores
        $locale = $product->getStore()->getConfig('general/locale/code');
        $this->_product = $product;

        $this->_locale = preg_split('/_/', $locale);
        $this->_currency = Mage::app()->getBaseCurrencyCode();
        $this->setData($this->_createProduct($product));
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _createProduct(Mage_Catalog_Model_Product $product)
    {
        $brand = null;
        if ($product->hasData('brand')) {
            $brand = $product->getAttributeText('brand');
        }
        $manufacturer = null;
        if ($product->hasData('manufacturer')) {
            $manufacturer = $product->getAttributeText('manufacturer');
        }


        $data = array(
            'id'                => $product->getEntityId(),
            'productTypeId'     => $this->_getProductTypeId($product),
            'name'              => array($this->_createLocalizedValue($product->getName())),
            'shortDescription'  => array($this->_createLocalizedValue($product->getShortDescription())),
            'description'       => array($this->_createLocalizedValue($product->getDescription())),
            'GTIN'              => $product->hasData('gtin') ?
                $product->getAttributeText('gtin') : null,
            'brand'             => !empty($brand) ?
                array($this->_createLocalizedValue($brand)) : null,
            'manufacturer'      => !empty($manufacturer) ?
                array($this->_createLocalizedValue($manufacturer)) : null,
            'MPN'               => $product->hasData('mpn') ?
                $product->getAttributeText('mpn') : null,
            'MSRP'              => $this->_getMsrp($product),
            'MAP'               => $product->hasData('map') ?
                $this->_getCurrencyAmount($product->getAttributeText('map')) : null,
            'images'            => $this->_createImageUrls($product),
            'attributes'        => $this->_createAttributes($product),
            'variationFactors'  => $this->_createVariationFactors($product),
            'skuList'           => $this->_createSkus($product)
        );

        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getMsrp($product)
    {
        $helper = Mage::helper('catalog');
        if (method_exists($helper, 'canApplyMsrp')) {
            if ($helper->canApplyMsrp($product)) {
                 if ($product->getAttributeText('msrp')) {
                    return $this->_getCurrencyAmount($product->getAttributeText('msrp'));
                }
            }
        } else if ($product->getData('msrp')) {
            return $this->_getCurrencyAmount($product->getData('msrp'));
        }

        return null;
    }

    /**
     * @param $amount
     * @return array
     */
    protected function _getCurrencyAmount($amount)
    {
        return array(
            'amount'    => $amount,
            'code'      => $this->_currency,
        );
    }

    /**
     * @param Varien_Object $product
     * @return null
     */
    protected function _getProductTypeId(Varien_Object $product)
    {
        $productTypeId = Mage::getResourceModel('xcom_mapping/product_type')
            ->getProductTypeId($product->getAttributeSetId());
        return $productTypeId ? $productTypeId : null;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _createAttributes(Mage_Catalog_Model_Product $product, $attributes = null)
    {
        $result = array();

        $mappedAttributeCodes = array();
        $codeToName= array();
        $mappingData = Mage::getModel('xcom_mapping/attribute')
            ->getSelectAttributesMapping($product->getAttributeSetId());
        foreach ($mappingData as $mappedData) {
           //Mage::log("mapped data: " . print_r($mappedData, true), null, 'debug.log', true);
            $mappedAttributeCodes[] = $mappedData['attribute_code'];
            $codeToName[$mappedData['attribute_code']] = $mappedData['name'];
        }
        //Mage::log("mappedAttributeCodes: " . print_r($mappedAttributeCodes, true), null, 'debug.log', true);


        /** @var $mapper Xcom_Mapping_Model_Mapper */
        $mappingOptions = Mage::getSingleton('xcom_mapping/mapper')
            ->getMappingOptions($product);

        foreach ($mappingOptions as $key => $value) {
            if(in_array($key, $mappedAttributeCodes)) {
//                Mage::log("mapping options: " . print_r($mappingOptions, true), null, 'debug.log', true);
                $value = array(
                    'attributeId'       => $key,
                    //for now hard code it to ProductTypeString, need to figure out about StringEnumerationAttributeValue
                    // or BooleanAttributeValue
                    'attributeValue'    => $this->_createProductTypeAttributeValue($key, $codeToName[$key], $value, 'string'),
                );
                $result[] = $value;
            }
        }


        if(null === $attributes) {
            $attributes =  $product->getAttributes();
        }


        $attributeType = null;
        $attributeValue = null;
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        foreach ($attributes as $attribute) {
//            if($attribute->getIsUserDefined()) {
//                Mage::log("attribute in foreach: " . print_r($attribute, true), null, 'debug.log', true);
//            }
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined() && $attribute->getFrontendInput() == 'select') {
                $attributeValue = $product->getAttributeText($attributeCode);
                $attributeType = 'string';
            }
            else {
                $attributeValue = $product->getData($attributeCode);
                $attributeType = $attribute->getFrontendInput() == 'boolean' ? 'boolean' : 'string';
            }

            if ($attribute->getIsUserDefined() && !empty($attributeValue) &&
                !in_array($attributeCode, $mappedAttributeCodes)) {
                $value = array(
                    'attributeId'       => $attribute->getName(),
                    'attributeValue'    => $this->_createCustomAttributeValue(
                        $attribute->getFrontendLabel(), $attributeValue, $attributeType),
                );
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _createVariationFactors($product)
    {
        $factors = array();
        if($product->isConfigurable()) {
            /** @var $type Mage_Catalog_Model_Product_Type_Configurable */
            $type = $product->getTypeInstance();

            foreach ($type->getConfigurableAttributesAsArray() as $value) {
                $factors[] = $value['attribute_code'];
            }
        }

        return $factors;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _createSkus(Mage_Catalog_Model_Product $product)
    {
        $base_skus = array();
        if($product->isConfigurable()) {
            /** @var $type Mage_Catalog_Model_Product_Type_Configurable */
            $type = $product->getTypeInstance();

            $attributeCodes = array();
            foreach ($type->getConfigurableAttributesAsArray() as $value) {
//                Mage::log('attributes configurable: ' . print_r($value, true), null, 'debug.log', true);
//                Mage::log('attribute_code: ' . $value['attribute_code'], null, 'debug.log', true);
                $attributeCodes[] = array('code'  => $value['attribute_code'],
                    'label' => $value['label'],
                    'id'    => $value['attribute_id']);
                foreach ($value['values'] as $val) {
//                    Mage::log('store_label: ' . $val['store_label'], null, 'debug.log', true);
                }
            }

            $groups = $type->getChildrenIds($product->getId());
//            Mage::log("groups: " . print_r($groups, true), null, 'debug.log', true);
            foreach ($groups as $group) {
                foreach ($group as $childId) {
                    /** @var $childProduct Mage_Catalog_Model_Product */
                    $childProduct = Mage::getModel('catalog/product')->load($childId);
                    $attributes = array();
                    foreach ($attributeCodes as $array) {
//                        $attributes[$array['code']] = array('value' => $childProduct->getAttributeText($array['code']),
//                            'name'  => $array['label']);

                        $attributes[] = $childProduct->getTypeInstance(true)->getAttributeById($array['id'], $childProduct);
                    }
                    $attributes = $this->_createAttributes($childProduct, $attributes);

                    $base_skus[] = array( 'sku' => $childProduct->getSku(),
                        'attributes' => $attributes,
                    );

                }
            }


        } else {
            $base_skus[] = array('sku' => $product->getSku());
        }


        $stack = array($base_skus);

//        Mage::log(print_r($stack, true), null, 'debug.log', true);
        $skus = $this->_findAllSkus($stack);
//        Mage::log(print_r($skus, true), null, 'debug.log', true);

        // TODO: Need to test this case again
//        $sku = $product->getSku();
//        if(!isset($sku)) {
//            return array();
//        }

        $data = array();
        foreach ($skus as $node) {
            $sku = $node['sku'];
            $attributes = $node['attributes'];
            $data[] = $this->_createSku($sku, $attributes);
        }

        return $data;
    }

    private function _createStringAttribute($code, $name, $value)
    {
        return array(
            'attributeId'       => $code,
            'attributeValue'    => $this->_createCustomAttributeValue(
                $name, $value, 'string'),
        );
    }

    protected function _createSku($sku, $attributes = null)
    {
//        Mage::log('SKU: ' . $sku . '  Attributes: ' . print_r($attributes, true), null, 'debug.log', true);
        $variations = null;
        if(isset($attributes)) {
            $variations = array();
            foreach ($attributes as $array) {
                $variations = array_merge($variations, $array);
            }
        }
        $variations = empty($variations) ? null : array($variations);
        return array(
            'sku'                       => $sku,
            'productId'                 => $this->_product->getId(),
            'MSRP'                      => null,
            'MAP'                       => null,
            'variationAttributeValues'  => $variations,
            'images'                    => null
        );
    }

    private function _findAllSkus($stack, $skus = array(), $prefix = '', $attributes = array())
    {
//        Mage::log("stack: " . print_r($stack, true) . " skus: " . print_r($skus, true) . "  prefix: {$prefix}", null, 'debug.log', true);
        $parts = array_shift($stack);
        if(null === $parts) {
            $skus[] = array('sku' => $prefix, 'attributes' => $attributes);
            return $skus;
        }
        foreach ($parts as $node) {
            $skuSegment = $node['sku'];
            $middle = (empty($prefix) || empty($skuSegment)) ? '' : '-';
            $curPrefix = $prefix . $middle . $skuSegment;
            if($node != null && array_key_exists('attributes', $node) && $node['attributes'] != null) {
                $curAttributes = array_merge($attributes, $node['attributes']);
            } else {
                $curAttributes = $attributes;
            }
            $skus = $this->_findAllSkus($stack, $skus, $curPrefix, $curAttributes);
        }
        return $skus;
    }

    /**
     * Returns data about images associated with product
     * @param Mage_Catalog_Model_Product $product
     * @return array|null
     */
    protected function _createImageUrls(Mage_Catalog_Model_Product $product)
    {
        $result = array();
        //get media images associated with product
        $images = $product->getMediaGalleryImages();

        if (!empty($images)) {
            foreach ($images as $image ) {
                $isDeleted = $image->getRemoved();
                if (!$isDeleted) {
                    $label = $image->getLabel();
                    $data = array(
                        'url'     => $this->_getNotSecureImageUrl($image->getUrl()),
                        'height'  => null,
                        'width'   => null,
                        'label'   => $label ? $this->_createLocalizedValue($label) : null,
                        'altText' => null,
                        'tags'    => null,
                    );
                    $result[] = $data;
                }
            }
        }


        $image = $product->getData('image');
        if (!empty($image) && $image !='no_selection') {
            $imageUrl = (string)Mage::helper('catalog/image')->init($product, 'image');
            $label = $product->getData('image_label');
            $data = array(
                'url'     => $this->_getNotSecureImageUrl($imageUrl),
                'height'  => null,
                'width'   => null,
                'label'   => $label ? $this->_createLocalizedValue($label) : null,
                'altText' => null,
                'tags'    => null,
            );

            $result[] = $data;
        }

        $image = $product->getData('small_image');
        if (!empty($image) && $image !='no_selection') {
            $imageUrl = (string)Mage::helper('catalog/image')->init($product, 'small_image');
            $label = $product->getData('small_image_label');
            $data = array(
                'url'     => $this->_getNotSecureImageUrl($imageUrl),
                'height'  => null,
                'width'   => null,
                'label'   => $label ? $this->_createLocalizedValue($label) : null,
                'altText' => null,
                'tags'    => null,
            );

            $result[] = $data;
        }

        $image = $product->getData('thumbnail');
        if (!empty($image) && $image !='no_selection') {
            $imageUrl = (string)Mage::helper('catalog/image')->init($product, 'thumbnail');
            $label = $product->getData('thumbnail_label');
            $data = array(
                'url'     => $this->_getNotSecureImageUrl($imageUrl),
                'height'  => null,
                'width'   => null,
                'label'   => $label ? $this->_createLocalizedValue($label) : null,
                'altText' => null,
                'tags'    => array('THUMBNAIL'),
            );

            $result[] = $data;
        }

        return count($result) ? $result : null;
    }

    /**
     * @param $imageUrl
     * @return mixed
     */
    protected function _getNotSecureImageUrl($imageUrl)
    {
        if (strpos($imageUrl, 'https://') !== false) {
            $imageUrl = str_replace('https://', 'http://', $imageUrl);
        }
        return $imageUrl;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    protected function _getMappedAttributes(Mage_Catalog_Model_Product $product)
    {
        $attributeSetId = $product->getAttributeSetId();
        $attributeModel = Mage::getModel('xcom_mapping/attribute');
        $mappedAttributes   = $attributeModel->getSelectAttributesMapping($attributeSetId);

        return $mappedAttributes;
    }

    protected function _createProductTypeAttributeValue($key, $name, $value, $type)
    {
        $result = array();
        switch ($type) {
            case 'string':
                $result['value'] = $this->_createProductTypeStringAttributeValue($key, $name, $value);
                break;
            case 'enumeration': break;
            case 'boolean': break;
        }

        return $result;
    }

    protected function _createCustomAttributeValue($key, $value, $type)
    {
        $result = array();
        switch ($type) {
            case 'measurement':
            case 'string':
                $result['value'] = $this->_createStringAttributeValue($key, $value);
                break;
            case 'boolean':
                $result['value'] = $this->_createBooleanAttributeValue($key, $value);
                break;
        }

        return $result;
    }

    protected function _createProductTypeStringAttributeValue($id, $name, $value)
    {
        $data = array(
            'valueId'        => $id,
            'attributeValue' => $this->_createStringAttributeValue($name, $value)
        );
        return $data;
    }

    protected function _createStringAttributeValue($name, $value)
    {
        $data = array(
            'attributeNameValue' => array($this->_createLocalizedNameValue($name, $value))
        );
        return $data;
    }

    protected function _createBooleanAttributeValue($name, $value)
    {
        $data = array(
            'value' => (bool)$value,
            'attributeName'    => array($this->_createLocalizedValue($name))
        );
        return $data;
    }

    /**
     * @param $name
     * @param $value
     * @return array|null
     */
    protected function _createLocalizedNameValue($name, $value)
    {
        $data = array(
            'locale'    => array(
                'language'  => $this->_locale[0],
                'country'   => $this->_locale[1],
                'variant'   => null
            ),
            'name'          => $name,
            'value'         => $value
        );

        return $data;
    }

    /**
     * @param $value
     * @return array|null
     */
    protected function _createLocalizedValue($value)
    {
        if (empty($value)) {
            return null;
        }

        $data = array(
            'locale'    => array(
                'language'  => $this->_locale[0],
                'country'   => $this->_locale[1],
                'variant'   => null
            ),
            'stringValue'   => $value
        );

        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _prepareProductOptions(Mage_Catalog_Model_Product $product)
    {
        $productData = array();
        return $productData;
    }
}
