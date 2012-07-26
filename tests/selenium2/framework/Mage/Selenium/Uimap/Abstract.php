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
 * Abstract UIMap class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Uimap_Abstract
{
    /**
     * Element XPath
     * @var string
     */
    protected $_xPath = '';

    /**
     * UIMap elements
     * @var array
     */
    protected $_elements = array();

    /**
     * UIMap elements cache for recursive operations
     * @var array
     */
    protected $_elementsCache = array();

    /**
     * Parameters helper instance
     *
     * @var Mage_Selenium_Helper_Params
     */
    protected $_params = null;

    /**
     * Retrieve XPath of the current element
     *
     * @param Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return string|null
     */
    public function getXPath($paramsDecorator = null)
    {
        return $this->_applyParamsToString($this->_xPath, $paramsDecorator);
    }

    /**
     * Retrieve all elements on current level
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     * Parser from native UIMap array to UIMap class hierarchy
     *
     * @param array &$container Array with UIMap
     *
     * @return Mage_Selenium_Uimap_Abstract
     */
    protected function _parseContainerArray(array $container)
    {
        foreach ($container as $formElemKey => &$formElemValue) {
            if (empty($formElemValue)) {
                continue;
            }
            $newElement = Mage_Selenium_Uimap_Factory::createUimapElement($formElemKey, $formElemValue);
            if (empty($newElement)) {
                continue;
            }
            if (!isset($this->_elements[$formElemKey])) {
                $this->_elements[$formElemKey] = $newElement;
            } else {
                if ($this->_elements[$formElemKey] instanceof ArrayObject) {
                    $this->_elements[$formElemKey]->append($newElement);
                }
            }
        }

        return $this;
    }

    /**
     * Assign parameters decorator to UIMap tree from any level
     *
     * @param Mage_Selenium_Helper_Params $params Parameters decorator
     *
     * @return Mage_Selenium_Uimap_Abstract
     */
    public function assignParams($params)
    {
        $this->_params = $params;
        $this->_elementsCache = null;

        foreach ($this->_elements as $elem) {
            if ($elem instanceof Mage_Selenium_Uimap_Abstract
                    || $elem instanceof Mage_Selenium_Uimap_ElementsCollection
            ) {
                $elem->assignParams($params);
            } else if ($elem instanceof ArrayObject) {
                foreach ($elem as $arrElem) {
                    if ($arrElem instanceof Mage_Selenium_Uimap_Abstract) {
                        $arrElem->assignParams($params);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get parameters decorator
     *
     * @param Mage_Selenium_Helper_Params $paramsDecorator Parameters decorator instance (by default = null)
     *
     * @return Mage_Selenium_Helper_Params|null
     */
    protected function _getParams($paramsDecorator = null)
    {
        if ($paramsDecorator) {
            return $paramsDecorator;
        }

        return $this->_params;
    }

    /**
     * Apply parameters decorator to string
     *
     * @param string $text String to change
     * @param Mage_Selenium_Helper_Params|null $paramsDecorator Parameters decorator instance
     *
     * @return string
     */
    protected function _applyParamsToString($text, $paramsDecorator = null)
    {
        $paramsDecorator = $this->_getParams($paramsDecorator);
        if ($paramsDecorator) {
            return $paramsDecorator->replaceParameters($text);
        }

        return $text;
    }

    /**
     * Internal recursive function
     *
     * @param string $elementsCollectionName UIMap elements collection name
     * @param Mage_Selenium_Uimap_ElementsCollection|array $container UIMap container
     * @param array $cache Array with search results
     * @param Mage_Selenium_Helper_Params|null $paramsDecorator Parameters decorator instance
     *
     * @return array
     */
    protected function _getElementsRecursive($elementsCollectionName, &$container, &$cache, $paramsDecorator = null)
    {
        foreach ($container as $elKey => $elValue) {
            if ($elValue instanceof ArrayObject) {
                if (($elementsCollectionName == 'tabs'
                    && $elementsCollectionName == $elKey
                    && $elValue instanceof Mage_Selenium_Uimap_TabsCollection)
                    || ($elementsCollectionName == 'fieldsets'
                        && $elementsCollectionName == $elKey
                        && $elValue instanceof Mage_Selenium_Uimap_FieldsetsCollection)
                    || $elKey == $elementsCollectionName
                        && $elValue instanceof Mage_Selenium_Uimap_ElementsCollection) {
                    $cache = array_merge($cache, $elValue->getArrayCopy());
                } else {
                    $this->_getElementsRecursive($elementsCollectionName, $elValue, $cache, $paramsDecorator);
                }
            } elseif ($elValue instanceof Mage_Selenium_Uimap_Abstract) {
                $containerUimap = $elValue->getElements();
                $this->_getElementsRecursive($elementsCollectionName,
                                             $containerUimap, $cache, $paramsDecorator);
            }
        }

        return $cache;
    }

    /**
     * Search UIMap element by name on any level from current and deeper
     * This method uses a cache to save search results
     *
     * @param string $elementsCollectionName UIMap Elements collection name
     * @param Mage_Selenium_Helper_Params $paramsDecorator Parameters decorator instance (by default = null)
     *
     * @return array
     */
    public function getAllElements($elementsCollectionName, $paramsDecorator = null)
    {
        if (empty($this->_elementsCache[$elementsCollectionName])) {
            $cache = array();
            $this->_elementsCache[$elementsCollectionName] = new Mage_Selenium_Uimap_ElementsCollection(
                            $elementsCollectionName,
                            $this->_getElementsRecursive($elementsCollectionName, $this->_elements, $cache,
                                    $paramsDecorator),
                            $paramsDecorator);
        }

        return $this->_elementsCache[$elementsCollectionName];
    }

    /**
     * Magic method to call accessor methods<br>
     * Format:
     * <ul>
     * <li>- call "get"+"UIMap properties collection name"() to get UIMap elements collection by name from current level
     * <li>- call "getAll"+"UIMap properties collection name"() to get UIMap elements collection by name on any level
     * from current and deeper
     * <li>- call "find"+"UIMap element type"(element name) to get UIMap element by name on any level from current
     * and deeper
     * </ul>
     *
     * @param string $name Method's name to call 'get' | 'getAll' | 'find'
     * @param string $arguments Argument to calling method 'UIMap properties collection name' | 'UIMap element type'
     *
     * @throws Exception
     *
     * @return Mage_Selenium_Uimap_ElementsCollection|Mage_Selenium_Uimap_TabsCollection|string|null
     */
    public function __call($name, $arguments)
    {
        $returnValue = null;

        if (preg_match('|^getAll(\w+)$|', $name)) {
            $elementName = strtolower(substr($name, 6));
            if (!empty($elementName)) {
                $returnValue = $this->getAllElements($elementName,
                        $this->_getParams(isset($arguments[1]) ? $arguments[1] : null));
            }
        } elseif (preg_match('|^get(\w+)$|', $name)) {
            $elementName = strtolower(substr($name, 3));
            if (!empty($elementName) && isset($this->_elements[$elementName])) {
                $returnValue = $this->_elements[$elementName];
            }
        } elseif (preg_match('|^find(\w+)$|', $name)) {
            $elementName = strtolower(substr($name, 4));
            if ($elementName === 'checkbox') {
                $elementName .= 'es';
            } else {
                $elementName .= 's';
            }
            if (!empty($elementName) && !empty($arguments)) {
                $elementsColl = $this->getAllElements($elementName);
                $returnValue = $elementsColl->get($arguments[0],
                        $this->_getParams(isset($arguments[1]) ? $arguments[1] : null));
            }
        }

        if(!isset($elementName)) {
            throw new Exception('Element name is undefined.');
        }

        if (!empty($elementName) && !$returnValue) {
            throw new Exception('Can\'t find element(s) "' . $elementName . '"');
        }

        return $returnValue;
    }
}