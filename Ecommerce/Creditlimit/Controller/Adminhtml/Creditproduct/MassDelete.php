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

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class MassDelete
 *
 * Credit product mass delete controller
 */
class MassDelete extends \Magento\Backend\App\Action implements HttpPostActionInterface
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
        $productIds = $this->getRequest()->getParam('product');
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        if (!is_array($productIds) || empty($productIds)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
                foreach ($productIds as $productId) {
                    $product = $this->_objectManager->get(\Magento\Catalog\Model\Product::class)->load($productId);
                    $product->delete();
                }
                $this->messageManager->addSuccess(
                    sprintf(__('Total of %d record(s) have been deleted.'), count($productIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->_redirect('*/*/', ['store' => $storeId]);
    }
}
