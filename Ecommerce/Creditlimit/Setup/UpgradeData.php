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

namespace Ecommerce\Creditlimit\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Ecommerce\Creditlimit\Helper\Update
     */
    private $helperUpgrade;

    /**
     * @var array
     */
    protected $credit_product_data;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var []
     */
    protected $_calculators;

    /**
     * @var \Magento\Framework\Math\CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Ecommerce\Creditlimit\Helper\Upgrade $helperUpgrade
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Math\CalculatorFactory $_calculatorFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Ecommerce\Creditlimit\Helper\Upgrade $helperUpgrade,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Math\CalculatorFactory $_calculatorFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\State $appState
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->helperUpgrade = $helperUpgrade;
        $this->orderFactory = $orderFactory;
        $this->_calculatorFactory = $_calculatorFactory;
        $this->productMetadata = $productMetadata;
        $this->_appState = $appState;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $credit_product_data = $this->helperUpgrade->getProductData();
            $this->upgradeVersionOneZeroTwo($setup);
            $this->helperUpgrade->setProductData($credit_product_data);
        }
        if (version_compare($context->getVersion(), '2.1.0.1', '<')) {
            if ($this->helperUpgrade->checkMagentoEE()) {
                $creditTable = $resource->getTableName('customer_credit');
                $balanceTable = $resource->getTableName('magento_customerbalance');
                $connection->query('INSERT INTO ' . $creditTable . '(customer_id, credit_balance) SELECT customer_id, amount
             FROM ' . $balanceTable . ' WHERE customer_id NOT IN (SELECT customer_id FROM ' . $creditTable . ')');
            }
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $version = $this->productMetadata->getVersion();
            try {
                if (version_compare($version, '2.2.0', '>=')) {
                    $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
                } else {
                    $this->_appState->setAreaCode('admin');
                }
            } catch (\Exception $e) {
                $this->_appState->getAreaCode();
            }
            $this->convertOrder($setup);
        }

        if (version_compare($context->getVersion(), '2.2.3', '<')) {
            $attributeArray = [
                'storecredit_type',
                'storecredit_rate',
                'storecredit_value',
                'storecredit_from',
                'storecredit_to',
                'storecredit_dropdown'
            ];
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            foreach ($attributeArray as $attributeCode) {
                $this->disableSearchOfAttribute($attributeCode, $eavSetup);
            }
        }

        if (version_compare($context->getVersion(), '2.2.4', '<')) {
            $select = $connection->select();
            $select->from(
                ['catalog_eav_attribute' => $setup->getTable('catalog_eav_attribute')],
                'attribute_id'
            )->where(
                'attribute_id NOT IN ?',
                $connection->select()->from(
                    ['eav_attribute' => $setup->getTable('eav_attribute')],
                    ['attribute_id']
                )
            )->where(
                'catalog_eav_attribute.apply_to = ?', 'creditlimit'
            );
            $attributeIds = $connection->fetchCol($select);
            if (!empty($attributeIds)) {
                $connection->delete(
                    $setup->getTable('catalog_eav_attribute'),
                    ['attribute_id IN (?)' => $attributeIds]
                );
                $connection->delete(
                    $setup->getTable('eav_entity_attribute'),
                    ['attribute_id IN (?)' => $attributeIds]
                );
            }
        }

        if (version_compare($context->getVersion(), '2.2.5', '<')) {
            $attributeArray = [
                'storecredit_type',
                'storecredit_rate',
                'storecredit_value',
                'storecredit_from',
                'storecredit_to',
                'storecredit_dropdown'
            ];
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            foreach ($attributeArray as $attributeCode) {
                $this->updateAttributeProperties($attributeCode, $eavSetup);
            }
        }
        
        $setup->endSetup();
    }

    /**
     * @param $attributeCode
     * @param $eavSetup
     */
    public function disableSearchOfAttribute($attributeCode, $eavSetup)
    {
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode,
            [
                'is_searchable' => 0,
                'searchable' => false,
            ]
        );
    }

    public function updateAttributeProperties($attributeCode, $eavSetup) {
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode,
            [
                'searchable' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'comparable' => false,
                'wysiwyg_enabled' => false,
                'is_html_allowed_on_front' => false,
                'filterable' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable' => 0,
                'is_filterable' => 0
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeVersionOneZeroTwo($setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /* Prepare before add attribute */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "credit_rate");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_type");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_rate");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_value");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_from");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_to");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_dropdown");

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_type", $this->getAttributeStoreCreditTypeConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_rate", $this->getAttributeStoreCreditRateConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_value",  $this->getAttributeStoreCreditValueConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_from",  $this->getAttributeStoreCreditValueFromConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_to",  $this->getAttributeStoreCreditValueToConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_dropdown",  $this->getAttributeStoreCreditValueDropdownConfig());
    }


    /**
     * Example text field config
     *
     * @return array
     */
    public function getAttributeStoreCreditTypeConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'select';
        $attr['type'] = 'int';
        $attr['label'] = 'Type of Store Credit value';
        $attr['source'] = 'Ecommerce\Creditlimit\Model\Source\Storecredittype';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    public function getAttributeStoreCreditRateConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'text';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Credit Rate';
        $attr['frontend_class'] = 'validate-number';
        $attr['note'] = 'For example: 0.8';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    public function getAttributeStoreCreditValueConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'price';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Store Credit value';
        $attr['frontend_class'] = 'validate-number';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    public function getAttributeStoreCreditValueFromConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'price';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Minimum Store Credit value';
        $attr['frontend_class'] = 'validate-number';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    public function getAttributeStoreCreditValueToConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'price';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Maximum Store Credit value';
        $attr['frontend_class'] = 'validate-number';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    public function getAttributeStoreCreditValueDropdownConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'text';
        $attr['type'] = 'varchar';
        $attr['label'] = 'Store Credit values';
        $attr['note'] = 'Seperated by comma, e.g. 10,20,30';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    public function getAttributeDefaultConfig()
    {
        return [
            'group' => 'Credit Prices Settings',
            'sort_order' => 1,
            'backend' => '',
            'frontend' => '',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'searchable' => false,
            'visible_in_advanced_search' => true,
            'used_in_product_listing' => true,
            'used_for_sort_by' => true,
            'comparable' => true,
            'wysiwyg_enabled' => true,
            'is_html_allowed_on_front' => true,
            'filterable' => true,
            'apply_to' => 'creditlimit',
            'position' => 4,
            'required' => false,
            'user_defined' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => true,
            'visible_on_front' => true,
            'visible' => true,
            'is_searchable' => 0
        ];
    }



    public function convertOrder(ModuleDataSetupInterface $setup)
    {
        $orderTable = $setup->getTable('sales_order');
        $select = $setup->getConnection()->select();
        $select->from(['main_table' => $orderTable], ['entity_id'])
            ->where('base_creditlimit_discount > ?', 0);
        $data = $setup->getConnection()->fetchAll($select);
        foreach ($data as $item) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create()->load($item['entity_id']);
            $orderItems = $order->getAllItems();
            $store = $order->getStore();
            $totalItemsBaseCreditlimitDiscountInvoiced = $totalItemsCreditlimitDiscountInvoiced
                = $totalItemsBaseCreditlimitDiscountRefunded = $totalItemsCreditlimitDiscountRefunded = 0;
            foreach ($orderItems as $orderItem) {
                $qtyOrdered = $orderItem->getQtyOrdered();
                $qtyInvoiced = $orderItem->getQtyInvoiced();
                $qtyRefunded = $orderItem->getQtyRefunded();
                $baseCreditlimitDiscount = $this->roundPrice($orderItem->getBaseCreditlimitDiscount(), true, $store);
                $creditlimitDiscount = $this->roundPrice($orderItem->getCreditlimitDiscount(), true, $store);
                $baseDiscountAmount = $orderItem->getBaseDiscountAmount();
                $discountAmount = $orderItem->getDiscountAmount();
                $baseDiscountInvoiced = $orderItem->getBaseDiscountInvoiced();
                $discountInvoiced = $orderItem->getDiscountInvoiced();
                $baseDiscountRefunded = $orderItem->getBaseDiscountRefunded() ? $orderItem->getBaseDiscountRefunded() : 0;
                $discountRefunded = $orderItem->getDiscountRefunded() ? $orderItem->getDiscountRefunded() : 0;
                $baseCreditlimitDiscountInvoiced = $baseCreditlimitDiscount / $qtyOrdered * $qtyInvoiced;
                $creditlimitDiscountInvoiced = $creditlimitDiscount / $qtyOrdered * $qtyInvoiced;
                $baseCreditlimitDiscountRefunded = $baseCreditlimitDiscount / $qtyOrdered * $qtyRefunded;
                $creditlimitDiscountRefunded = $creditlimitDiscount / $qtyOrdered * $qtyRefunded;
                $orderItem->setBaseDiscountAmount(
                    $baseDiscountAmount + $this->roundPrice($baseCreditlimitDiscount, true, $store)
                );
                $orderItem->setDiscountAmount(
                    $discountAmount + $this->roundPrice($creditlimitDiscount, true, $store)
                );
                $orderItem->setBaseDiscountInvoiced(
                    $baseDiscountInvoiced + $this->roundPrice($baseCreditlimitDiscountInvoiced, true, $store)
                );
                $orderItem->setDiscountInvoiced(
                    $discountInvoiced + $this->roundPrice($creditlimitDiscountInvoiced, true, $store)
                );
                $orderItem->setBaseDiscountRefunded(
                    $baseDiscountRefunded + $this->roundPrice($baseCreditlimitDiscountRefunded, true, $store)
                );
                $orderItem->setBaseDiscountRefunded(
                    $discountRefunded + $this->roundPrice($creditlimitDiscountRefunded, true, $store)
                );
                $orderItem->setEcommerceBaseDiscount($orderItem->getEcommerceBaseDiscount() + $baseCreditlimitDiscount);
                $orderItem->setEcommerceDiscount($orderItem->getEcommerceDiscount() + $creditlimitDiscount);
                $totalItemsBaseCreditlimitDiscountInvoiced += $baseCreditlimitDiscountInvoiced;
                $totalItemsCreditlimitDiscountInvoiced += $creditlimitDiscountInvoiced;
                $totalItemsBaseCreditlimitDiscountRefunded += $baseCreditlimitDiscountRefunded;
                $totalItemsCreditlimitDiscountRefunded += $creditlimitDiscountRefunded;
                $orderItem->save();
            }
            $baseDiscountAmount = $order->getBaseDiscountAmount();
            $discountAmount = $order->getDiscountAmount();
            $baseDiscountInvoiced = $order->getBaseDiscountInvoiced();
            $discountInvoiced = $order->getDiscountInvoiced();
            $baseDiscountRefunded = $order->getBaseDiscountRefunded() ? $order->getBaseDiscountRefunded() : 0;
            $discountRefunded = $order->getDiscountRefunded() ? $order->getDiscountRefunded() : 0;
            $baseCreditlimitDiscount = $order->getBaseCreditlimitDiscount();
            $creditlimitDiscount = $order->getCreditlimitDiscount();
            $order->setBaseDiscountAmount($baseDiscountAmount - $baseCreditlimitDiscount);
            $order->setDiscountAmount($discountAmount - $creditlimitDiscount);
            $order->setBaseDiscountInvoiced($baseDiscountInvoiced - $this->roundPrice($totalItemsBaseCreditlimitDiscountInvoiced, true, $store));
            $order->setDiscountInvoiced($discountInvoiced - $this->roundPrice($totalItemsCreditlimitDiscountInvoiced, true, $store));
            $order->setBaseDiscountRefunded($baseDiscountRefunded - $this->roundPrice($totalItemsBaseCreditlimitDiscountRefunded, true, $store));
            $order->setDiscountRefunded($discountRefunded - $this->roundPrice($totalItemsCreditlimitDiscountRefunded, true, $store));
            $order->save();
        }
    }


    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     * @param bool $negative Indicates if we perform addition (true) or subtraction (false) of rounded value
     * @return float
     */
    public function roundPrice($price, $negative = false, $store)
    {
        $store->getStoreId();
        if ($price) {
            if (!isset($this->_calculators[$store->getStoreId()])) {
                $this->_calculators[$store->getStoreId()] = $this->_calculatorFactory->create(['scope' => $store]);
            }
            $price = $this->_calculators[$store->getStoreId()]->deltaRound($price, $negative);
        }
        return $price;
    }
}
