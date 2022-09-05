<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommerce\CreditlimitGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Ecommerce\Creditlimit\Model\Product\Type;

/**
 * @inheritdoc
 */
class StoreCreditProductTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['type_id']) && $data['type_id'] == Type::TYPE_CODE) {
            return 'StoreCreditProduct';
        }
        return '';
    }
}
