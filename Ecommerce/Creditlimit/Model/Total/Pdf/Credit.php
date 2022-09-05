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

namespace Ecommerce\Creditlimit\Model\Total\Pdf;

/**
 * Class Credit
 *
 * Total Pdf credit model
 */
class Credit extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * @inheritDoc
     */
    public function getTotalsForDisplay()
    {
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }
        $totals = [
            [
                'label' => __('Customer credit:'),
                'amount' => $amount,
                'font_size' => $fontSize,
            ]
        ];
        return $totals;
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        if ($this->getSource()->getCreditlimitDiscount()) {
            return -$this->getSource()->getCreditlimitDiscount();
        }
        return -$this->getOrder()->getCreditlimitDiscount();
    }
}
