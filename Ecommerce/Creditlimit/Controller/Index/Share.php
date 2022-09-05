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

class Share extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $resultPageFactory;
    /**
     * @var \Ecommerce\Creditlimit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Ecommerce\Creditlimit\Helper\Account $accountHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ecommerce\Creditlimit\Helper\Account $accountHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->_accountHelper = $accountHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->_accountHelper->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Share Credit'));
        return $resultPage;
    }
}
