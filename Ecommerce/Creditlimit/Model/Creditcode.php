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

namespace Ecommerce\Creditlimit\Model;

class Creditcode extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_creditlimitHelper;

    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context,
     * @param \Magento\Framework\Registry $registry,
     * @param \Ecommerce\Creditlimit\Helper\Data $creditlimitHelper,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Ecommerce\Creditlimit\Model\ResourceModel\Creditcode $resource
     * @param \Ecommerce\Creditlimit\Model\ResourceModel\Creditcode\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ecommerce\Creditlimit\Helper\Data $creditlimitHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ecommerce\Creditlimit\Model\ResourceModel\Creditcode $resource,
        \Ecommerce\Creditlimit\Model\ResourceModel\Creditcode\Collection $resourceCollection
    )
    {
        $this->_creditlimitHelper = $creditlimitHelper;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Ecommerce\Creditlimit\Model\ResourceModel\Creditcode');
        $this->setIdFieldName('credit_code_id');
    }

    public function beforeSave()
    {
        if (!$this->getCreditCode())
            $this->setCreditCode('[N.4]-[AN.5]-[A.4]');
        if ($this->_codeIsExpression())
            $this->setCreditCode($this->_getCreditCode());
        return parent::beforeSave();
    }

    public function _codeIsExpression()
    {
        return $this->_creditlimitHelper->isExpression($this->getCreditCode());
    }

    public function _getCreditCode()
    {
        $code = $this->_creditlimitHelper->calcCode($this->getCreditCode());
        $times = 10;
        while ($this->loadByCode($code)->getId() && $times) {
            $code = $this->_creditlimitHelper->calcCode($this->getCreditCode());
            $times--;
            if ($times == 0) {
                throw new \Exception(__('Exceeded maximum retries to find available random credit code!'));
            }
        }
        return $code;
    }

    public function loadByCode($code)
    {
        return $this->load($code, 'credit_code');
    }

    public function changeCodeStatus($credit_code_id, $status)
    {
        $credit_code = $this->load($credit_code_id);
        $credit_code->setStatus($status);
        try {
            $credit_code->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
//            echo $e->getMessage();
        }
    }

    public function addCreditCode($friend_email, $credit_amount, $status, $customer_id)
    {
        $store = $this->_storeManager->getStore();
        $currentCurrencyCode = $store->getCurrentCurrency()->getCode();
        if ($status) {
            $this->setStatus($status);
        }
        $this->setRecipientEmail($friend_email)
            ->setDescription('send code to friend')
            ->setTransactionTime(date("Y-m-d H:i:s"))
            ->setAmountCredit($credit_amount)
            ->setCustomerId($customer_id)
            ->setCurrency($currentCurrencyCode);
        try {
            $this->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
//            echo $e->getMessage();
        }
        return $this->getCreditCode();
    }
}