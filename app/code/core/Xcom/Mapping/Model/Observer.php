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
class Xcom_Mapping_Model_Observer
{
    /**
     * Event when taxonomy related data is deleted in
     * Xcom_Mapping_Adminhtml_Map_AttributeController::clearTaxonomyAction
     */
    public function createTaxonomyMessages()
    {
        /* @var $helper Xcom_Mapping_Helper_Data */
        $helper = Mage::helper('xcom_mapping');
        $locales = $helper->getSupportedLocales();
        $hardcodedTopics = $helper->getHardcodedTopics();

        foreach ($hardcodedTopics as $topic) {
            foreach ($locales as $locale) {
                Mage::getModel('xcom_initializer/job')
                    ->setTopic($topic)
                    ->setStatus(Xcom_Initializer_Model_Job::STATUS_PENDING)
                    ->setMessageParams(json_encode($locale))
                    ->setIssuer('xcom_mapping')
                    ->save();
            }
        }
    }
}
