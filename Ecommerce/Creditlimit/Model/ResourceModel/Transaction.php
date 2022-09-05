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

namespace Ecommerce\Creditlimit\Model\ResourceModel;

class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        $connectionName = null
    )
    {
       $this->_priceCurrency = $priceCurrency;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('credit_transaction', 'transaction_id');
    }

    public function getCreditUsed()
    {
        $table = $this->getMainTable();
        $select = $this->getConnection()->select()->from($table)->reset('columns')->columns(new \Zend_Db_Expr('SUM(spent_credit)'));
        $spent_credit = $this->getConnection()->fetchOne($select);
        return $this->_priceCurrency->convertAndFormat($spent_credit);
    }
}