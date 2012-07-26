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
 * @package     Xcom_Initializer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Initializer_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        parent::_construct();
    }

    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        $this->_getAllTopicsArray($collection);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _getAllTopicsArray(Varien_Data_Collection $collection)
    {
        $configTopics = Mage::helper('xcom_xfabric')
            ->getNodeByXpath('*/*[@initializer="prepopulate"]/name');

        /** @var $jobResource Xcom_Initializer_Model_Resource_Job */
        $jobResource = Mage::getResourceModel('xcom_initializer/job');

        $topicId = 0;
        foreach ($configTopics as $topic) {
            $jobCount = $jobResource->getJobCounts($topic);
            $statusText = $jobCount['total'] > 0 ? $this->__('Started') : $this->__('Not started');
            $statusText = ($jobCount['total'] > 0 && $jobCount['total'] == $jobCount['saved']) ? $this->__('Completed') : $statusText;
            $collection->addItem(new Varien_Object(array(
                'topic'      => $topic,
                'topic_id'   => ++$topicId,
                'total'      => $jobCount['total'],
                'inprocess'  => $jobCount['inprocess'],
                'sent'       => $jobCount['sent'],
                'received'   => $jobCount['received'],
                'saved'      => $jobCount['saved'],
                'wait_since' => $jobCount['wait_since'],
                'status'     => $statusText,
            )));
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('topic_id', array(
            'header'=> $this->__('Topic #'),
            'width' => '50',
            'type'  => 'text',
            'index' => 'topic_id',
            'sortable'  => false,
        ));

        $this->addColumn('topic', array(
            'header' => $this->__('Topic Name'),
            'index' => 'topic',
            'type'  => 'text',
            'sortable'  => false,
        ));

        $this->addColumn('progress', array(
            'header' => $this->__('Progress'),
            'type'  => 'text',
            'sortable'  => false,
            'renderer'  => 'xcom_initializer/renderer_column_progress',
            'width' => '300',
        ));
        $this->addColumn('wait_since', array(
            'header' => $this->__('In Process Since'),
            'index' => 'wait_since',
            'type'  => 'datetime',
            'sortable'  => false,
            'width' => '150',
        ));

        return parent::_prepareColumns();
    }

    public function getRowClass($item)
    {
        if (preg_match('/completed/i', $item->getStatus())) {
            return 'created';
        }
        return '';
    }
}
