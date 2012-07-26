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
 * UIMap factory class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Uimap_Factory
{
    /**
     * Array of allowed element names
     *
     * @var array
     */
    protected static $_allowedElementNames = array('buttons', 'messages', 'links', 'fields', 'dropdowns', 'multiselects',
                                                  'checkboxes', 'radiobuttons', 'required', 'pageelements');

    /**
     * Construct an Uimap_Factory
     */
    protected function __construct()
    {
    }

    /**
     * Creates a UIMap object
     *
     * @param string $elemKey
     * @param string|array $elemValue
     *
     * @return mixed
     */
    public static function createUimapElement($elemKey, &$elemValue)
    {
        $elements = null;

        switch ($elemKey) {
            case 'form':
                $elements = new Mage_Selenium_Uimap_Form($elemValue);
                break;
            case 'tabs':
                $elements = new Mage_Selenium_Uimap_TabsCollection();
                foreach ($elemValue as &$tabArrayValue) {
                    foreach ($tabArrayValue as $tabKey => &$tabValue) {
                        $elements[$tabKey] = new Mage_Selenium_Uimap_Tab($tabKey, $tabValue);
                    }
                }
                break;
            case 'fieldsets':
                $elements = new Mage_Selenium_Uimap_FieldsetsCollection();
                foreach ($elemValue as &$fieldsetArrayValue) {
                    foreach ($fieldsetArrayValue as $fieldsetKey => &$fieldsetValue) {
                        $elements[$fieldsetKey] =
                            new Mage_Selenium_Uimap_Fieldset($fieldsetKey, $fieldsetValue);
                    }
                }
                break;
            default:
                if (in_array($elemKey, self::$_allowedElementNames)) {
                    $elements = new Mage_Selenium_Uimap_ElementsCollection($elemKey,
                                    $elemValue);
                }
        }
        return $elements;
    }
}
