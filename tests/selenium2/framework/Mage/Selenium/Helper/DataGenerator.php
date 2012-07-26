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
 * Data generator helper. Generates random data for using in tests.
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Helper_DataGenerator extends Mage_Selenium_Helper_Abstract
{
    /**
     * PCRE classes used for data generation
     * @var array
     */
    protected $_chars = array(
        ':alnum:'       => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        ':alpha:'       => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ':digit:'       => '0123456789',
        ':lower:'       => 'abcdefghijklmnopqrstuvwxyz',
        ':upper:'       => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ':punct:'       => '!@#$%^&*()_+=-[]{}\\|";:/?.>,<',
        'invalid-email' => '()[]\\;:,<>@'
    );

    /**
     * Email domain used for auto generated values
     * @var string
     */
    protected $_emailDomain = 'example.com';

    /**
     * Email domain zone used for auto generated values
     * @var string
     */
    protected $_emailDomainZone = 'com';

    /**
     * Paragraph delimiter used for text generation
     * @var string
     */
    protected $_paragraphDelimiter = "\n";

    /**
     * Generates some random value
     *
     * @param string $type Available types are 'string', 'text', 'email'
     * @param int $length Generated value length
     * @param null|string|array $modifier Value modifier, e.g. PCRE class
     * @param null|string $prefix Prefix to prepend the generated value
     *
     * @return string
     * @throws Mage_Selenium_Exception
     */
    public function generate($type = 'string', $length = 100, $modifier = null, $prefix = null)
    {
        $result = null;
        switch ($type) {
            case 'string':
                $result = $this->generateRandomString($length, $modifier, $prefix);
                break;
            case 'text':
                $result = $this->generateRandomText($length, $modifier, $prefix);
                break;
            case 'email':
                $result = $this->generateEmailAddress($length, $modifier, $prefix);
                break;
            default:
                throw new Mage_Selenium_Exception('Undefined type of generation');
                break;
        }
        return $result;
    }

    /**
     * Generates email address
     *
     * @param int $length Generated string length (number of characters)
     * @param string $validity Defines if the generated string should be a valid email address possible values of
     * this parameter are 'valid' and 'invalid', any other value doesn't define validity of the generated address
     * @param string $prefix Prefix to prepend the generated value
     *
     * @return string
     */
    public function generateEmailAddress($length = 20, $validity = 'valid', $prefix = '')
    {
        $minLength = 6;

        if ($length < $minLength) {
            $length = $minLength;
        }

        if (!$validity) {
            $validity = 'valid';
        }

        $email = $prefix;

        //Subtracts 2 characters, as they are needed for '@' and '.'
        $mainLength = floor(($length - strlen($this->_emailDomainZone) - strlen($prefix) - 2) / 2);
        $domainPartLength = $length - strlen($this->_emailDomainZone) - strlen($prefix) - $mainLength - 2;

        switch ($validity) {
            case 'valid':
                $email .= $this->generateRandomString($mainLength);
                break;
            case 'invalid':
                mt_srand((double)microtime() * 100000);
                switch (mt_rand(0, 3)) {
                    case 0:
                        $email .= $this->generateRandomString(ceil($mainLength / 2))
                            . $this->generateRandomString(floor($mainLength / 2), 'invalid-email');
                        break;
                    case 1:
                        $email .= $this->generateRandomString($mainLength - 1, ':alnum:', '.');
                        break;
                    case 2:
                        $ml = $mainLength - 2;
                        $email .= $this->generateRandomString(ceil($ml / 2))
                            . '..' . $this->generateRandomString(floor($ml / 2));
                        break;
                    case 3:
                        $ml = $mainLength - 1;
                        $email .= $this->generateRandomString(ceil($ml / 2))
                            . '@' . $this->generateRandomString(floor($ml / 2));
                        break;
                }
                break;
            default:
                $email .= $this->generateRandomString($mainLength, array(':alnum:', 'invalid-email'));
                break;
        }

        if (!empty($email)) {
            $email .= '@' . $this->generateRandomString($domainPartLength) . '.' . $this->_emailDomainZone;
        }

        return $email;
    }

    /**
     * Generates random string
     *
     * @param int $length Generated string length (number of characters)
     * @param string|array $class PCRE class(es) to use for character in the generated string.
     * String value can contain several comma-separated PCRE classes.
     * If no class is specified, only alphanumeric characters are used by default
     * @param string $prefix Prefix to prepend the generated value
     *
     * @return string
     */
    public function generateRandomString($length = 100, $class = ':alnum:', $prefix = '')
    {
        if (!$class) {
            $class = ':alnum:';
        }

        if (!is_array($class)) {
            $class = explode(',', $class);
        }

        $chars = '';
        foreach ($class as $elem) {
            if (isset($this->_chars[$elem])) {
                $chars .= $this->_chars[$elem];
            }
        }

        if (in_array('text', $class)) {
            $chars .= str_repeat(' ', (int)strlen($chars) * 0.2);
        }

        $string = $prefix;
        if (!empty($chars)) {
            $charsLength = strlen($chars);
            mt_srand((double)microtime() * 100000);
            for ($i = 0; $i < $length; $i++) {
                $string .= $chars[mt_rand(0, $charsLength - 1)];
            }
        }

        return $string;
    }

    /**
     * Generates random string. Inserts spaces to the generated text randomly.
     * Note that spaces will be added to the text in addition to the specified class.
     *
     * @param int $length Generated string length (number of characters)
     * @param  null|string|array $modifier Allows to specify multiple properties of the generated text, e.g.:<br>
     * <li>'class' => string - PCRE class(es) to use for generation, see<br>
     * {@link Mage_Selenium_Helper_DataGenerator::generateRandomString()}
     * <li>if no class is specified, only alphanumeric characters are used by default
     * <li>'para'  => int - number of paragraphs (default = 1)
     * @param string $prefix Prefix to prepend the generated value
     *
     * @return string
     */
    public function generateRandomText($length = 100, $modifier = null, $prefix = '')
    {
        if (is_array($modifier)) {
            $class = (isset($modifier['class'])) ? $modifier['class'] : ':alnum:';
            $paraCount = (isset($modifier['para']) && $modifier['para'] > 1) ? (int)$modifier['para'] : 1;
        } else {
            $class = (!empty($modifier)) ? $modifier : ':alnum:';
            $paraCount = 1;
        }

        if (!is_array($class)) {
            $class = explode(',', $class);
        }

        $class[] = 'text';
        $textArr = array();

        //Reserve place for paragraph delimiters
        $length -= ($paraCount - 1) * strlen($this->_paragraphDelimiter);
        $paraLength = floor($length / $paraCount);

        for ($i = 0; $i < $paraCount; $i++) {
            $textArr[] = $this->generateRandomString($paraLength, $class);
        }

        //Correct result length
        $missed = $length - ($paraLength * $paraCount);
        if ($missed) {
            $textArr[$paraCount - 1] .= $this->generateRandomString($missed, $class);
        }

        return $prefix . implode($this->_paragraphDelimiter, $textArr);
    }
}
