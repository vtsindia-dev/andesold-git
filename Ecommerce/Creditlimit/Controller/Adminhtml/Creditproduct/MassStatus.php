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
 * Class MassStatus
 *
 * Credit product mass status controller
 */
class MassStatus extends \Magento\Backend\App\Action implements HttpPostActionInterface
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
     * Update product(s) status action
     */
    public function execute()
    {
        $productIds = (array)$this->getRequest()->getParam('product');
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $status = (int)$this->getRequest()->getParam('status');

        try {
            $this->_validateMassStatus($productIds, $status);
            $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                ->updateAttributes($productIds, ['status' => $status], $storeId);
            $this->messageManager->addSuccess(
                sprintf(__('Total of %d record(s) have been updated.'), count($productIds))
            );
            $this->_objectManager->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::class)
                ->reindexList($productIds);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while updating the product(s) status.'));
        }

        $this->_redirect('*/*/', ['store' => $storeId]);
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create(\Magento\Catalog\Model\Product::class)->isProductsHasSku($productIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
    }
}
