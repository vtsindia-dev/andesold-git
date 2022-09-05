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

namespace Ecommerce\Creditlimit\Model\ResourceModel\Transaction;

/**
 * Class Collection
 *
 * Transaction collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Ecommerce\Creditlimit\Helper\Data $dataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Ecommerce\Creditlimit\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->_dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ecommerce\Creditlimit\Model\Transaction::class,
            \Ecommerce\Creditlimit\Model\ResourceModel\Transaction::class
        );
    }

    /**
     * Get Date Range
     *
     * @param string $range
     * @param \DateTime $customStart
     * @param \DateTime $customEnd
     * @param bool $returnObjects
     * @return array
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateStart = new \DateTime();
        $dateEnd = new \DateTime();

        // go to the end of a day
        $dateStart->setTime(0, 0, 0);
        $dateEnd->setTime(23, 59, 59);

        switch ($range) {
            case '24h':
                $dateEnd = new \DateTime();
                $dateEnd->modify('+1 hour');
                $dateStart = clone $dateEnd;
                $dateStart->modify('-1 day');
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->modify('-6 days');
                break;

            case '1m':
                $dateStart->setDate(
                    $dateStart->format('Y'),
                    $dateStart->format('m'),
                    $this->_dataHelper->getReportConfig(
                        'mtd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(
                    ',',
                    $this->_dataHelper->getReportConfig('ytd_start', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                );
                $startMonth = isset($startMonthDay[0]) ? (int) $startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int) $startMonthDay[1] : 1;
                $dateStart->setDate($dateStart->format('Y'), $startMonth, $startDay);
                if ($range == '2y') {
                    $dateStart->modify('-2 year');
                }
//                $dateEnd->setDate($dateEnd->format('Y'), 1, 1);
//                $dateEnd->modify('-1 day');
                break;
        }

        // $dateStart->setTimezone('Etc/UTC');
        // $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return [$dateStart, $dateEnd];
        } else {
            return ['from' => $dateStart, 'to' => $dateEnd, 'datetime' => true];
        }
    }

    /**
     * Prepare Customer credit
     *
     * @param string $range
     * @param \DateTime $customStart
     * @param \DateTime $customEnd
     * @return $this
     * @throws \Exception
     */
    public function prepareCreditlimit($range, $customStart, $customEnd)
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns(
            [
                'spent_credit' => 'SUM(spent_credit)',
                'received_credit' => 'SUM(received_credit)',
            ]
        );
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->getSelect()->columns(['range' => $this->_getRangeExpressionForAttribute($range, 'transaction_time')])
            ->order('range', \Magento\Framework\DB\Select::SQL_ASC)
            ->group('range');
        $this->addFieldToFilter('transaction_time', $dateRange);
        return $this;
    }

    /**
     * Get Range Expression For Attribute
     *
     * @param string $range
     * @param string $attribute
     * @return mixed
     */
    public function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }

    /**
     * Get Range Expression
     *
     * @param string $range
     * @return string
     */
    public function _getRangeExpression($range)
    {
        switch ($range) {
            case '24h':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d %H:00\')';
                break;
            case '7d':
            case '1m':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                break;
        }

        return $expression;
    }
}
