<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommerce\Creditlimit\Api\Data;

/**
 * Search results of Repository::getList method
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface TransactionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get package list
     *
     * @return \Ecommerce\Creditlimit\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set package list
     *
     * @param \Ecommerce\Creditlimit\Api\Data\TransactionInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
