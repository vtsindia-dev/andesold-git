/*
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

/*jshint browser:true*/
/*global alert*/
require(["jquery"], function ($) {
  "use strict";

  $(document).ready(function () {
    $(
      'div#product_info_tabs-basic li[data-ui-id="product-tabs-tab-item-credit-prices-settings"]'
    ).remove();
    $("div#product_info_tabs_credit-prices-settings_content").remove();
  });
});
