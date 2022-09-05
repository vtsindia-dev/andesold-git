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


/**
 * Creditlimit Model
 *
 * @category    Ecommerce
 * @package     Ecommerce_Creditlimit
 * @author      Ecommerce Developer
 */
namespace Ecommerce\Creditlimit\Model;

/**
 * Class Aftertax
 *
 * After tax model
 */
class Aftertax
{
    /**
     * Get Option Array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            0 => __('Before tax'),
            1 => __('After tax'),
        ];
    }

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
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
}
