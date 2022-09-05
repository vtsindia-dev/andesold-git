<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommerce\CreditlimitGraphQl\Model\Resolver;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Ecommerce\Creditlimit\Helper\Data as CreditlimitConfig;
use Ecommerce\Creditlimit\Model\CreditlimitFactory;

/**
 * Fetch customer reward points
 */
class CustomerStoreCreditBalance implements ResolverInterface
{
    const XML_ENABLE = 'enable';
    const MS_STORE_CREDIT_BALANCE = 'ms_store_credit_balance';
    const CUSTOMER_ID = 'customer_id';

    /**
     * @var CreditlimitConfig
     */
    private $config;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CreditlimitFactory
     */
    private $customerCreditFactory;

    /**
     * CustomerStoreCreditBalance constructor.
     * @param CreditlimitConfig $config
     * @param CustomerRegistry $customerRegistry
     * @param CreditlimitFactory $customerCreditFactory
     */
    public function __construct(
        CreditlimitConfig $config,
        CustomerRegistry $customerRegistry,
        CreditlimitFactory $customerCreditFactory
    ) {
        $this->config = $config;
        $this->customerRegistry = $customerRegistry;
        $this->customerCreditFactory = $customerCreditFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $currentStoreId = (int)$context->getExtensionAttributes()->getStore()->getId();

        if (!(int) $this->config->getGeneralConfig(self::XML_ENABLE, $currentStoreId)) {
            return 0;
        }

        /** @var Customer $customer */
        $currentCustomer = $this->customerRegistry->retrieve($context->getUserId());
        if (!$currentCustomer && !$currentCustomer->getId()) {
            throw new GraphQlInputException(
                __('Something went wrong while loading the customer.')
            );
        }

        $currentStoreCreditBalanceCustomer = $this->customerCreditFactory->create()
            ->load($currentCustomer->getId(), self::CUSTOMER_ID)
            ->getCreditBalance();
        return $currentStoreCreditBalanceCustomer ?: 0;
    }
}
