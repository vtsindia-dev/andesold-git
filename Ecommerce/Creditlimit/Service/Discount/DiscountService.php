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

namespace Ecommerce\Creditlimit\Service\Discount;

use Magento\Framework\App\ObjectManager;

/**
 * Service discount by store credit
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DiscountService
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var array
     */
    protected $quoteTotalData;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * DiscountService constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Tax\Model\Config|null $taxConfig
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Tax\Model\Config $taxConfig = null
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->taxConfig = $taxConfig ?: ObjectManager::getInstance()->create(\Magento\Tax\Model\Config::class);
    }

    /**
     * Calculate quote totals for each giftCode and save results
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @param bool $isApplyGiftAfterTax
     */
    public function initTotals($items, $isApplyGiftAfterTax = false)
    {
        $totalItemsPrice = 0;
        $totalBaseItemsPrice = 0;
        $validItemsCount = 0;
        foreach ($items as $item) {
            //Skipping child items to avoid double calculations
            if ($item->getParentItemId() || $item->getProduct()->getTypeId() == 'creditlimit') {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $totalItemPrice = $this->getTotalItemPrice($child, $isApplyGiftAfterTax);
                    $totalItemsPrice += $totalItemPrice['item_price'];
                    $totalBaseItemsPrice += $totalItemPrice['base_item_price'];
                }
            } else {
                $totalItemPrice = $this->getTotalItemPrice($item, $isApplyGiftAfterTax);
                $totalItemsPrice += $totalItemPrice['item_price'];
                $totalBaseItemsPrice += $totalItemPrice['base_item_price'];
            }
            $validItemsCount++;
        }

        $this->quoteTotalData = [
            'items_price' => $totalItemsPrice,
            'base_items_price' => $totalBaseItemsPrice,
            'items_count' => $validItemsCount,
        ];
    }

    /**
     * Get Total Item Price
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @param bool $isApplyGiftAfterTax
     * @return array
     */
    public function getTotalItemPrice($item, $isApplyGiftAfterTax)
    {
        $totalItemPrice = 0;
        $totalBaseItemPrice = 0;
        $qty = $item->getTotalQty();
        $totalItemPrice += $this->getItemPrice($item) * $qty - $item->getDiscountAmount();
        $totalBaseItemPrice += $this->getItemBasePrice($item) * $qty - $item->getBaseDiscountAmount();
        if ($isApplyGiftAfterTax && !$this->taxConfig->discountTax()) {
            $totalItemPrice += $item->getTaxAmount();
            $totalBaseItemPrice += $item->getBaseTaxAmount();
        } elseif (!$isApplyGiftAfterTax && $this->taxConfig->discountTax()) {
            $totalItemPrice -= $item->getTaxAmount();
            $totalBaseItemPrice -= $item->getBaseTaxAmount();
        }
        return [
            'item_price' => $totalItemPrice,
            'base_item_price' => $totalBaseItemPrice
        ];
    }

    /**
     * Get Quote Total Data
     *
     * @return array
     */
    public function getQuoteTotalData()
    {
        return $this->quoteTotalData;
    }

    /**
     * Return item base price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemBasePrice($item)
    {
        $price = $item->getBaseDiscountCalculationPrice();
        if ($price === null) {
            return $item->getBaseCalculationPrice();
        } else {
            return $price;
        }
    }

    /**
     * Return item price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        if ($price === null) {
            return $item->getCalculationPrice();
        } else {
            return $price;
        }
    }
}
