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

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ecommerce\Creditlimit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_creditlimit;
    /**
     * @var \Magento\Customer\Model\Session $customersesion
     */
    protected $_customersession;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditcodeFactory
     */
    protected $_creditcode;
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionFactory
     */
    protected $_transaction;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Ecommerce\Creditlimit\Helper\Account $accountHelper
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimit
     * @param \Magento\Customer\Model\Session $customersesion
     * @param \Ecommerce\Creditlimit\Model\CreditcodeFactory $creditcode
     * @param \Ecommerce\Creditlimit\Model\TransactionFactory $transaction
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ecommerce\Creditlimit\Helper\Account $accountHelper,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimit,
        \Magento\Customer\Model\Session $customersesion,
        \Ecommerce\Creditlimit\Model\CreditcodeFactory $creditcode,
        \Ecommerce\Creditlimit\Model\TransactionFactory $transaction
    )
    {
        $this->_accountHelper = $accountHelper;
        $this->_creditlimit = $creditlimit;
        $this->_customersession = $customersesion;
        $this->_creditcode = $creditcode;
        $this->_transaction = $transaction;
        parent::__construct($context);
    }


    public function execute()
    {
    	if (!$this->_accountHelper->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $credit_code_id = $this->getRequest()->getParam('id');
        $customer_id = $this->_customersession->getCustomerId();
        $credit_code = $this->_creditcode->create()->load($credit_code_id);
        $add_balance = $credit_code->getAmountCredit();
        $credit_code_status = $credit_code->getStatus();
        if($credit_code_status == 2 || $credit_code_status == 3 ){
            $warning = __('Credit code %s has been used.',$credit_code->getCreditCode());
            $this->messageManager->addError($warning);
            return $this->_redirect('*/index/share');             
        }
        $this->_transaction->create()->addTransactionHistory($customer_id, \Ecommerce\Creditlimit\Model\TransactionType::TYPE_CANCEL_SHARE_CREDIT, __("cancel share credit "), "", $add_balance);
        $this->_creditlimit->create()->changeCustomerCredit($add_balance);
        $this->_creditcode->create()->changeCodeStatus($credit_code_id, \Ecommerce\Creditlimit\Model\Source\Status::STATUS_CANCELLED);
        $this->messageManager->addSuccess(__('Credit code has been canceled'));
        return $this->_redirect('*/index/share');
    }
}
