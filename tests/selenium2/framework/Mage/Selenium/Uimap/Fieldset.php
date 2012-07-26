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
 * Fieldset UIMap class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Uimap_Fieldset extends Mage_Selenium_Uimap_Abstract
{
    /**
     * @var string
     */
    protected $_fieldsetId = '';

    /**
     * Construct a Uimap_Fieldset
     *
     * @param string $fieldsetId Fieldset ID
     * @param array $fieldsetContainer Array of data, which contains in specific fieldset
     */
    public function  __construct($fieldsetId, array &$fieldsetContainer)
    {
        $this->_fieldsetId = $fieldsetId;
        $this->_xPath = isset($fieldsetContainer['xpath'])
            ? $fieldsetContainer['xpath']
            : '';
        $this->_parseContainerArray($fieldsetContainer);
        if ($this->_xPath != '' && isset($this->_elements)) {
            $parent = $this->_xPath;
            foreach ($this->_elements as $elementData) {
                foreach ($elementData as $elementName => $elementXpath) {
                    if (preg_match('|^' . preg_quote($parent) . '|', $elementXpath)) {
                        continue;
                    }
                    $elementXpath = str_ireplace('css=', ' ', $elementXpath);
                    $elementData[$elementName] = $parent . $elementXpath;
                }
            }
        }
    }
}
