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

namespace Ecommerce\Creditlimit\Plugin\Adminhtml\Block\Customer;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

class BackButton
{
    /**
     * BackButton constructor
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function afterGetButtonData(\Magento\Customer\Block\Adminhtml\Edit\BackButton $button, $result)
    {
        $type = $this->context->getRequestParam('type');
        if($type == 'creditlimit'){
            return [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", $button->getUrl('creditlimitadmin/creditlimit/')),
                'class' => 'back',
                'sort_order' => 10
            ];
        }

        return $result;
    }
}
