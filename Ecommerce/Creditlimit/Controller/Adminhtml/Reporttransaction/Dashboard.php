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

namespace Ecommerce\Creditlimit\Controller\Adminhtml\Reporttransaction;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Dashboard extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecommerce_Creditlimit::report_chart_credit');
        $resultPage->addBreadcrumb(__('Customer Credit Chart'), __('Customer Credit Report'));
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Credit Report'));
        $this->_addContent($resultPage->getLayout()->createBlock('Ecommerce\Creditlimit\Block\Adminhtml\Report'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommerce_Creditlimit::report_chart_credit');
    }
}