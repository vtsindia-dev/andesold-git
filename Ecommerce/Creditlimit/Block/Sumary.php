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

namespace Ecommerce\Creditlimit\Block;

class Sumary extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_customerCreditFactory;

    /**
     * @var \Ecommerce\Creditlimit\Helper\Data
     */
    protected $_creditHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $customerCreditFactory
     * @param \Ecommerce\Creditlimit\Helper\Data $creditHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $customerCreditFactory,
        \Ecommerce\Creditlimit\Helper\Data $creditHelper
    )
    {
        $this->_customerCreditFactory = $customerCreditFactory;
        $this->_creditHelper = $creditHelper;
        parent::__construct($context);
    }

    public function getBalanceLabel()
    {
        return $this->_creditHelper->getCustomerCreditValueLabel();
    }

}
