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
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tab UIMap class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Uimap_Tab extends Mage_Selenium_Uimap_Abstract
{
    /**
     * Tab ID
     *
     * @var string
     */
    protected $_tabId = '';

    /**
     * Construct a Uimap_Tab
     *
     * @param string $tabId Tab's ID
     * @param array $tabContainer Array of data that contains the specific tab
     */
    public function  __construct($tabId, array &$tabContainer)
    {
        $this->_tabId = $tabId;
        $this->_xPath = isset($tabContainer['xpath'])
                            ? $tabContainer['xpath']
                            : '';

        $this->_parseContainerArray($tabContainer);
    }

    /**
     * Get page ID
     *
     * @return string
     */
    public function getTabId()
    {
        return $this->_tabId;
    }

    /**
     * Get Fieldset structure by ID
     *
     * @param string $id Fieldset ID
     *
     * @return Mage_Selenium_Uimap_Fieldset|null
     */
    public function getFieldset($id)
    {
        return isset($this->_elements['fieldsets'])
                ? $this->_elements['fieldsets']->getFieldset($id)
                : null;
    }
}