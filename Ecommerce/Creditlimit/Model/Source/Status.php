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
 * Class Status
 *
 * Source status model
 */
class Status extends \Magento\Framework\DataObject
{
    const STATUS_UNUSED = 1;
    const STATUS_USED = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_AWAITING_VERIFICATION = 4;

    /**
     * Get model option as array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_UNUSED => __('Unused'),
            self::STATUS_USED => __('Used'),
            self::STATUS_CANCELLED => __('Cancelled'),
            self::STATUS_AWAITING_VERIFICATION => __('Awaiting verification')
        ];
    }

    /**
     * Get model option hash as array
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->getOptionArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $options;
    }

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
