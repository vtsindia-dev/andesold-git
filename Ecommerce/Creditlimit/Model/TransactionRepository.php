<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommerce\Creditlimit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Ecommerce\Creditlimit\Model\ResourceModel\Transaction as TransactionResourceModel;
use Ecommerce\Creditlimit\Model\ResourceModel\Transaction\Collection;
use Ecommerce\Creditlimit\Model\ResourceModel\Transaction\CollectionFactory;
use Ecommerce\Creditlimit\Api\Data\TransactionInterfaceFactory;
use Ecommerce\Creditlimit\Api\Data\TransactionSearchResultsInterface;
use Ecommerce\Creditlimit\Api\Data\TransactionSearchResultsInterfaceFactory;
use Ecommerce\Creditlimit\Api\TransactionRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class TransactionRepository
 *
 * Use for CRUD Transaction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    protected $packageCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var TransactionSearchResultsInterfaceFactory
     */
    protected $packageSearchResultsInterfaceFactory;

    /**
     * @var TransactionResourceModel
     */
    protected $packageResource;

    /**
     * @var TransactionInterfaceFactory
     */
    protected $packageInterfaceFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * TransactionRepository constructor.
     *
     * @param CollectionFactory $packageCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     * @param TransactionSearchResultsInterfaceFactory $packageSearchResultsInterfaceFactory
     * @param TransactionResourceModel $packageResource
     * @param TransactionInterfaceFactory $packageInterfaceFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $packageCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        TransactionSearchResultsInterfaceFactory $packageSearchResultsInterfaceFactory,
        TransactionResourceModel $packageResource,
        TransactionInterfaceFactory $packageInterfaceFactory,
        LoggerInterface $logger
    ) {
        $this->packageCollectionFactory = $packageCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->packageSearchResultsInterfaceFactory = $packageSearchResultsInterfaceFactory;
        $this->packageResource = $packageResource;
        $this->packageInterfaceFactory = $packageInterfaceFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): TransactionSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->packageCollectionFactory->create();

        if (null === $searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        /** @var TransactionSearchResultsInterface $searchResult */
        $searchResult = $this->packageSearchResultsInterfaceFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }
}
