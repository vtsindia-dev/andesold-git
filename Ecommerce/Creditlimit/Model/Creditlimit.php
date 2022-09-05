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

namespace Ecommerce\Creditlimit\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Creditlimit
 *
 * Customer credit model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Creditlimit extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_creditlimitHelper;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Magento\Framework\Url
     */
    protected $_urlBuilder;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditcodeFactory
     */
    protected $_creditcode;

    /**
     * @var \Ecommerce\Creditlimit\Helper\Sendmail
     */
    protected $_helperSendmail;

    /**
     * Creditlimit constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ecommerce\Creditlimit\Helper\Data $creditlimitHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Url $urlBuilder
     * @param CreditcodeFactory $creditcode
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ecommerce\Creditlimit\Helper\Sendmail $helperSendmail
     * @param ResourceModel\Creditlimit $resource
     * @param ResourceModel\Creditlimit\Collection $resourceCollection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ecommerce\Creditlimit\Helper\Data $creditlimitHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Url $urlBuilder,
        \Ecommerce\Creditlimit\Model\CreditcodeFactory $creditcode,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ecommerce\Creditlimit\Helper\Sendmail $helperSendmail,
        \Ecommerce\Creditlimit\Model\ResourceModel\Creditlimit $resource,
        \Ecommerce\Creditlimit\Model\ResourceModel\Creditlimit\Collection $resourceCollection
    ) {
        $this->_storeManager = $storeManager;
        $this->_creditlimitHelper = $creditlimitHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_priceCurrency = $priceCurrency;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_creditcode = $creditcode;
        $this->messageManager = $messageManager;
        $this->_helperSendmail = $helperSendmail;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Ecommerce\Creditlimit\Model\ResourceModel\Creditlimit::class);
        $this->setIdFieldName('credit_id');
    }

    /**
     * Get Credit By Customer Id
     *
     * @param int $customerId
     * @return \Magento\Framework\DataObject
     */
    public function getCreditByCustomerId($customerId)
    {
        $collection = $this->getCollection()->addFieldToFilter('customer_id', $customerId);

        if ($collection->getSize()) {
            $id = $collection->getFirstItem()->getId();
            $this->load($id);
        }
        return $collection->getFirstItem();
    }

    /**
     * Change Customer Credit
     *
     * @param float $credit_amount
     * @param null|int $customer_id
     */
    public function changeCustomerCredit($credit_amount, $customer_id = null)
    {
        if ($customer_id == null) {
            $customer_id = $this->_creditlimitHelper->getCustomer()->getId();
        }
        $customer = $this->getCreditByCustomerId($customer_id);
        $begin_balance = $customer->getCreditBalance();

        try {
            $customer->setCustomerId($customer_id)->setCreditBalance($begin_balance + $credit_amount);
            $customer->setUpdatedAt(date("Y-m-d H:i:s"));
            $customer->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }

    /**
     * Add Credit To Friend
     *
     * @param float $credit_amount
     * @param int $customer_id
     */
    public function addCreditToFriend($credit_amount, $customer_id)
    {
        if ($customer_id != null) {
            $friend_account = $this->getCreditByCustomerId($customer_id);
            $friend_credit_balance = $friend_account->getCreditBalance() + $credit_amount;
            $friend_account->setCustomerId($customer_id)->setCreditBalance($friend_credit_balance);
            $friend_account->setUpdatedAt(date("Y-m-d H:i:s"));
            try {
                $friend_account->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
    }

    /**
     * Send Credit To Friend By Email
     *
     * @param float $credit_amount
     * @param string $friend_email
     * @param string $message
     * @param null|int $customer_id
     * @param null|string $customer_name
     */
    public function sendCreditToFriendByEmail(
        $credit_amount,
        $friend_email,
        $message,
        $customer_id = null,
        $customer_name = null
    ) {
        $credit_code = $this->_creditcode->create();
        $code = $credit_code->addCreditCode(
            $friend_email,
            $credit_amount,
            \Ecommerce\Creditlimit\Model\Source\Status::STATUS_UNUSED,
            $customer_id
        );
        $this->sendCodeToFriendEmail(
            $friend_email,
            $credit_amount,
            $message,
            $code,
            $customer_id = null,
            $customer_name
        );
    }

    /**
     * Send Credit To Friend By Email After Verify
     *
     * @param int $credit_code_id
     * @param float $credit_amount
     * @param string $friend_email
     * @param string $message
     * @param null|int $customer_id
     */
    public function sendCreditToFriendByEmailAfterVerify(
        $credit_code_id,
        $credit_amount,
        $friend_email,
        $message,
        $customer_id = null
    ) {
        $credit_code = $this->_creditcode->create()->load($credit_code_id);
        if (isset($credit_code) && isset($friend_email) && isset($credit_amount)) {
            $this->sendCodeToFriendEmail(
                $friend_email,
                $credit_amount,
                $message,
                $credit_code->getCreditCode(),
                $customer_id
            );
        }
    }

    /**
     * Send Verify Email
     *
     * @param string $email
     * @param float $value
     * @param string $message
     * @param string $keycode
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendVerifyEmail($email, $value, $message, $keycode)
    {
        $customer_id = $this->_customerSession->getCustomerId();
        $customerData = $this->_customerFactory->create()->load($customer_id);
        $send_name = $customerData->getFirstname() . " " . $customerData->getLastname();
        $store = $this->_storeManager->getStore($this->getStoreId());
        $share_url = $this->_urlBuilder->getUrl('creditlimit/index/validateCustomer');
        $veriryurl = $this->_urlBuilder->getUrl('creditlimit/index/sharepost?keycode=' . $keycode);
        $veriryurl = substr($veriryurl, 0, strlen($veriryurl) - 1);
        if ($rate = $this->_storeManager->getStore()->getCurrentCurrencyRate()) {
            $value = $value / $rate;
        }
        try {
            $emailTemplateVariables = [
                'store' => $store,
                'recipient_email' => $email,
                'value' => $this->_creditlimitHelper->getFormatAmount($value),
                'send_name' => $send_name,
                'message' => $message,
                'emailcode' => $keycode,
                'verifyurl' => $veriryurl,
                'share_url' => $share_url,
            ];

            $receiverInfo = [
                'email' => $customerData->getEmail(),
                'name' => $customerData->getName()
            ];

            $this->_helperSendmail->Send('creditlimit/email/verify', $emailTemplateVariables, $receiverInfo);

        } catch (\Magento\Framework\Exception\MailException $ex) {
            $this->messageManager->addError($ex->getMessage());
            return $this;
        }
        return $this;
    }

    /**
     * Send Code To Friend Email
     *
     * @param string $email
     * @param float $amount
     * @param string $message
     * @param string $creditcode
     * @param null|int $customer_id
     * @param null|string $customer_name
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendCodeToFriendEmail(
        $email,
        $amount,
        $message,
        $creditcode,
        $customer_id = null,
        $customer_name = null
    ) {
        if ($customer_id) {
            $customerData = $this->_customerFactory->create()->load($customer_id);
        } else {
            $customerData = $this->_creditlimitHelper->getCustomer();
        }

        $receiver_name = $this->_creditlimitHelper->getNameCustomerByEmail($email);
        $send_name = $customer_name;

        if (trim($send_name) == "" && $customerData->getId() != null) {
            $send_name = $customerData->getFirstname() . " " . $customerData->getLastname();
        }

        /** @var \Magento\Framework\UrlInterface $urlInterface */
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\UrlInterface::class);
        $urlInterface->getCurrentUrl();

        $store = $this->_storeManager->getStore($this->getStoreId());
        $login_url = $this->_urlBuilder->getUrl('customer/account/login');
        $redeem_page_url = $this->_urlBuilder->getUrl('creditlimit/index/redeem');
        $redeemurl = $this->_urlBuilder->getUrl('creditlimit/index/redeem?code=' . $creditcode);
        $redeemurl = substr($redeemurl, 0, strlen($redeemurl) - 1);
        try {

            $emailTemplateVariables = [
                'store' => $store,
                'send_name' => $send_name,
                'receiver_name' => $receiver_name,
                'value' => $amount,
                'login_url' => $login_url,
                'redeem_page_url' => $redeem_page_url,
                'message' => $message,
                'creditcode' => $creditcode,
                'redeemurl' => $redeemurl,
            ];

            $receiverInfo = [
                'email' => $email,
                'name' => $receiver_name
            ];

            $this->_helperSendmail->Send('creditlimit/email/creditcode', $emailTemplateVariables, $receiverInfo);

        } catch (\Magento\Framework\Exception\MailException $ex) {
            $this->_logger->info($ex->getMessage());
        }
        return $this;
    }

    /**
     * Send Notify to Customer
     *
     * @param string $email
     * @param string $name
     * @param float $credit_value
     * @param float $balance
     * @param string $message
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendNotifytoCustomer($email, $name, $credit_value, $balance, $message)
    {
        $store = $this->_storeManager->getStore($this->getStoreId());
        $storeurl = $store->getBaseUrl();
        if ($credit_value > 0) {
            $emailTemplateVariables = [
                'store' => $store,
                'storeurl' => $storeurl,
                'receiver_name' => $name,
                'value' => $this->_creditlimitHelper->getFormatAmount($credit_value),
                'message' => $message,
                'balance' => $this->_creditlimitHelper->getFormatAmount($balance),
                'addcredit' => true,
            ];
        } else {
            $emailTemplateVariables = [
                'store' => $store,
                'storeurl' => $storeurl,
                'receiver_name' => $name,
                'value' => $this->_creditlimitHelper->getFormatAmount($credit_value),
                'message' => $message,
                'balance' => $this->_creditlimitHelper->getFormatAmount($balance),
                'deductcredit' => true,
            ];
        }
        try {
            $receiverInfo = [
                'email' => $email,
                'name' => $name
            ];

            $this->_helperSendmail->Send('creditlimit/email/notify', $emailTemplateVariables, $receiverInfo);

        } catch (\Magento\Framework\Exception\MailException $ex) {
            $this->messageManager->addError($ex->getMessage());
        }
        return $this;
    }

    /**
     * Send Success Email
     *
     * @param string $email
     * @param string $customer
     * @param string $receivename
     * @param bool $check
     * @return $this
     */
    public function sendSuccessEmail($email, $customer, $receivename, $check)
    {
        try {
            $emailTemplateVariables = [
                'receivename' => $receivename,
                'name' => $customer,
                'buycreditproduct' => $check,
            ];

            $receiverInfo = [
                'email' => $email,
                'name' => $customer
            ];

            $this->_helperSendmail->Send('creditlimit/email/notify_success', $emailTemplateVariables, $receiverInfo);

        } catch (\Magento\Framework\Exception\MailException $ex) {
            $this->messageManager->addError($ex->getMessage());
        }
        return $this;
    }
}
