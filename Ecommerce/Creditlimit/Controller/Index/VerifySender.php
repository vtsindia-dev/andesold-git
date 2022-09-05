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

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class VerifySender
 *
 * Verify sender controller
 */
class VerifySender extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customer;
    /**
     * @var \Ecommerce\Creditlimit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_creditlimit;

    /**
     * VerifySender constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Ecommerce\Creditlimit\Helper\Account $accountHelper
     * @param \Ecommerce\Creditlimit\Helper\Data $creditHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimit
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Ecommerce\Creditlimit\Helper\Account $accountHelper,
        \Ecommerce\Creditlimit\Helper\Data $creditHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimit
    ) {
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
        $this->_accountHelper = $accountHelper;
        $this->_creditHelper = $creditHelper;
        $this->_storeManager = $storeManager;
        $this->_creditlimit = $creditlimit;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $session = $this->_customerSession;
        if (!$this->_accountHelper->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        if ($this->_creditHelper->getGeneralConfig('validate', null) == 0) {
            $this->_redirect("creditlimit/index/share");
        }
        $sender_id = $this->_customerSession->getCustomerId();
        $customer = $this->_customer->create();
        $sender_email = $customer->load($sender_id)->getEmail();
        $id = $this->getRequest()->getParam('id');
        $email = $this->getRequest()->getParam('creditlimit_email_input');
        $value = $this->getRequest()->getParam('creditlimit_value_input');
        $description = $this->getRequest()->getParam('customer-credit-share-message');
        $session->setEmail($email);
        $session->setValue($value);
        $session->setCreditCodeId($id);
        $session->setDescription($description);
        if (isset($id) && ($email) && isset($value)) {
            $session->setData("sentemail", 'yes');
            $ran_num = rand(1, 1000000);
            $keycode = sha1(sha1(sha1($ran_num)));
            $session->setData("emailcode", $keycode);
            $this->_creditlimit->create()->sendVerifyEmail($email, $value, null, $keycode);
        }
        $session->setVerify(true);
        $this->messageManager->addSuccess(
            __(
                "A verification code has been sent to <a href=\"mailto: %1\"><b>your email</b></a>."
                . " Now, please check your email and verify your credit sending!",
                $sender_email
            )
        );
        return $this->_redirect('*/*/share');
    }
}
