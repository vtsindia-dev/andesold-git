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

namespace Ecommerce\Creditlimit\Model\Total\Quote;

/**
 * Calculate applied store credit in cart
 *
 * @deprecated Always apply store credit after tax, so this file is no need anymore
 * @see \Ecommerce\Creditlimit\Model\Total\Quote\Discount
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DiscountAfterTax extends DiscountAbstract
{
    protected $_code = 'creditdiscountaftertax';
    
    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!$isApplyAfterTax = !$this->taxConfig->applyTaxAfterDiscount($quote->getStoreId())) {
            return $this;
        }
        
        $this->calculateDiscount($quote, $shippingAssignment, $total, $isApplyAfterTax);
        return $this;
    }
}
