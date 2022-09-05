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

namespace Ecommerce\Creditlimit\Controller\Checkout;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Store\Model\StoreManagerInterface;
use Ecommerce\Creditlimit\Block\Payment\Form;
use Ecommerce\Creditlimit\Helper\Data;
use Ecommerce\Creditlimit\Model\CreditlimitFactory;
use Zend_Json;

/**
 * Class AmountPost
 *
 * Checkout Amount Post controller
 */
class AmountPost extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * @var CreditlimitFactory
     */
    protected $_creditlimit;
    /**
     * @var Data
     */
    protected $_creditlimitHelper;

    /**
     * AmountPost constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param Cart $cart
     * @param CreditlimitFactory $creditlimit
     * @param Data $creditlimitHelper
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        Cart $cart,
        CreditlimitFactory $creditlimit,
        Data $creditlimitHelper
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->_creditlimit = $creditlimit;
        $this->_creditlimitHelper = $creditlimitHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $quote = $this->_checkoutSession->getQuote();
        if ($request->getParam('customer_credit') >= 0 && is_numeric($request->getParam('customer_credit'))) {
            $creditAmount = $request->getParam('customer_credit');
            $baseCreditAmount = $this->_creditlimitHelper->getConvertedToBaseCustomerCredit($creditAmount);

            $customer = $this->_creditlimitHelper->getCustomer();
            $customerId = $customer->getId();
            $customer_credit = $this->_creditlimit->create()->load($customerId, 'customer_id');
            $creditBalance = $customer_credit->getCreditBalance();

            $creditAmount = min($baseCreditAmount, $creditBalance);

            /** integration with gift card and reward points */
            $quote->setCustomerCreditAmount($creditAmount);
            $quote->save();
            return $this->_goBack();
        }

        if (is_numeric($request->getParam('credit_amount')) && $request->getParam('credit_amount') >= 0) {
            $creditAmount = $request->getParam('credit_amount');
            $baseCreditAmount = $this->_creditlimitHelper->getConvertedToBaseCustomerCredit($creditAmount);

            $customer = $this->_creditlimitHelper->getCustomer();
            $customerId = $customer->getId();
            $customer_credit = $this->_creditlimit->create()->load($customerId, 'customer_id');
            $creditBalance = $customer_credit->getCreditBalance();

            $creditAmount = min($baseCreditAmount, $creditBalance);
            $quote->setCustomerCreditAmount($creditAmount);
            $quote->setCreditdiscountAmount($creditAmount);

            $result = $this->_objectManager->create(Form::class)
                ->getCreditlimitData();
            $quote->save();
            $result['credit_discount'] = $quote->getCreditdiscountAmount();
            return $this->getResponse()->setBody(Zend_Json::encode($result));
        }

        if (!$request->getParam('customer_credit') && !$request->getParam('credit_amount')) {
            $quote->setCustomerCreditAmount(0);
            $quote->save();
            return $this->_goBack();
        }

        return $this->_goBack();
    }
}
