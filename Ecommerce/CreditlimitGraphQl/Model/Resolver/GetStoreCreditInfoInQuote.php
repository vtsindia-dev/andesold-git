<?php
/**
 * Copyright © Ecommerce, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommerce\CreditlimitGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Ecommerce\Creditlimit\Helper\Creditproduct as StoreCreditConfig;

/**
 * Get store credit's info in quote
 */
class GetStoreCreditInfoInQuote implements ResolverInterface
{
    const XML_ENABLE = 'enable';

    /**
     * @var StoreCreditConfig
     */
    private $config;

    /**
     * GetStoreCreditInfoInQuote constructor.
     *
     * @param StoreCreditConfig $config
     */
    public function __construct(
        StoreCreditConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $currentStore = $context->getExtensionAttributes()->getStore();

        if (!(int) $this->config->getGeneralConfig(self::XML_ENABLE, $currentStore->getId())) {
            return 0;
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Quote $quote */
        $quote = $value['model'];

        return [
            'use_customer_credit' => (int) $quote->getData('use_customer_credit'),
            'customer_credit_amount' => (int) $quote->getData('customer_credit_amount'),
            'customer_credit_amount_entered' => (int) $quote->getData('customer_credit_amount_entered'),
            'creditdiscount_amount' => (float) $quote->getData('creditdiscount_amount'),
            'base_creditdiscount_amount' => (float) $quote->getData('base_creditdiscount_amount'),
            'creditlimit_discount' => (float) $quote->getData('creditlimit_discount'),
            'base_creditlimit_discount' => (float) $quote->getData('base_creditlimit_discount'),
            'creditlimit_discount_for_shipping' => (float) $quote->getData('creditlimit_discount_for_shipping'),
            'base_creditlimit_discount_for_shipping' =>
                (float) $quote->getData('base_creditlimit_discount_for_shipping')
        ];
    }
}
