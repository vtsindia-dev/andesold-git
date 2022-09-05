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

class Customeremail extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionFactory
     */
    protected $_transactionFactory;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_helperCore;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory
     * @param \Magento\Framework\Pricing\Helper\Data $helperCore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Pricing\Helper\Data $helperCore,
        array $data = []
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_helperCore = $helperCore;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $transactionId = $row->getId();
        $customerId = $this->_transactionFactory->create()->load($transactionId)->getCustomerId();
        $customer = $this->_customerFactory->create()->load($customerId);
        $emailAdrress = $customer->getData('email');
        if ($customer) {
            $href = $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'cart']);
            return '<a href="' . $href . '" target="_blank">' . $emailAdrress . '</a>';
        }
        return $row->getId();
    }

}
