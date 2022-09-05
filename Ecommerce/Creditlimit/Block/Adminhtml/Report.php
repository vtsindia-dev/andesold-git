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

namespace Ecommerce\Creditlimit\Block\Adminhtml;

class Report extends \Magento\Backend\Block\Template
{
    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('creditlimit/report/index.phtml');
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setChild('statistics-credit',$this->getLayout()->createBlock('Ecommerce\Creditlimit\Block\Adminhtml\Statisticscredit'));
        $this->setChild('max-balance', $this->getLayout()->createBlock('Ecommerce\Creditlimit\Block\Adminhtml\Maxbalance'));
        $this->setChild('customer-credit', $this->getLayout()->createBlock('Ecommerce\Creditlimit\Block\Adminhtml\Report\Dashboard'));
        parent::_prepareLayout();
    }
}
