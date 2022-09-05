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

namespace Ecommerce\Creditlimit\Plugin;

class getItemAddToCartParams
{
    /**
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetItemAddToCartParams($subject, $result)
    {
        $item = $subject->getItem();
        $product = $item->getProduct();
        if($product->getTypeId() == "creditlimit"){
            return json_encode([ 'action' => $product->getProductUrl() ]);
        }
        return $result;
    }
}