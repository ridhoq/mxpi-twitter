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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Model_ObserverTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Observer
     */
    protected $_object     = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Listing_Model_Observer();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Listing_Model_Observer', $this->_object);
    }

    public function testSendListingSearchRequest()
    {
        $resource = $this->mockResource('xcom_listing/listing_collection', array('load'));
        $resource->expects($this->once())
            ->method('load')
            ->will($this->returnValue(array(new Varien_Object())));

        $products = array(
            new Varien_Object(array(
                'product_id' => 'product_1',
                'entity_id' => 'product_1',
                'sku' => 'product_1',
            )),
            new Varien_Object(array(
                'product_id' => 'product_2',
                'entity_id' => 'product_2',
                'sku' => 'product_2',
            ))
        );

        $productResourceMock = $this->mockResource('xcom_listing/channel_product_collection',
            array('addFieldToSelect', 'addCatalogProducts'));
        $productResourceMock->expects($this->any())
            ->method('addFieldToSelect')
            ->will($this->returnValue($productResourceMock));
        $productResourceMock->expects($this->any())
            ->method('addCatalogProducts')
            ->will($this->returnValue($products));

        $objectMock = $this->getMock(get_class($this->_object), array('_sendListingSearchRequest'));
        $objectMock->sendListingSearchRequest(new Varien_Object());
    }

    public function categoryProductTypeProvider()
    {
        return array(
            array(null, null, null, null),
            array(rand(1, 99999), rand(), rand(), null),
            array(rand(1, 99999), rand(), null, null),
            array(rand(1, 99999), rand(), null, rand(1, 99999))
        );
    }

    /**
     * @param $relationProductTypeId
     * @param $mappingProductTypeId
     * @param $categoryIds
     * @param $productTypeId
     *
     * @dataProvider categoryProductTypeProvider
     */
    public function testSendRecommendedCategorySearch($relationProductTypeId, $mappingProductTypeId,
                                                      $categoryIds, $productTypeId)
    {
        $observer   = new Varien_Object(array(
            'relation_product_type_id'  => $relationProductTypeId,
            'mapping_product_type_id'   => $mappingProductTypeId
        ));
        $mockCategory   = $this->mockResource('xcom_listing/category', array('getRecommendedCategoryIds'));
        if ($relationProductTypeId) {
            $mockCategory->expects($this->once())
                ->method('getRecommendedCategoryIds')
                ->with($this->equalTo($mappingProductTypeId))
                ->will($this->returnValue($categoryIds));

            $mockProductType   = $this->mockModel('xcom_mapping/product_type', array('load', 'getProductTypeId'));
            if (empty($categoryIds)) {
                $mockProductType->expects($this->once())
                    ->method('load')
                    ->with($this->equalTo($mappingProductTypeId))
                    ->will($this->returnValue($mockProductType));
                $mockProductType->expects($this->exactly($productTypeId ? 2 : 1))
                    ->method('getProductTypeId')
                    ->will($this->returnValue($productTypeId));
                $this->_mockSendCategoryForProductTypeMessage($productTypeId);
            } else {
                $mockProductType->expects($this->never())
                    ->method('load');
            }
        } else {
            $mockCategory->expects($this->never())
                ->method('getRecommendedCategoryIds');
        }

        $this->_object->sendRecommendedCategorySearch($observer);
    }

    protected function _mockSendCategoryForProductTypeMessage($productTypeId)
    {
        $environments = array(
            array('site_code' => 'US', 'environment' => 'sandbox'),
            array('site_code' => 'US', 'environment' => 'production'),
            array('site_code' => 'UK', 'environment' => 'sandbox'),
            array('site_code' => 'UK', 'environment' => 'production')
        );

        $mockEnvironment    = $this->mockResource('xcom_mmp/environment', array('getAllEnvironments'));
        if ($productTypeId) {
            $mockEnvironment->expects($this->once())
                ->method('getAllEnvironments')
                ->will($this->returnValue($environments));

            $mockHelper = $this->mockHelper('xcom_xfabric', array('send'));

            foreach ($environments as $num => $record) {
                $options = array(
                    'product_type_id' => $productTypeId,
                    'siteCode'        => $record['site_code'],
                    'environmentName' => $record['environment']
                );
                $this->_mockSend($mockHelper, $options, $num);
            }
        } else {
            $mockEnvironment->expects($this->never())
                ->method('getAllEnvironments');
        }
    }

    protected function _mockSend($mockHelper, $options, $at)
    {
        $mockHelper->expects($this->at($at))
            ->method('send')
            ->with($this->equalTo('marketplace/categoryForProductType/search'), $this->equalTo($options))
            ->will($this->returnValue(null));
    }
}
