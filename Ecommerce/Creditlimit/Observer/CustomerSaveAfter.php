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
 */

namespace Ecommerce\Creditlimit\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Customer save after observer
 */
class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_creditlimitFactory;
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * CustomerSaveAfter Construct
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_request = $request;
        $this->messageManager = $messageManager;
        $this->_creditlimitFactory = $creditlimitFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_objectManager = $objectManager;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
    }

    /**
     * Predispath admin action controller
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer->getId()) {
            return $this;
        }

        $customer_id = $customer->getId();

        $credit_value = $this->_request->getParam('credit_value');
        if (strpos($credit_value, ',')!==false) {
            $credit_value = str_replace(',', '.', $credit_value);
        }
        $description = $this->_request->getParam('description');
        $customerData = $this->_request->getParam('customer');
        $customer_group = $customerData['group_id'];
        $sign = substr($credit_value, 0, 1);
        
        if (!$credit_value) {
            return $this->getReturn();
        }
        $transaction = $this->_transactionFactory->create();
        $creditlimit = $this->_creditlimitFactory->create()->load($customer_id, 'customer_id');

        if ($sign == "-") {
            $end_credit = $creditlimit->getCreditBalance() - substr($credit_value, 1, strlen($credit_value));
            if ($end_credit < 0) {
                $end_credit = 0;
                $credit_value = -$creditlimit->getCreditBalance();
            }
        } else {
            $end_credit = $creditlimit->getCreditBalance() + $credit_value;
        }
        
        $transactionCreditData = [
            'customer_id' => $customer_id,
            'type_transaction_id' => 1,
            'detail_transaction' => $description,
            'received_credit' => $credit_value,
            'amount_credit' => $credit_value,
            'end_balance' => $end_credit,
            'transaction_time' => date("Y-m-d H:i:s"),
            'customer_group_ids' => $customer_group
        ];
        $transaction->setData($transactionCreditData);

        try {
            if (!$creditlimit->getCustomerId()) {
                $creditlimit->setCustomerId($customer_id);
            }

            $creditlimit->setCreditBalance($end_credit)->save();
            $transaction->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $sendemail = $this->_request->getParam('send_mail');
        if ($sendemail == 1) {
            $email = $customer->getEmail();
            $name = $customer->getLastname();
            $balance = $creditlimit->getCreditBalance();
            $message = $this->_request->getParam('description');
            $creditlimit->sendNotifytoCustomer($email, $name, $credit_value, $balance, $message);
        }

        return $this->getReturn();
    }

    /**
     * Fix param to return customer credit page
     *
     * @return $this
     */
    public function getReturn()
    {
        $type = $this->_request->getParam('type');
        $redirectBack = $this->_request->getParam('back', false);
        if ($type == 'creditlimit' && $redirectBack == false) {
            $RedirectUrl = $this->_url->getUrl('creditlimitadmin/creditproduct/');
            $this->_responseFactory->create()->setRedirect($RedirectUrl)->sendResponse();
        }
        return $this;
    }
}
