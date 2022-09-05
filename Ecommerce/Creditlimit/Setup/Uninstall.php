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

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /* Remove attribute added */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_type");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_rate");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_value");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_from");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_to");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_dropdown");

        $installer = $setup;
        $installer->startSetup();

        /**
         * Drop tables
         */
        $installer->getConnection()->dropTable($installer->getTable('credit_transaction'));
        $installer->getConnection()->dropTable($installer->getTable('credit_code'));
        $installer->getConnection()->dropTable($installer->getTable('type_transaction'));
        $installer->getConnection()->dropTable($installer->getTable('customer_credit'));

        /**
         * Drop columns
         */
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'base_creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'base_creditlimit_discount_for_shipping');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'creditlimit_discount_for_shipping');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'base_creditlimit_hidden_tax');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'creditlimit_hidden_tax');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'base_creditlimit_shipping_hidden_tax');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'creditlimit_shipping_hidden_tax');

        $setup->getConnection()->dropColumn($setup->getTable('sales_order_item'), 'creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order_item'), 'base_creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order_item'), 'base_creditlimit_hidden_tax');
        $setup->getConnection()->dropColumn($setup->getTable('sales_order_item'), 'creditlimit_hidden_tax');

        $setup->getConnection()->dropColumn($setup->getTable('sales_invoice'), 'creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_invoice'), 'base_creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_invoice'), 'base_creditlimit_hidden_tax');
        $setup->getConnection()->dropColumn($setup->getTable('sales_invoice'), 'creditlimit_hidden_tax');

        $setup->getConnection()->dropColumn($setup->getTable('sales_creditmemo'), 'creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_creditmemo'), 'base_creditlimit_discount');
        $setup->getConnection()->dropColumn($setup->getTable('sales_creditmemo'), 'base_creditlimit_hidden_tax');
        $setup->getConnection()->dropColumn($setup->getTable('sales_creditmemo'), 'creditlimit_hidden_tax');

        $fieldList = [
            'price',
            'minimal_price'
        ];

        // make these attributes applicable to credit products
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if (in_array('creditlimit', $applyTo)) {
                $key = array_search('creditlimit', $applyTo);
                unset($applyTo[$key]);

                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        $installer->endSetup();
    }
}