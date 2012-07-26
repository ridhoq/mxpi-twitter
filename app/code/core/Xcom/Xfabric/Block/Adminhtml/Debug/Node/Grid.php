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

class Xcom_Xfabric_Block_Adminhtml_Debug_Node_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('xcom_xfabric_info');
        $this->setUseAjax(true);
        $this->setDefaultSort('node_id');
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
        return 'xcom_xfabric/debug_node_collection';
    }

    protected function _prepareCollection()
    {
        $debugId = $this->getRequest()->getParam('debug_id');

        if ($debugId) {
            $collection = Mage::getResourceModel($this->_getCollectionClass());
            $collection->addFieldToFilter('debug_id', array('eq' => $debugId));
            $this->setCollection($collection);
        }

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection() as $item) {
            $start = $item->getStartedMicrotime();
            $end   =  $item->getCompletedMicrotime();
            $item->setPeriod((float)$end - (float)$start);

            // Prepare Headers
            $headers = $item->getHeaders();
            if ($headers = @unserialize($headers)) {
                $headersReduced = array();
                if (is_array($headers)) {
                    foreach ($headers as $key => $value) {
                        if (preg_match('/[a-z]/i', $key)) {
                            $headersReduced[] = $key . ': ' . $value;
                        } else {
                            $headersReduced[] = $value;
                        }
                    }
                }
                $headers = implode("\n", $headersReduced);
                $item->setHeaders(nl2br($headers));
            } else {
                $item->setHeaders(nl2br($item->getHeaders()));
            }
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('node_id', array(
            'header'=> $this->__('Node ID'),
            'type'  => 'number',
            'width' => '80px',
            'index' => 'node_id',
        ));

        $this->addColumn('parent_id', array(
            'header'=> $this->__('Parent Node ID'),
            'type'  => 'number',
            'width' => '80px',
            'index' => 'parent_id',
        ));

        $this->addColumn('topic', array(
            'header'    => $this->__('Topic'),
            'index'     => 'topic',
        ));

        $this->addColumn('headers', array(
            'header'    => $this->__('Headers'),
            'index'     => 'headers',
            'renderer'  => 'xcom_xfabric/adminhtml_widget_grid_column_renderer_raw'
        ));

        $this->addColumn('body', array(
            'header'    => $this->__('Body'),
            'index'     => 'body',
        ));

        $this->addColumn('started_at', array(
            'header'=> $this->__('Started At'),
            'type'  => 'datetime',
            'index' => 'started_at',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
