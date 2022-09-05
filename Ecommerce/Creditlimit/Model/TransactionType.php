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

class TransactionType extends \Magento\Framework\Model\AbstractModel
{
    const TYPE_UPDAT_BY_ADMIN = 1;
    const TYPE_SHARE_CREDIT_TO_FRIENDS = 2;
    const TYPE_RECEIVE_CREDIT_FROM_FRIENDS = 3;
    const TYPE_REDEEM_CREDIT = 4;
    const TYPE_REFUND_ORDER_INTO_CREDIT = 5;
    const TYPE_CHECK_OUT_BY_CREDIT = 6;
    const TYPE_CANCEL_SHARE_CREDIT = 7;
    const TYPE_BUY_CREDIT = 8;
    const TYPE_CANCEL_ORDER = 9;
    const TYPE_REFUND_CREDIT_PRODUCT = 10;

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Ecommerce\Creditlimit\Model\ResourceModel\TransactionType');
        $this->setIdFieldName('type_transaction_id');
    }
}