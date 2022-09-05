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

namespace Ecommerce\Creditlimit\Model\Total\Order\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Total\AbstractTotal;

/**
 * Class CreditDiscount
 *
 * Used to calculate invoice total
 */
class CreditDiscount extends AbstractTotal
{
    /**
     * Collect invoice giftvoucher
     *
     * @param Invoice $invoice
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getCreditlimitDiscount() < 0.0001) {
            return $this;
        }

        $totalDiscountInvoiced = 0;
        $totalBaseDiscountInvoiced = 0;

        $totalDiscountAmount = 0;
        $totalBaseDiscountAmount = 0;
        $checkAddShipping = true;

        foreach ($order->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getCreditlimitDiscount()) {
                $checkAddShipping = false;
                $totalBaseDiscountInvoiced += $previousInvoice->getBaseCreditlimitDiscount();
                $totalDiscountInvoiced += $previousInvoice->getCreditlimitDiscount();
            }
        }

        if ($checkAddShipping) {
            $totalBaseDiscountAmount += $order->getBaseCreditlimitDiscountForShipping();
            $totalDiscountAmount += $order->getCreditlimitDiscountForShipping();
        }

        if ($invoice->isLast()) {
            $totalBaseDiscountAmount = $order->getBaseCreditlimitDiscount() - $totalBaseDiscountInvoiced;
            $totalDiscountAmount = $order->getCreditlimitDiscount() - $totalDiscountInvoiced;
        } else {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $baseOrderItemCreditlimitDiscount = (float)$orderItem->getBaseCreditlimitDiscount();
                $orderItemCreditlimitDiscount = (float)$orderItem->getCreditlimitDiscount();

                $orderItemQty = $orderItem->getQtyOrdered();
                $invoiceItemQty = $item->getQty();

                if ($baseOrderItemCreditlimitDiscount && $orderItemQty) {
                    $totalBaseDiscountAmount += $invoice->roundPrice(
                        $baseOrderItemCreditlimitDiscount / $orderItemQty * $invoiceItemQty,
                        'base',
                        false
                    );
                    $totalDiscountAmount += $invoice->roundPrice(
                        $orderItemCreditlimitDiscount / $orderItemQty * $invoiceItemQty,
                        'regular',
                        false
                    );
                }
            }
        }

        $invoice->setBaseCreditlimitDiscount($totalBaseDiscountAmount);
        $invoice->setCreditlimitDiscount($totalDiscountAmount);

        $grandTotal = abs($invoice->getGrandTotal()) < 0.0001 ? 0 : $invoice->getGrandTotal();
        $baseGrandTotal = abs($invoice->getBaseGrandTotal()) < 0.0001 ? 0 : $invoice->getBaseGrandTotal();
        $invoice->setGrandTotal($grandTotal);
        $invoice->setBaseGrandTotal($baseGrandTotal);

        return $this;
    }
}
