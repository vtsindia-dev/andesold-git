<?php
/**
 * Copyright © 2021 Ecommerce. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecommerce\Creditlimit\Api;

/**
 * Interface TransactionRepositoryInterface
 *
 * Used for Creditlimit - Transaction
 */
interface TransactionRepositoryInterface
{
    /**
     * Find Transactions by given SearchCriteria
     *
     * SearchCriteria is not required because load all packages is useful case
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Ecommerce\Creditlimit\Api\Data\TransactionSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ): \Ecommerce\Creditlimit\Api\Data\TransactionSearchResultsInterface;
}
