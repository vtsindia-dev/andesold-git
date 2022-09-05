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

namespace Ecommerce\Creditlimit\Controller\Index;

class Redeempost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditcodeFactory
     */
    protected $_creditcodeFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_creditlimitFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ecommerce\Creditlimit\Model\CreditcodeFactory $creditcodeFactory,
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory,
     * @param \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Ecommerce\Creditlimit\Model\CreditcodeFactory $creditcodeFactory,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory,
        \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_creditcodeFactory = $creditcodeFactory;
        $this->_creditlimitFactory = $creditlimitFactory;
        $this->_transactionFactory = $transactionFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $customer_id = $this->_customerSession->getCustomerId();
        $credit_code = $this->getRequest()->getParam('redeem_credit_code');
        $credit = $this->_creditcodeFactory->create()->getCollection()->addFieldToFilter('credit_code', $credit_code);
        if ($credit->getSize() == 0) {
            $this->messageManager->addError(__('Code is invalid. Please check again!'));
            $this->_redirect('creditlimit/index/redeem');
        } elseif ($credit->getFirstItem()->getStatus() != 1) {
            $this->messageManager->addError('Code was canceled.');
            $this->_redirect('creditlimit/index/redeem');
        } else {
            $this->_creditcodeFactory->create()->changeCodeStatus($credit->getFirstItem()->getId(), \Ecommerce\Creditlimit\Model\Source\Status::STATUS_USED);
            $credit_amount = $credit->getFirstItem()->getAmountCredit();
            $this->_transactionFactory->create()->addTransactionHistory($customer_id, \Ecommerce\Creditlimit\Model\TransactionType::TYPE_REDEEM_CREDIT, __("redeem credit by code '") . $credit_code . "'", "", $credit_amount);
            $this->_creditlimitFactory->create()->changeCustomerCredit($credit_amount);
            $this->messageManager->addSuccess(__('Redeem success!'));
            $this->_redirect('creditlimit/index/index');
        }
    }
}
