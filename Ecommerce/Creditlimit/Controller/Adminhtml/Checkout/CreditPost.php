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

namespace Ecommerce\Creditlimit\Controller\Adminhtml\Checkout;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Result\PageFactory;
use Ecommerce\Creditlimit\Model\CreditlimitFactory;

/**
 * Class CreditPost
 *
 * Checkout credit post controller
 */
class CreditPost extends Action implements HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var Session $checkoutSession
     */
    protected $_checkoutSession;

    /**
     * @var CreditlimitFactory
     */
    protected $_creditModel;

    /**
     * @var Quote $sessionQuote
     */
    protected $_sessionQuote;
    /**
     * @var Data
     */
    protected $_helperJson;

    /**
     * CreditPost constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Quote $sessionQuote
     * @param Session $checkoutSession
     * @param Data $helperJson
     * @param PriceCurrencyInterface $priceCurrency
     * @param CreditlimitFactory $creditlimitFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Quote $sessionQuote,
        Session $checkoutSession,
        Data $helperJson,
        PriceCurrencyInterface $priceCurrency,
        CreditlimitFactory $creditlimitFactory
    ) {
        parent::__construct($context);
        $this->_sessionQuote = $sessionQuote;
        $this->_checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_helperJson = $helperJson;
        $this->priceCurrency = $priceCurrency;
        $this->_creditModel = $creditlimitFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $quote = $this->_sessionQuote->getQuote();
        $result = [];

        $customer_id = $this->_sessionQuote->getCustomerId();
        $credit_available = $this->_creditModel->create()->load($customer_id, 'customer_id')->getCreditBalance();

        if ($request->isPost()) {
            $creditvalue = $request->getParam('credit_value');
            if ($creditvalue <= $credit_available) {
                $quote->setCustomerCreditAmountEntered($creditvalue);
                $creditvalue = $creditvalue / $this->priceCurrency->convert(1, false, false);
                if ($creditvalue < 0.0001) {
                    $creditvalue = 0;
                }
                $quote->setCustomerCreditAmount($creditvalue);
                $quote->setUseCustomerCredit(true);
                $quote->save();
                $result['creditvalue'] = $quote->getCreditdiscountAmount();
            }
        }
        return $this->getResponse()->setBody($this->_helperJson->jsonEncode($result));
    }
}
