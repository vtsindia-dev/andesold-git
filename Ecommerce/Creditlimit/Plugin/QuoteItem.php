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

namespace Ecommerce\Creditlimit\Plugin;

use Closure;
use Magento\Sales\Model\Order\Item;

class QuoteItem
{
    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Ecommerce\Creditlimit\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Ecommerce\Creditlimit\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     *
     * Save gia tri discount vao sale order item
     *
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    )
    {
        if ($item->getProduct()->getTypeId() == \Ecommerce\Creditlimit\Model\Product\Type::TYPE_CODE) {
            $item->setBaseOriginalPrice($item->getBasePrice());
            $item->setOriginalPrice($item->getPrice());
        }
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        if ($item->getBaseCreditlimitDiscount()) {
            $orderItem->setCreditlimitDiscount($item->getCreditlimitDiscount());
            $orderItem->setBaseCreditlimitDiscount($item->getBaseCreditlimitDiscount());
            $orderItem->setEcommerceBaseDiscount($item->getEcommerceBaseDiscount());
            $orderItem->setEcommerceDiscount($item->getEcommerceDiscount());
        }
        return $orderItem;
    }
}
