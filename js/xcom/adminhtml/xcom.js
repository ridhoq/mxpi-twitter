/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

var Xcom = new Class.create();
Xcom.prototype = {
    /**
     * Initial config
     *
     * @param data Array
     */
    initialize : function (data)
    {
    },

    /**
     * Functions those are responsible for onboarding process
     */
    onboarding : {
        /**
         * Point user to the X.Commerce merchant registration process
         *
         * @param onboardingUri URI which is responseible for merchant registration
         * @param fabricConfigInfo JSON with configuration data sent to the URI
         */
        register : function (onboardingUri, fabricConfigInfo)
        {
            var $form = new Element('form', {
                'action' : onboardingUri,
                'method' : 'post',
                'target' : '_blank'
            });

            $form.insert(
                Element.writeAttribute(
                    new Element('input'), {'type': 'hidden', 'name': 'fabric_config_info', 'value': fabricConfigInfo}
                )
            );

            // IE won't submit form until it is inserted into the body
            document.body.appendChild($form);
            $form.submit();
        }
    }
};

var xcom = new Xcom();
