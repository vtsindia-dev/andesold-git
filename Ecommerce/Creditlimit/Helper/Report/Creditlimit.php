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

namespace Ecommerce\Creditlimit\Helper\Report;

class Creditlimit extends \Magento\Backend\Helper\Dashboard\AbstractDashboard
{
    /**
     * @var \Ecommerce\Creditlimit\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ecommerce\Creditlimit\Model\TransactionFactory $transactionFactory
    )
    {
        $this->_transactionFactory = $transactionFactory;
        parent::__construct($context);
    }

    protected function _initCollection()
    {
        $this->_collection = $this->_transactionFactory->create()->getCollection()->prepareCreditlimit($this->getParam('period'), 0, 0);
        $this->_collection->load();
    }
}
