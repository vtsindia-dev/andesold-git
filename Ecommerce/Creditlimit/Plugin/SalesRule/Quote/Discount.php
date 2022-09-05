<?php
/**
 * Copyright © 2016 Ecommerce. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecommerce\Creditlimit\Plugin\SalesRule\Quote;

/**
 * Class Discount
 * @package Ecommerce\Creditlimit\Plugin\SalesRule\Quote
 */
class Discount
{
    /**
     * @param \Magento\SalesRule\Model\Quote\Discount $discount
     * @param null|array $result
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return null|array
     */
    public function aroundFetch(
        \Magento\SalesRule\Model\Quote\Discount $discount,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        $result = $proceed($quote, $total);
        if (is_array($result)) {
            if ($total->getCreditlimitDiscount() && $result['value'] != 0) {
                $result['value'] = min(0, $result['value'] + $total->getCreditlimitDiscount());
            }
        }
        return $result;
    }
}