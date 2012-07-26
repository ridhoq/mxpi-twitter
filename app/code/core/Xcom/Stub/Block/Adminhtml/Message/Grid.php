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
 * @package     Xcom_Stub
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Stub_Block_Adminhtml_Message_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('messageGrid');
        $this->setDefaultSort('message_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('xcom_stub/message')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_id', array(
          'header'    => $this->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'message_id',
        ));

        $this->addColumn('sender_topic_name', array(
          'header'    => $this->__('Topic'),
          'align'     =>'left',
          'index'     => 'sender_topic_name',
        ));

        $this->addColumn('sender_message_name', array(
          'header'    => $this->__('Message Name'),
          'align'     =>'left',
          'index'     => 'sender_message_name',
        ));

        $this->addColumn('description', array(
          'header'    => $this->__('Comment'),
          'align'     =>'left',
          'index'     => 'description',
        ));

        $this->addColumn('action', array(
            'header'    => $this->__('Action'),
            'width'     => '100px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'       => $this->__('Send Request'),
                    'url'           => array(
                        'base'      =>'*/*/sendrequest',
                    ),
                    'field'         => 'id',
                ),
                array(
                    'caption'       => $this->__('Send Response'),
                    'url'           => array(
                        'base'      =>'*/*/sendresponse',
                    ),
                    'field'         => 'id',
                )
            ),
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
