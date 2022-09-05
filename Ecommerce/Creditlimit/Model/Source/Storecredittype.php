<?php
/**
 * Ecommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecommerce.com license that is
 * available through the world-wide-web at this URL:
 * http://www.ecommerce.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecommerce
 * @package     Ecommerce_Creditlimit
 * @copyright   Copyright (c) 2017 Ecommerce (http://www.ecommerce.com/)
 * @license     http://www.ecommerce.com/license-agreement.html
 *
 */

namespace Ecommerce\Creditlimit\Model\Source;

/**
 * Class Storecredittype
 *
 * Store credit type model
 */
class Storecredittype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const CREDIT_TYPE_NONE = 0;
    const CREDIT_TYPE_FIX = 1;
    const CREDIT_TYPE_RANGE = 2;
    const CREDIT_TYPE_DROPDOWN = 3;

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'label' => __('Select'),
                    'value' => ''
                ],
                [
                    'label' => __('Fixed value'),
                    'value' => self::CREDIT_TYPE_FIX
                ],
                [
                    'label' => __('Range of values'),
                    'value' => self::CREDIT_TYPE_RANGE
                ],
                [
                    'label' => __('Dropdown values'),
                    'value' => self::CREDIT_TYPE_DROPDOWN
                ],
            ];
        }
        return $this->_options;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
