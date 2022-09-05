<?php
/**
 * Copyright © 2016 Ecommerce. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecommerce\Creditlimit\Plugin\Checkout;
/**
 * Class ShippingInformationManagement
 * @package Ecommerce\Creditlimit\Plugin\Checkout
 */
class ShippingInformationManagement
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
     * @param \Magento\Checkout\Model\ShippingInformationManagement $shippingInformationManagement
     * @param $paymentDetails
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return mixed
     */
    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $shippingInformationManagement,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    )
    {
        $paymentDetails = $proceed($cartId, $addressInformation);
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