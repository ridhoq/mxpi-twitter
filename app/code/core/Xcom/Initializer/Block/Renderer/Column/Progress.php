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
class Xcom_Initializer_Block_Renderer_Column_Progress
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (!$row->getTotal()) {
            return $row->getStatus();
        }
        return $this->_getProgressBarHtml($row);
    }

    protected function _getProgressBarHtml(Varien_Object $row)
    {
        $percent = $row->getSaved() / $row->getTotal() * 100;
        if ($percent >= 100) {
            $percent = 100;
            $message = $row->getStatus();
        } else {
            $message = $this->__('%s of %s messages processed', $row->getSaved(), $row->getTotal());
        }
        return '
            <div id="progress_bar">
                <div class="status" style="width: ' . (int)$percent . '%;height:100%;"></div>
                <p>' . $message . '</p>
            </div>';
    }
}
