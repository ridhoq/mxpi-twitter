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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mmp_Model_Resource_Account_CollectionTest extends Xcom_Collection_TestCase
{
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mmp_Model_Resource_Account_Collection();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testAddDateValidationFilter()
    {
        $this->_object->addDateValidationFilter();

        $where = $this->_retrieveWhere($this->_object->getSelect());
        $expectedWhere = "((CASE WHEN DATEDIFF(validated_at, NOW()) >= 0" .
            " THEN 1 WHEN validated_at=0 THEN 1 ELSE 0 END) = '1')";

        $actualWhere = array_values(array_intersect($where, array($expectedWhere)));
        $this->assertEquals(array($expectedWhere), $actualWhere, $expectedWhere);
    }

    protected function _retrieveWhere($select)
    {
        $where = $select->getPart(Zend_Db_Select::WHERE);
        return $where;
    }
}
