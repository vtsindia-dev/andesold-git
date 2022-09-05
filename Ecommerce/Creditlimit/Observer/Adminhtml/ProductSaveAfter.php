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

namespace Ecommerce\Creditlimit\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_request;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    protected $_responseFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_request = $request;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $type = $this->_request->getParam('type');
        $redirectBack = $this->_request->getParam('back', false);
        if($type == 'creditlimit' && $redirectBack == false){
            $RedirectUrl= $this->_url->getUrl('creditlimitadmin/creditproduct/');
            $this->_responseFactory->create()->setRedirect($RedirectUrl)->sendResponse();
//            die();
            return false;
        }

        return $this;
    }
}
