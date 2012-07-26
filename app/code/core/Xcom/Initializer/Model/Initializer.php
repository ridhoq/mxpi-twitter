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
class Xcom_Initializer_Model_Initializer
{
    /**
     * These are default rules. We need to unblock these actions for any issuer, otherwise it won't be possible
     * to direct user to some info page.
     *
     * @var array
     */
    protected $_restrictions = array(
        '*' => array(
            'allowed' => array(
                'action' => array(
                    array('xcomdenied' => 'xcom_initializer/initializer::xcomDenied'),
                    array('denied'     => '*/*::denied'),
                )
            )
        )
    );

    /**
     * Each scope has different priority. If request matched rules from different scopes - weight will decide which
     * of them to obey. Under same request URN and action represents same thing, so they don't have priority one above
     * another.
     *
     * @var array
     */
    protected $_weights = array(
        'scope' => array(
            'namespace'  => 1,
            'controller' => 2,
            'action'     => 3,
            'urn'        => 3,
        ),
        // If request meets rules with same scope but different direction, it will be decided by these weights which
        // to use
        'direction' => array(
            'denied'  => 1,
            'allowed' => 2,
        ),
    );

    /* @var array */
    protected $_currentMatch;
    protected $_topicsAmount = 0;

    /**
     * Checks whether route/controller action is allowed to be reached depending on initializer state
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return bool
     */
    public function isAllowed(Mage_Core_Controller_Request_Http $request)
    {
        if ($this->isDataCollected()) {
            return true;
        }

        if (count($this->_restrictions) == 1) {
            $initializerAclNode = Mage::getConfig()->getNode('default/xcom/initializer_acl');

            if ($initializerAclNode) {
                $this->_restrictions += $initializerAclNode->asArray();
            }
        }

        $parts = array(
            // In admin area getControllerModule returns module with added _Adminhtml, which is not going to meet
            // same module.
            'module'     => str_replace('_adminhtml', '', strtolower($request->getControllerModule())),
            'controller' => strtolower($request->getControllerName()),
            'action'     => strtolower($request->getActionName()),
            'urn'        => trim(strtolower($request->getRequestString()), '/'),
        );

        $moduleParts = explode('_', $parts['module']);
        $parts['namespace'] = $moduleParts[0];

        $this->_currentMatch = array(
           'scopeWeight'     => 0,
           'directionWeight' => 2,
           'urn'             => '',
       );

        foreach ($this->_restrictions as $issuer => $rules) {
            if ($issuer != '*') {
                if (!Mage::getResourceModel('xcom_initializer/job')->hasJobsLeft($issuer)) {
                    continue;
                }
            }

            foreach (array('allowed', 'denied') as $direction) {
                if (empty($rules[$direction])) {
                    continue;
                }

                foreach ($rules[$direction] as $scope => $values) {
                    if (!is_array($values)) {
                        Mage::log(printf('Invalid configuration for scope node %s', $scope));
                        continue;
                    }

                    foreach ($values as $value) {
                        $value = strtolower(is_array($value) ? current($value) : $value);
                        $hasMatched = false;

                        if (empty($value)) {
                            Mage::log(printf('Empty node inside scope %s', $scope));
                            continue;
                        }

                        switch (strtolower($scope)) {
                            case 'namespace':
                                if ('denied' == $direction) {
                                    $hasMatched = $value == $parts['namespace'];
                                } else {
                                    Mage::log('<namespace> node allowed in <denied> section only');
                                }
                                break;
                            case 'action':
                            case 'controller':
                                $pattern = '(?P<module>.+)/(?P<controller>.+)';
                                $scopes = array('module', 'controller');

                                if ('action' == $scope) {
                                    $pattern .= '::(?P<action>.+)';
                                    $scopes[] = 'action';
                                }

                                if (preg_match('#' . $pattern . '#', $value, $matches)) {
                                    // Split value into module and controller (and action, if it's 'action' rule)
                                    $hasMatched = true;

                                    foreach ($scopes as $_scope) {
                                        if (!in_array($matches[$_scope], array('*', $parts[$_scope]))) {
                                            // Value part doesn't match corresponding part of current request or '*'
                                            $hasMatched = false;
                                            break;
                                        }
                                    }
                                } else {
                                    Mage::log(printf('Invalid action/controller definition: %s. Allowed pattern: namespace_module/controller::action', $value));
                                }
                                break;
                            case 'module':
                                $hasMatched = $value == $parts['module'];
                                break;
                            case 'urn':
                                $value = trim($value, '/');
                                $hasMatched = 0 === strpos($parts['urn'], $value);
                                break;
                            default:
                                Mage::log(printf('Unknown rule scope: %s', $scope));
                        }

                        if ($hasMatched) {
                            $this->_matched($scope, $direction, 'urn' == $scope ? $value : '');
                        }
                    }
                }
            }
        }

        return $this->_isAllowed();
    }

    /**
     * isAllowed() reports to this function if match has occurred. This function decides whether to take that into
     * account, or not.
     *
     * @param string $scope
     * @param string $direction 'allowed' or 'denied'
     * @param string $urn       Specified only in case of URN scope match
     * @return Xcom_Initializer_Model_Initializer
     */
    protected function _matched($scope, $direction, $urn = '')
    {
        /**
         * Meta weights (in order of priority):
         * - scope
         * - "claryfying" URN
         * - direction
         */

        $hasPriority = false;

        if ($this->_weights['scope'][$scope] > $this->_currentMatch['scopeWeight']) {
            $this->_currentMatch = array(
                'scopeWeight'     => $this->_weights['scope'][$scope],
                'directionWeight' => $this->_weights['direction'][$direction],
                'urn'             => $urn,
            );
        } else {
            if (!empty($urn) && strlen($urn) >= strlen($this->_currentMatch['urn'])) {
                if (strpos($urn, $this->_currentMatch['urn']) === 0) {
                    $hasPriority = true;
                }
            } else {
                if ($this->_weights['scope'][$scope] == $this->_currentMatch['scopeWeight']) {
                    if ($this->_weights['direction'][$direction] > $this->_currentMatch['directionWeight']) {
                        $hasPriority = true;
                    }
                }
            }
        }

        if ($hasPriority) {
            // Leave scope as is because it is either direction change or clarifying URN
            $this->_currentMatch['directionWeight'] = $this->_weights['direction'][$direction];
            $this->_currentMatch['urn'] = $urn;
        }

        return $this;
    }

    /**
     * Checks whether submitted requested allowed depending on previous processing by isAllowed()
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_currentMatch['directionWeight'] == $this->_weights['direction']['allowed'];
    }

    /**
     * Check if all data is collected and ready to start work with xcom environment
     *
     * @return bool
     */
    public function isDataCollected()
    {
        if (!$this->_topicsAmount) {
            $this->_topicsAmount = count(
                Mage::helper('xcom_xfabric')->getNodeByXpath('*/*[@initializer="prepopulate"]')
            );
        }

        return Mage::getResourceModel('xcom_initializer/job')->isDataCollected($this->_topicsAmount);
    }
}
