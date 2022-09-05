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

class Sharepost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ecommerce\Creditlimit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_creditlimitFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionFactory
     */
    protected $_transactionFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditcodeFactory
     */
    protected $_creditcodeFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Ecommerce\Creditlimit\Helper\Account $accountHelper
     * @param \Ecommerce\Creditlimit\Helper\Data $creditHelper
     * @param \Magento\Checkout\Model\Session $customerSession
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory
     * @param \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory,
     * @param \Ecommerce\Creditlimit\Model\CreditcodeFactory $creditcodeFactory,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ecommerce\Creditlimit\Helper\Account $accountHelper,
        \Ecommerce\Creditlimit\Helper\Data $creditHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory,
        \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory,
        \Ecommerce\Creditlimit\Model\CreditcodeFactory $creditcodeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->_accountHelper = $accountHelper;
        $this->_creditHelper = $creditHelper;
        $this->_customerSession = $customerSession;
        $this->_creditlimitFactory = $creditlimitFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_creditcodeFactory = $creditcodeFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->_accountHelper->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        $customer = $this->_creditHelper->getCustomer();
        /* @var $creditlimitFactory \Ecommerce\Creditlimit\Model\Creditlimit */
        /* @var $transactionFactory \Ecommerce\Creditlimit\Model\Transaction */
        $creditlimitFactory = $this->_creditlimitFactory->create();
        $transactionFactory = $this->_transactionFactory->create();

        $customer_credit = round($this->_creditHelper->getCreditBalanceByUser(), 3);
        if ($customer_credit <= 0) {
            $this->messageManager->addError(__('Your credit amount not enough to share!'));
            return $this->_redirect("creditlimit/index/share");
        }
        $customer_id = $customer->getId();
        $customer_name = $customer->getFirstname() . " " . $customer->getLastname();
        $customer_email = $customer->getEmail();
        $credit_code_id = $this->getRequest()->getParam('credit_code_id_hide');
        if ($this->_creditHelper->getGeneralConfig('validate')) {
            if ($this->_customerSession->getData("sentemail") != 'yes') {
                return $this->_redirect("creditlimit/index/share");
            }
            $keycode = $this->getRequest()->getParam('creditlimitcode');
            $email = $this->getRequest()->getParam('email_hide');
            $amount = $this->getRequest()->getParam('amount_hide');
            $amount = $this->_creditHelper->getConvertedToBaseCustomerCredit($amount);
            $message = $this->getRequest()->getParam('message_hide');

            if ($email == $customer_email) {
                $this->messageManager->addError(__('Invalid email. Please check again!'));
                return $this->_redirect("creditlimit/index/share");
            }
            if ($amount < 0 || $amount > $customer_credit) {
                $this->messageManager->addError(__('Invalid amount. Please check again!'));
                return $this->_redirect("creditlimit/index/share");
            }

            $friend_account_id = $this->_customerFactory->create()->getCollection()
                ->addFieldToFilter('email', $email)
                ->getFirstItem()
                ->getId();
            if (trim($keycode) == trim($this->_customerSession->getData("emailcode"))) {

                $transactionFactory->addTransactionHistory($customer_id, \Ecommerce\Creditlimit\Model\TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS, $customer_email . __(" sent ") . $amount . __(" credit to ") . $email, "", -$amount);
                $creditlimitFactory->changeCustomerCredit(-$amount);
                if (isset($friend_account_id)) {
                    $transactionFactory->addTransactionHistory($friend_account_id, \Ecommerce\Creditlimit\Model\TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS, $email . __(" received ") . $amount . __(" credit from ") . $customer_name, "", $amount);
                    $creditlimitFactory->addCreditToFriend($amount, $friend_account_id);
                } else {
                    if (isset($credit_code_id)) {
                        $this->_creditcodeFactory->create()->changeCodeStatus($credit_code_id, \Ecommerce\Creditlimit\Model\Source\Status::STATUS_UNUSED);
                        $creditlimitFactory->sendCreditToFriendByEmailAfterVerify($credit_code_id, $amount, $email, $message, $customer_id);
                    } else {
                        $creditlimitFactory->sendCreditToFriendByEmail($amount, $email, $message, $customer_id);
                    }
                }
                $creditlimitFactory->sendSuccessEmail($customer_email, $customer_name, $email, false);
                $this->_customerSession->setData("sentemail", 'no');
                $this->messageManager->addSuccess(__('Credit has been successfully sent to ') . $email);
                $session = $this->_customerSession;
                $session->setVerify(false);
                $session->setEmail(false);
                $session->setValue(false);
                $session->setCreditCodeId(false);
                $session->setDescription(false);
                return $this->_redirect("creditlimit/index/share");
            } else {
                $this->messageManager->addError(__('Invalid verify code. Please check again!'));
                return $this->_redirect("creditlimit/index/share");
            }
        } else {
            $email = $this->getRequest()->getParam('creditlimit_email_input');
            $amount = $this->getRequest()->getParam('creditlimit_value_input');
            $amount = $this->_creditHelper->getConvertedToBaseCustomerCredit($amount);
            $message = $this->getRequest()->getParam('customer-credit-share-message');
            $friend_account_id = $this->_customerFactory->create()->getCollection()
                ->addFieldToFilter('email', $email)
                ->getFirstItem()
                ->getId();
            if ($email == $customer_email) {
                $this->messageManager->addError(__('Invalid email. Please check again!'));
                return $this->_redirect("creditlimit/index/share");
            }
            if ($amount < 0 || $amount > $customer_credit) {
                $this->messageManager->addError(__('Invalid amount. Please check again!'));
                return $this->_redirect("creditlimit/index/share");
            }
            $transactionFactory->addTransactionHistory($customer_id, \Ecommerce\Creditlimit\Model\TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS, $customer_email . __(" sent ") . $amount . __(" credit to ") . $email, "", -$amount);
            $creditlimitFactory->changeCustomerCredit(-$amount);
            if (isset($friend_account_id)) {
                $transactionFactory->addTransactionHistory($friend_account_id, \Ecommerce\Creditlimit\Model\TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS, $email . __(" received ") . $amount . __(" credit from ") . $customer_name, "", $amount);
                $creditlimitFactory->addCreditToFriend($amount, $friend_account_id);
            } else {
                $creditlimitFactory->sendCreditToFriendByEmail($amount, $email, $message, $customer_id);
            }
            $creditlimitFactory->sendSuccessEmail($customer_email, $customer_name, $email, false);
            $this->_customerSession->setData("sentemail", 'no');
            $this->messageManager->addSuccess(__('Credit has been successfully sent to ') . $email);
            $this->_redirect("creditlimit/index/share");
        }
    }
}
