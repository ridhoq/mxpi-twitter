<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rhoq
 * Date: 7/26/12
 * Time: 5:26 PM
 * To change this template use File | Settings | File Templates.
 */
class Xcom_Chronicle_Model_Message_HashTagSale_Create_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    protected function _construct()
    {
        $this->_topic               = 'com.x.hashtagsale.v1/HTSCart/MakeSaleActiveHTS';
        $this->_schemaRecordName    = 'MakeSaleActiveHTS';
        $this->_schemaVersion       = "1.0.2";

        parent::_construct();
    }

    public function process()
    {
        parent::process();
        $data = $this->getBody();
        try {
            $this->_processStartSale($data);
        }
        catch(Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _processStartSale($data)
    {
        $rule = Mage::getModel('catalogrule/rule');
        $productSku = $data['productSku'];
        $rule->setName('HTS_' . $productSku);
        $saleLength = $data['saleLength'];
        $rule->setFromDate(Mage::helper('core')->formatDate());
        $rule->setEndDate(Mage::helper('core')->formatDate(time() + (7 * 24 * 60 *60)));
        $rule->setSimpleAction('by_percent');
        $rule->setDiscountAmount($data['discountAmount']);
        $rule->setIsActive(1);
    }
}
