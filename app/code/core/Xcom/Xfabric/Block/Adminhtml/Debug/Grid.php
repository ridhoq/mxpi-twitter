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

class Xcom_Xfabric_Block_Adminhtml_Debug_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('xcom_xfabric_info');
        $this->setUseAjax(true);
        $this->setDefaultSort('debug_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'xcom_xfabric/debug_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection() as $debug) {
            $start = $debug->getStartedMicrotime();
            $end   =  $debug->getCompletedMicrotime();
            $debug->setPeriod((float)$end - (float)$start);
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('debug_id', array(
            'header'=> $this->__('Debug ID'),
            'type'  => 'text',
            'width' => '80px',
            'index' => 'debug_id',
        ));

        $this->addColumn('name', array(
            'header'=> $this->__('Name'),
            'type'  => 'text',
            'index' => 'name',
        ));

        $this->addColumn('started_at', array(
            'header'=> $this->__('Started At'),
            'type'  => 'datetime',
            'index' => 'started_at',
        ));

        $this->addColumn('completed_at', array(
            'header'=> $this->__('Completed At'),
            'type'  => 'datetime',
            'index' => 'completed_at',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/node', array('debug_id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowClass($item)
    {
        if (preg_match('/failed/i', $item->getName())) {
            return 'failed';
        }
        return '';
    }
}
