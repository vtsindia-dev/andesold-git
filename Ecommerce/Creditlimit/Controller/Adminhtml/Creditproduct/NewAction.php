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

namespace Ecommerce\Creditlimit\Controller\Adminhtml\Creditproduct;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Class NewAction
 *
 * Credit product new action controller
 */
class NewAction extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommerce_Creditlimit::creditproduct');
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $set = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)->getDefaultAttributeSetId();
        $this->_session->setCreditProductCreate(true);
        return $this->_redirect('catalog/product/new', ['type' => 'creditlimit', 'set' => $set]);
    }
}
