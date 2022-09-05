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

namespace Ecommerce\Creditlimit\Block\Adminhtml\Customer\Renderer;

class Customerprice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_helperCore;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_creditlimitFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory,
        \Magento\Framework\Pricing\Helper\Data $helperCore,
        array $data = []
    )
    {
        $this->_creditlimitFactory = $creditlimitFactory;
        $this->_helperCore = $helperCore;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $customerId = $row->getId();
        $customer = $this->_creditlimitFactory->create()->load($customerId, 'customer_id');
        $price = $customer->getCreditBalance();

        if ($price == NULL) {
            $price = 0.00;
        }
        return $this->_helperCore->currency($price, true, false);
    }

}
