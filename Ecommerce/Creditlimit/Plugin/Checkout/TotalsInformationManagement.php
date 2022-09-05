<?php
/**
 * Copyright © 2016 Ecommerce. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecommerce\Creditlimit\Plugin\Checkout;
/**
 * Class TotalsInformationManagement
 * @package Ecommerce\Creditlimit\Plugin\Checkout
 */
class TotalsInformationManagement
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
    )
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement $totalsInformationManagement
     * @param $totals
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return mixed
     */
    public function aroundCalculate(
        \Magento\Checkout\Model\TotalsInformationManagement $totalsInformationManagement,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    )
    {
        $totals = $proceed($cartId, $addressInformation);
        if ($totals && $totals->getBaseDiscountAmount()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $baseCreditlimitDiscount = $quote->getBaseCreditlimitDiscount();
            if ($baseCreditlimitDiscount) {
                $totals->setBaseDiscountAmount($totals->getBaseDiscountAmount() + $baseCreditlimitDiscount);
                $totals->setDiscountAmount($totals->getDiscountAmount() + $quote->getCreditlimitDiscount());
            }
        }
        return $totals;
    }
}