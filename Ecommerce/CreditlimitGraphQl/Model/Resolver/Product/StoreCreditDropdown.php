<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommerce\CreditlimitGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Ecommerce\Creditlimit\Model\Product\Type;

/**
 * Post formatting for data in the store credit dropdown
 */
class StoreCreditDropdown implements ResolverInterface
{
    /**
     * @inheritDoc
     *
     * Add formatting for the giftcard dropdown
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];

        if ($product->getTypeId() === Type::TYPE_CODE) {
            if ($product->getStorecreditDropdown()) {
                return array_map('floatval', explode(',', $product->getStorecreditDropdown()));
            } else {
                return [];
            }
        }

        return [];
    }
}
