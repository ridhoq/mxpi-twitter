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
class Core_Mage_PriceRules_Helper extends Mage_Selenium_TestCase
{
    protected static $optionsNesting = 1;
    protected static $qtyOptionsNesting = 0;
    protected static $optionsQty = 0;

    /**
     * Create new Rule
     *
     * @param string|array $createRuleData
     */
    public function createRule($createRuleData)
    {
        $this->clickButton('add_new_rule');
        $this->fillTabs($createRuleData);
        $this->saveForm('save_rule');
    }

    /**
     * Filling tabs
     *
     * @param string|array $ruleData
     */
    public function fillTabs($ruleData)
    {
        if (is_string($ruleData)) {
            $ruleData = $this->loadData($ruleData);
        }
        $ruleData       = $this->arrayEmptyClear($ruleData);
        $ruleInfo       = (isset($ruleData['info'])) ? $ruleData['info'] : array();
        $ruleConditions = (isset($ruleData['conditions'])) ? $ruleData['conditions'] : array();
        $ruleActions    = (isset($ruleData['actions'])) ? $ruleData['actions'] : array();
        $ruleLabels     = (isset($ruleData['labels'])) ? $ruleData['labels'] : null;
        if (array_key_exists('websites', $ruleInfo) && !$this->controlIsPresent('multiselect', 'websites')) {
            unset($ruleInfo['websites']);
        }
        $this->fillForm($ruleInfo, 'rule_information');
        $this->fillConditionsTab($ruleConditions);
        $this->fillActionsTab($ruleActions);
        if ($ruleLabels) {
            $this->fillLabelsTab($ruleLabels);
        }
    }

    /**
     * Fill Conditions Tab
     *
     * @param array $conditionsData
     */
    public function fillConditionsTab(array $conditionsData)
    {
        $this->openTab('rule_conditions');
        $this->addConditions($conditionsData, 'rule_conditions');
    }

    /**
     * Fill Actions Tab
     *
     * @param array $actionsData
     */
    public function fillActionsTab(array $actionsData)
    {
        $conditionsData = array();
        if (array_key_exists('action_conditions', $actionsData)) {
            $conditionsData = $actionsData['action_conditions'];
            unset($actionsData['action_conditions']);
        }
        $this->fillForm($actionsData, 'rule_actions');
        $this->addConditions($conditionsData, 'rule_actions');
    }

    /**
     * Fill Labels Tab
     *
     * @param array $labelsData
     */
    public function fillLabelsTab(array $labelsData)
    {
        $this->openTab('rule_labels');
        $storViewlabels = array();
        if (array_key_exists('store_view_labels', $labelsData)) {
            $storViewlabels = $labelsData['store_view_labels'];
            unset($labelsData['store_view_labels']);
        }
        $this->fillForm($labelsData, 'rule_labels');
        foreach ($storViewlabels as $key => $value) {
            $this->addParameter('storeViewName', $key);
            $this->fillForm(array('store_view_rule_label' => $value), 'rule_labels');
        }
    }

    /**
     * Add rule conditions
     *
     * @param array $conditionsData
     * @param string $tabId
     */
    public function addConditions(array $conditionsData, $tabId = '')
    {
        $fillArray = array();
        $isNested = false;
        foreach ($conditionsData as $key => $value) {
            if (!is_array($value)) {
                if ($key == 'select_' . preg_replace('/(^rule_)|(s$)/', '', $tabId) . '_new_child') {
                    $isNested = true;
                }
                $fillArray[$key] = $value;
                unset($conditionsData[$key]);
            }
        }
        $returnOptionsNesting = self::$optionsNesting;
        $returnQtyOptionsNesting = self::$qtyOptionsNesting;
        $returnOptionsQty = self::$optionsQty;
        if ($fillArray) {
            $this->fillConditionFields($fillArray, $tabId, $isNested);
        }

        foreach ($conditionsData as $key => $value) {
            if (is_array($value)) {
                $this->addConditions($value, $tabId);
            }
        }
        self::$optionsNesting = $returnOptionsNesting;
        self::$qtyOptionsNesting = $returnQtyOptionsNesting;
        self::$optionsQty = $returnOptionsQty;
    }

    /**
     * Set conditions params
     *
     * @param string $type
     */
    public function setConditionsParams($type)
    {
        $optionsNesting = self::$optionsNesting;
        if (self::$qtyOptionsNesting > 0) {
            for ($i = 1; $i < self::$qtyOptionsNesting; $i++) {
                $optionsNesting = self::$optionsNesting . '--' . self::$optionsQty;
            }
            $this->addParameter('condition', $optionsNesting);
            $xpath = $this->_getControlXpath('fieldset', 'rule_' . $type . '_item') . '/li';
        } else {
            $xpath = $this->_getControlXpath('fieldset', 'apply_for_rule_' . $type) . '/ul/li';
            $this->addParameter('condition', $optionsNesting);
        }
        self::$optionsNesting = $optionsNesting;
        self::$optionsQty = $this->getXpathCount($xpath);
        $this->addParameter('key', self::$optionsQty);
    }

