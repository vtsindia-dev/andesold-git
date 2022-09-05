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

namespace Ecommerce\Creditlimit\Helper;

use Magento\Framework\App\Area;

class Account extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\App\State $appState
    )
    {
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_sessionQuote = $sessionQuote;
        $this->_appState = $appState;
        parent::__construct($context);
    }

    public function getNavigationLabel()
    {
        return __('Store Credit');
    }

    public function getDashboardLabel()
    {
        return __('Account Dashboard');
    }

    public function getSendCreditLabel()
    {
        return __('Send Credit');
    }

    public function getRedeemCreditLabel()
    {
        return __('Redeem Credit');
    }

    public function accountNotLogin()
    {
        return !$this->isLoggedIn();
    }

    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /* Always return true because remove the settings "Group can use credit" */
    public function customerGroupCheck()
    {
        return true;
    }
}
