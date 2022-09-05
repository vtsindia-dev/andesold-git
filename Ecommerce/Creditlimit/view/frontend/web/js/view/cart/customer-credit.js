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
define(["jquery", "mage/url"], function ($, urlBuilder) {
  "use strict";

  $.widget("ecommerce.cartCustomerCredit", {
    _create: function () {
      var self = this;

      $("#checkout-cart-remove-credit-amount-button").on("click", function () {
        self.removeCreditAmount();
      });
    },
    removeCreditAmount: function () {
      var url = urlBuilder.build("creditlimit/checkout/amountPost");
      var params = {
        customer_credit: 0,
      };

      return $.post(url, params).done(function () {
        window.location.reload();
      });
    },
  });

  return $.ecommerce.cartCustomerCredit;
});
