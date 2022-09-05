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

namespace Ecommerce\Creditlimit\Block\Adminhtml;

class Maxbalance extends \Magento\Backend\Block\Template
{
    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ecommerce\Creditlimit\Helper\Data $creditHelper
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ecommerce\Creditlimit\Helper\Data $creditHelper,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    )
    {
        $this->_creditHelper = $creditHelper;
        $this->_localeCurrency = $localeCurrency;
        $this->_priceCurrency = $priceCurrency;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('creditlimit/maxbalance.phtml');
        $this->setDefaultLimit(5);
    }

//    public function _prepareLayout()
//    {
//        return parent::_prepareLayout();
//    }

    public function getTopFiveCustomerMaxCreditBalan()
    {
        return $this->_creditHelper->topFiveCustomerMaxCredit();
    }

    public function getLocaleCurrency()
    {
        return $this->_localeCurrency;
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getCreditHelper()
    {
        return $this->_creditHelper;
    }


    public function getPriceCurrency()
    {
        return $this->_priceCurrency;
    }
}
