<?php
/**
 * Ecommerce
 * NOTICE OF LICENSE
 * This source file is subject to the Ecommerce.com license that is
 * available through the world-wide-web at this URL:
 * http://www.ecommerce.com/license-agreement.html
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecommerce
 * @package     Ecommerce_Creditlimit
 * @copyright   Copyright (c) 2017 Ecommerce (http://www.ecommerce.com/)
 * @license     http://www.ecommerce.com/license-agreement.html
 */

namespace Ecommerce\Creditlimit\Model\Total\Quote;

use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Tax\Model\Config;
use Ecommerce\Creditlimit\Helper\Account;
use Ecommerce\Creditlimit\Helper\Data;
use Ecommerce\Creditlimit\Service\Discount\DiscountService;
use Ecommerce\Creditlimit\Model\Product\Type as ProductCreditType;

/**
 * Abstract discount with store credit
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DiscountAbstract extends AbstractTotal
{
    protected $_code = 'creditdiscount';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var Account
     */
    protected $accountHelper;
    /**
     * @var Data
     */
    protected $creditHelper;

    /**
     * @var DiscountService
     */
    protected $discountService;

    /**
     * @var Config
     */
    protected $taxConfig;

    /**
     * @var float
     */
    protected $baseItemsPrice = 0;

    /**
     * @var float
     */
    protected $baseDiscountTotal = 0;

    /**
     * @var float
     */
    protected $discountTotal = 0;

    /**
     * DiscountAbstract constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $checkoutSession
     * @param Account $accountHelper
     * @param Data $creditHelper
     * @param DiscountService $discountService
     * @param Config $taxConfig
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Session $checkoutSession,
        Account $accountHelper,
        Data $creditHelper,
        DiscountService $discountService,
        Config $taxConfig
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
        $this->accountHelper = $accountHelper;
        $this->creditHelper = $creditHelper;
        $this->discountService = $discountService;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Collect address discount amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @param bool $isApplyAfterTax
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculateDiscount(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total,
        $isApplyAfterTax = false
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();

        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }

        $items = $shippingAssignment->getItems();

        if (!count($items)) {
            return $this;
        }

        $creditAmountEntered = $quote->getCustomerCreditAmount();
        if ($creditAmountEntered === 0 || !$this->accountHelper->customerGroupCheck()) {
            $quote->setCreditdiscountAmount(null);
            $quote->setBaseCreditdiscountAmount(null);
            return $this;
        }

        $this->baseDiscountTotal = $this->discountTotal = 0;

        $this->discountService->initTotals($items, $isApplyAfterTax);

        $itemsTotal = $this->discountService->getQuoteTotalData();
        if ($itemsTotal['base_items_price'] <= 0) {
            $quote->setCreditdiscountAmount(null);
            $quote->setBaseCreditdiscountAmount(null);
            return $this;
        }

        $this->baseItemsPrice = 0;
        foreach ($items as $item) {
            if ($item->getParentItemId() || $item->getProductType() == ProductCreditType::TYPE_CODE) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->calculateDiscountItem($child, $isApplyAfterTax);
                }
            } else {
                $this->calculateDiscountItem($item, $isApplyAfterTax);
            }
        }

        if ($creditAmountEntered > $this->baseDiscountTotal && $this->creditHelper->getSpendConfig('shipping')) {
            $shippingAmount = $address->getShippingAmountForDiscount();

            if ($this->taxConfig->shippingPriceIncludesTax()) {
                $shippingAmountIncludedTax = true;
            } else {
                $shippingAmountIncludedTax = false;
            }

            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            $baseShippingAmount = $baseShippingAmount - $address->getBaseShippingDiscountAmount();
            if ($isApplyAfterTax && !$shippingAmountIncludedTax) {
                $baseShippingAmount += $address->getBaseShippingTaxAmount();
            } elseif (!$isApplyAfterTax && $shippingAmountIncludedTax) {
                $baseShippingAmount -= $address->getBaseShippingTaxAmount();
            }

            $baseDiscountShipping = $creditAmountEntered - $this->baseDiscountTotal;
            $baseDiscountShipping = min($baseDiscountShipping, $baseShippingAmount);
            $baseDiscountShipping = $this->priceCurrency->round($baseDiscountShipping);

            $discountShipping = $this->priceCurrency->convert($baseDiscountShipping, $quote->getStore());
            $discountShipping = $this->priceCurrency->round($discountShipping);

            $total->setBaseCreditlimitDiscountForShipping(
                $total->getBaseCreditlimitDiscountForShipping() + $baseDiscountShipping
            );
            $total->setCreditlimitDiscountForShipping(
                $total->getCreditlimitDiscountForShipping() + $discountShipping
            );
            $total->setEcommerceBaseDiscountForShipping(
                $total->getEcommerceBaseDiscountForShipping() + $baseDiscountShipping
            );
            $total->setEcommerceDiscountForShipping(
                $total->getEcommerceDiscountForShipping() + $discountShipping
            );
            $total->setBaseShippingDiscountAmount(
                max(0, $total->getBaseShippingDiscountAmount() + $baseDiscountShipping)
            );
            $total->setShippingDiscountAmount(
                max(0, $total->getShippingDiscountAmount() + $discountShipping)
            );

            $this->baseDiscountTotal += $baseDiscountShipping;
            $this->discountTotal += $discountShipping;
        }

        $quote->setCreditdiscountAmount($this->discountTotal);
        $quote->setBaseCreditdiscountAmount($this->baseDiscountTotal);

        $total->setBaseCreditlimitDiscount($this->baseDiscountTotal);
        $total->setCreditlimitDiscount($this->discountTotal);
        $total->setEcommerceBaseDiscount($total->getEcommerceBaseDiscount() + $this->baseDiscountTotal);
        $total->setEcommerceDiscount($total->getEcommerceDiscount() + $this->discountTotal);
        $total->setBaseDiscountAmount($total->getBaseDiscountAmount() - $this->baseDiscountTotal);
        $total->setDiscountAmount($total->getDiscountAmount() - $this->discountTotal);
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotalWithDiscount() - $this->baseDiscountTotal);
        $total->setSubtotalWithDiscount($total->getSubtotalWithDiscount() - $this->discountTotal);

        $quote->setBaseCreditlimitDiscount($total->getBaseCreditlimitDiscount());
        $quote->setCreditlimitDiscount($total->getCreditlimitDiscount());
        $quote->setEcommerceBaseDiscount($total->getEcommerceBaseDiscount());
        $quote->setEcommerceDiscount($total->getEcommerceDiscount());
        $quote->setBaseCreditlimitDiscountForShipping($total->getBaseCreditlimitDiscountForShipping());
        $quote->setCreditlimitDiscountForShipping($total->getCreditlimitDiscountForShipping());
        $quote->setEcommerceBaseDiscountForShipping($total->getEcommerceBaseDiscountForShipping());
        $quote->setEcommerceDiscountForShipping($total->getEcommerceDiscountForShipping());

        $total->setTotalAmount($this->getCode(), (string)-$this->discountTotal);
        $total->setBaseTotalAmount($this->getCode(), (string)-$this->baseDiscountTotal);
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total)
    {
        $result = [];
        $amount = $total->getCreditlimitDiscount();

        if ($amount != 0) {
            if ($this->getCode() == 'creditdiscount') {
                $result = [
                    'code' => $this->getCode(),
                    'title' => __('Customer Credit'),
                    'value' => -abs($amount)
                ];
            }
        }

        return $result;
    }

    /**
     * Calculate Discount Item
     *
     * @param CartItemInterface $item
     * @param bool $isApplyAfterTax
     */
    public function calculateDiscountItem(CartItemInterface $item, bool $isApplyAfterTax)
    {
        $store = $item->getQuote()->getStore();
        $creditAmountEntered = $item->getQuote()->getCustomerCreditAmount();
        $itemsTotal = $this->discountService->getQuoteTotalData();

        $qty = $item->getTotalQty();
        $itemPrice = $this->discountService->getItemPrice($item);
        $baseItemPrice = $this->discountService->getItemBasePrice($item);
        $itemPriceAfterDiscount = $itemPrice * $qty - $item->getDiscountAmount();

        $baseDiscountAmount = $item->getBaseDiscountAmount();
        $baseItemPriceAfterDiscount = $baseItemPrice * $qty - $baseDiscountAmount;

        // If discount on price include tax
        // => ItemPrice has already include tax
        // => No need to summary tax anymore

        // If discount on price exclude tax
        // => Tax need to be added
        if ($isApplyAfterTax && !$this->taxConfig->discountTax($store)) {
            $itemPriceAfterDiscount += $item->getTaxAmount();
            $baseItemPriceAfterDiscount += $item->getBaseTaxAmount();
        } elseif (!$isApplyAfterTax && $this->taxConfig->discountTax($store)) {
            $itemPriceAfterDiscount -= $item->getTaxAmount();
            $baseItemPriceAfterDiscount -= $item->getBaseTaxAmount();
        }

        $this->baseItemsPrice += $baseItemPriceAfterDiscount;
        if ($this->baseItemsPrice == $itemsTotal['base_items_price']) {
            $baseItemDiscountAmount = $creditAmountEntered - $this->baseDiscountTotal;
        } else {
            $discountRate = $baseItemPriceAfterDiscount / $itemsTotal['base_items_price'];
            $baseItemDiscountAmount = $creditAmountEntered * $discountRate;
        }

        $baseItemDiscountAmount = $this->priceCurrency->round($baseItemDiscountAmount);
        $baseItemDiscountAmount = min($baseItemDiscountAmount, $baseItemPriceAfterDiscount);

        $itemDiscountAmount = $this->priceCurrency->convert($baseItemDiscountAmount, $store);
        $itemDiscountAmount = $this->priceCurrency->round($itemDiscountAmount);
        $itemDiscountAmount = min($itemDiscountAmount, $itemPriceAfterDiscount);

        $item->setBaseCreditlimitDiscount($baseItemDiscountAmount)
            ->setCreditlimitDiscount($itemDiscountAmount)
            ->setEcommerceBaseDiscount($item->getEcommerceBaseDiscount() + $baseItemDiscountAmount)
            ->setEcommerceDiscount($item->getEcommerceDiscount() + $itemDiscountAmount)
            ->setBaseDiscountAmount($item->getBaseDiscountAmount() + $baseItemDiscountAmount)
            ->setDiscountAmount($item->getDiscountAmount() + $itemDiscountAmount);

        $this->baseDiscountTotal += $baseItemDiscountAmount;
        $this->discountTotal += $itemDiscountAmount;
    }
}
