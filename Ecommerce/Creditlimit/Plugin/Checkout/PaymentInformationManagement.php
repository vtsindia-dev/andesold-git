<?php
/**
 * Copyright © 2016 Ecommerce. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecommerce\Creditlimit\Plugin\Checkout;
/**
 * Class PaymentInformationManagement
 * @package Ecommerce\Creditlimit\Plugin\Checkout
 */
class PaymentInformationManagement
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * PaymentInformationManagement constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }
    
    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $paymentInformationManagement
     * @param $paymentDetails
     * @param $cartId
     * @return $paymentDetails
     */
    public function aroundGetPaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $paymentInformationManagement,
        \Closure $proceed,
        $cartId
    )
    {
        $paymentDetails = $proceed($cartId);
        $totals = $paymentDetails->getTotals();
        if ($totals->getBaseDiscountAmount()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $baseCreditlimitDiscount = $quote->getBaseCreditlimitDiscount();
            if ($baseCreditlimitDiscount) {
                $totals->setBaseDiscountAmount($totals->getBaseDiscountAmount() + $baseCreditlimitDiscount);
                $totals->setDiscountAmount($totals->getDiscountAmount() + $quote->getCreditlimitDiscount());
            }
        }
        return $paymentDetails->setTotals($totals);
    }
}