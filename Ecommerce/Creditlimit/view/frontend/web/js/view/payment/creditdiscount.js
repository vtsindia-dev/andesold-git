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
define([
  "jquery",
  "ko",
  "uiComponent",
  "Magento_Checkout/js/model/quote",
  "Ecommerce_Creditlimit/js/model/creditdiscount",
  "Ecommerce_Creditlimit/js/action/set-credit-amount",
  "Ecommerce_Creditlimit/js/action/cancel-credit-amount",
], function (
  $,
  ko,
  Component,
  quote,
  creditdata,
  setCreditAmountAction,
  cancelCreditAmountAction
) {
  "use strict";
  var totals = quote.getTotals();
  var creditdata = window.creditlimitInfo;
  var amount = null;
  var isApplied = false;
  if (creditdata.credit_amount != null) {
    amount = creditdata.credit_amount;
    isApplied = true;
  }
  // var isLoading = ko.observable(false);
  return Component.extend({
    defaults: {
      template: "Ecommerce_Creditlimit/payment/creditdiscount",
    },
    amount: ko.observable(amount),
    /**
     * Applied flag
     */
    isApplied: ko.observable(isApplied),
    isLoading: ko.observable(false),
    creditdata: creditdata,
    /**
     * Coupon code application procedure
     */
    apply: function () {
      if (this.validate()) {
        this.isLoading(true);
        setCreditAmountAction(
          this.amount,
          this.isApplied,
          this.isLoading,
          this.creditdata
        );
      }
    },
    /**
     * Cancel using coupon
     */
    cancel: function () {
      if (this.validate()) {
        this.isLoading(true);
        cancelCreditAmountAction(this.amount, this.isApplied, this.isLoading);
      }
    },
    /**
     * Coupon form validation
     *
     * @returns {boolean}
     */
    validate: function () {
      var form = "#discount-credit-form";
      return $(form).validation() && $(form).validation("isValid");
    },

    isLoggedin: function () {
      return this.creditdata.is_logged_in;
    },

    isEnable: function () {
      if (this.creditdata.isEnable == 1) {
        return true;
      } else {
        return false;
      }
    },

    loginLink: function () {
      return this.creditdata.login_link;
    },

    isInGroup: function () {
      return this.creditdata.in_group_credit;
    },

    isHasCreditItem: function () {
      return this.creditdata.has_credit_item;
    },

    isCreditItemOnly: function () {
      return this.creditdata.credit_item_only;
    },

    creditBalance: function () {
      return this.creditdata.credit_balance;
    },
  });
});
