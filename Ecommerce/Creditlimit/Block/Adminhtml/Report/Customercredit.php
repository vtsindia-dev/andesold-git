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

namespace Ecommerce\Creditlimit\Block\Adminhtml\Report;

/**
 * Class Creditlimit
 *
 * Generate Customer credit chart
 */
class Creditlimit extends \Ecommerce\Creditlimit\Block\Adminhtml\Report\Graph
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $currency = $this->_localeCurrency->getCurrency(
            $this->_storeManager->getStore()->getCurrentCurrencyCode()
        )->getSymbol();
        $productMetadata = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Framework\App\ProductMetadataInterface::class);
        if (version_compare($productMetadata->getVersion(), '2.3.2', '<')) {
            $_chxt = 'x,y,y';
        } else {
            $_chxt = 'x,y';
        }

        $this->_googleChartParams = [
            'cht' => 'lc',
            'chf' => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
            'chdl' => __('Used credit') . '|' . __('Received credit'),
            'chco' => '2424ff,db4814',
            'chxt' => $_chxt,
            'chxl' => '|2:||' . __('# Credit(%1)', $currency)
        ];

        $this->setHtmlId('customer-credit');
        parent::_construct();
    }

    /**
     * @inheritDoc
     */
    protected function _prepareData()
    {
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        /*$data = */$this->setDataRows(['spent_credit', 'received_credit']);
        $this->_axisMaps = [
            'x' => 'range',
            'y' => 'received_credit'
        ];
        parent::_prepareData();
    }

    /**
     * Get Comment Content
     *
     * @return \Magento\Framework\Phrase
     */
    public function getCommentContent()
    {
        return __('This report shows the <b>used Credit </b> and <b>reveived Credit</b> of Customer');
    }
}
