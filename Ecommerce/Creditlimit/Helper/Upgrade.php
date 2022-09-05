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

use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Upgrade
 *
 * Customer credit upgrade helper
 */
class Upgrade extends AbstractHelper
{
    const MAGENTO_EE = 'Enterprise';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Upgrade constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->productMetadata = $productMetadata;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        try {
            $state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            $state->getAreaCode();
        }
        parent::__construct($context);
    }

    /**
     * Get product data
     *
     * @return array
     */
    public function getProductData()
    {
        $data = [];
        /* @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addFilter('type_id', 'creditlimit');
        foreach ($collection->getItems() as $item) {
            $id = $item->getEntityId();
            $product = $this->productFactory->create()->load($id);

            $data[$id]['storecredit_rate'] = $product->getCreditRate();
            $data[$id]['storecredit_value'] = $product->getStorecreditValue();
            $data[$id]['storecredit_from'] = $product->getStorecreditFrom();
            $data[$id]['storecredit_to'] = $product->getStorecreditTo();
            $data[$id]['storecredit_dropdown'] = $product->getStorecreditDropdown();
            $data[$id]['storecredit_type'] = $product->getStorecreditType();
        }
        return $data;
    }

    /**
     * Set Product Data
     *
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function setProductData($data)
    {
        /* @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addFilter('type_id', 'creditlimit');
        foreach ($collection->getItems() as $item) {
            $id = $item->getEntityId();
            $data_set = $data[$id];

            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productFactory->create()->load($id);

            $attributes = $item->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getAttributeCode() == 'storecredit_type') {
                    $this->xlog(__LINE__.' '.__METHOD__);
                    $this->xlog($attribute->getAttributeCode());
                    $this->xlog($attribute->getAttributeId());
                }
            }

            $product->addData($data_set);
            $product->save();
            $this->xlog(__LINE__.' '.__METHOD__);
            $this->xlog($product->getData('storecredit_type'));
        }

        return $this;
    }

    /**
     * Xlog
     *
     * @param  string|array $message
     * @return void
     */
    public function xlog($message = 'null')
    {
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)
            ->debug($message);
    }

    /**
     * Check Magento EE
     *
     * @return string
     */
    public function checkMagentoEE()
    {
        $edition = $this->productMetadata->getEdition();
        if ($edition == self::MAGENTO_EE) {
            return true;
        } else {
            return false;
        }
    }
}