    /**
     * Fill data for one condition
     *
     * @param array $data
     * @param string $tabId
     * @param bool $isNested
     */
    public function fillConditionFields(array $data, $tabId = '', $isNested = false)
    {
        if ($isNested) {
            self::$qtyOptionsNesting +=1;
        }
        $type = preg_replace('/(^rule_)|(s$)/', '', $tabId);
        $this->setConditionsParams($type);
        $uimapData = $this->getCurrentUimapPage()->getMainForm();
        if ($tabId && $uimapData->getTab($tabId)) {
            $uimapData = $uimapData->getTab($tabId);
        }
        $fieldsets = $uimapData->getAllFieldsets($this->_paramsHelper);
        $formDataMap = $this->_getFormDataMap($fieldsets, $data);

        try {
            foreach ($formDataMap as $formFieldName => $formField) {
                if ($formFieldName === 'category') {
                    $this->click($uimapData->findLink(preg_replace('/(^rule_)|(s$)/', '', $tabId) . '_value'));
                    $this->click($uimapData->findLink('open_chosser'));
                    $this->pleaseWait();
                    $categories = explode(',', $formField['value']);
                    $categories = array_map('trim', $categories);
                    foreach ($categories as $value) {
                        $this->categoryHelper()->selectCategory($value, 'rule_condition_item');
                    }
                    $this->click($uimapData->findLink('confirm_choise'));
                    continue;
                }
                $this->clickControl('link', preg_replace('/(^select_)|(^type_)/', '', $formFieldName), false);
                switch ($formField['type']) {
                    case self::FIELD_TYPE_INPUT:
                        $this->_fillFormField($formField);
                        break;
                    case self::FIELD_TYPE_CHECKBOX:
                        $this->_fillFormCheckbox($formField);
                        break;
                    case self::FIELD_TYPE_DROPDOWN:
                        $this->_fillFormDropdown($formField);
                        break;
                    case self::FIELD_TYPE_RADIOBUTTON:
                        $this->_fillFormRadiobutton($formField);
                        break;
                    case self::FIELD_TYPE_MULTISELECT:
                        $this->_fillFormMultiselect($formField);
                        break;
                    default:
                        throw new PHPUnit_Framework_Exception('Unsupported field type');
                }
            }
        } catch (PHPUnit_Framework_Exception $e) {
            $errorMessage = isset($formFieldName)
                    ? 'Problem with field \'' . $formFieldName . '\': ' . $e->getMessage()
                    : $e->getMessage();
            $this->fail($errorMessage);
        }
    }

    /**
     * Open Rule
     *
     * @param array $ruleSearch
     */
    public function openRule(array $ruleSearch)
    {
        $ruleSearch = $this->arrayEmptyClear($ruleSearch);
        $xpathTR = $this->search($ruleSearch, 'rule_search_grid');
        $this->assertNotNull($xpathTR, 'Rule with next search criteria:' . "\n" .
                implode(' and ', $ruleSearch) . "\n" . 'is not found');
        $cellId = $this->getColumnIdByName('Rule Name');
        $this->addParameter('elementTitle', $this->getText($xpathTR . '//td[' . $cellId . ']'));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Open Rule and delete
     *
     * @param array $ruleSearch
     */
    public function deleteRule(array $ruleSearch)
    {
        $this->openRule($ruleSearch);
        $this->clickButtonAndConfirm('delete_rule', 'confirmation_for_delete');
    }

    /**
     * Verify Rule Data
     *
     * @param array|string $ruleData
     */
    public function verifyRuleData($ruleData)
    {
        if (is_string($ruleData)) {
            $ruleData = $this->loadData($ruleData);
        }
        $ruleData = $this->arrayEmptyClear($ruleData);
        $simpleVerify = array();
        $specialVerify = array();
        foreach ($ruleData as $tabName => $tabData) {
            if (is_array($tabData)) {
                foreach ($tabData as $fieldKey => $fieldValue) {
                    if (is_array($fieldValue)) {
                        $specialVerify[$fieldKey] = $fieldValue;
                    } else {
                        $simpleVerify[$fieldKey] = $fieldValue;
                    }
                }
            }
        }
        if (array_key_exists('websites', $simpleVerify) && !$this->controlIsPresent('multiselect', 'websites')) {
            unset($simpleVerify['websites']);
        }
        $this->assertTrue($this->verifyForm($simpleVerify), $this->getParsedMessages());
        //@TODO verify Conditions and storeView titles
    }

    /**
     * Sets all created Rules as inactive (PreConditions for prices verification in frontend)
     *
     * @return bool
     */
    public function setAllRulesToInactive()
    {
        $xpathTR = $this->search(array('filter_status' => 'Active'), 'rule_search_grid');
        if (!$xpathTR) {
            return true;
        }
        $cellId = $this->getColumnIdByName('Rule Name');
        while ($this->isElementPresent($xpathTR)) {
            $this->addParameter('elementTitle', $this->getText($xpathTR . '//td[' . $cellId . ']'));
            $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
            $this->click($xpathTR);
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
            $this->fillForm(array('status' => 'Inactive'), 'rule_information');
            $this->saveForm('save_rule');
        }
    }

    /**
     * Edit Created Rule
     *
     * @param array $editRuleData
     * @param array $ruleSearchCreated
     */
    public function editRule($editRuleData, $ruleSearchCreated)
    {
        $this->openRule($ruleSearchCreated);
        $this->fillTabs($editRuleData);
        $this->saveForm('save_rule');
    }
}