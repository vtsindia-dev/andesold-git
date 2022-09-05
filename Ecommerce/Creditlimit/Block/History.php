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

namespace Ecommerce\Creditlimit\Block;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_customerCreditFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionFactory
     */
    protected $_transactionFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionTypeFactory
     */
    protected $_transactiontypeFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $datetime;
    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_helperData;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $customerCreditFactory
     * @param \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory
     * @param \Ecommerce\Creditlimit\Model\TransactionTypeFactory $transactiontype
     * @param \Ecommerce\Creditlimit\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $customerCreditFactory,
        \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory,
        \Ecommerce\Creditlimit\Model\TransactionTypeFactory $transactiontypeFactory,
        \Ecommerce\Creditlimit\Helper\Data $helperData
    )
    {
        $this->_customerSession = $customerSession;
        $this->_customerCreditFactory = $customerCreditFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_transactiontypeFactory = $transactiontypeFactory;
        $this->datetime = $context->getLocaleDate();
        $this->_helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $customer_id = $this->_customerSession->getCustomer()->getId();
        $collection = $this->_transactionFactory->create()->getCollection()->addFieldToFilter('customer_id', $customer_id);
        $collection->setOrder('transaction_time', 'DESC');
        $this->setCollection($collection);
    }

    public function getLocaleDateTime()
    {
        return $this->datetime;
    }

    public function _prepareLayout()
    {
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'creditlimit.history.pager')->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getTransactionType($trans_type_id)
    {
        return $this->_transactiontypeFactory->create()->load($trans_type_id)->getTransactionName();
    }

    public function getCurrencyLabel($credit)
    {
//        $credit = $this->_customerCredit->getConvertedFromBaseCustomerCredit($credit); Gin fix multi currentcy
        return $this->_helperData->getFormatAmount($credit);
    }

}
