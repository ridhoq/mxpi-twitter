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
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Newsletter_Helper extends Mage_Selenium_TestCase
{
    /**
     * Subscribe to newsletter
     *
     * @param string $email
     */
    public function frontSubscribe($email)
    {
        $this->fillForm(array('sign_up_newsletter' => $email));
        $this->saveForm('subscribe');
    }

    /**
     * Perform a mass action with newsletter subscribers
     *
     * @param string $action Mass action value: 'unsubscribe'|'delete'
     * @param array $searchDataSet
     */
    public function massAction($action, $searchDataSet)
    {
        foreach ($searchDataSet as $searchData) {
            $this->searchAndChoose($searchData);
        }
        $this->addParameter('qtyOfRecords', count($searchDataSet));
        $this->fillForm(array('subscribers_massaction' => ucfirst(strtolower($action))));
        $this->clickButton('submit');
    }

    /**
     * Perform a mass action with newsletter subscribers
     *
     * @param string $status Status from data set to check, e.g. 'subscribed'|'unsubscribed'
     * @param array $searchData
     *
     * @return boolean. True if $searchData with $status status is found. False otherwise.
     */
    public function checkStatus($status, $searchData)
    {
        $searchData['filter_status'] = ucfirst(strtolower($status));
        $searchData = $this->arrayEmptyClear($searchData);
        return !is_null($this->search($searchData));
    }
}