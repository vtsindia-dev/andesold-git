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

/**
 * Customer store credit(balance) application
 */
/*global define,alert*/
define([
  "ko",
  "jquery",
  "Magento_Checkout/js/model/quote",
  "Magento_Checkout/js/model/resource-url-manager",
  "Magento_Checkout/js/model/error-processor",
  "Magento_SalesRule/js/model/payment/discount-messages",
  "mage/storage",
  "Magento_Checkout/js/action/get-payment-information",
  "Magento_Checkout/js/model/totals",
  "mage/translate",
  "Ecommerce_Creditlimit/js/action/reload-shipping-method",
  "mage/url",
], function (
  ko,
  $,
  quote,
  urlManager,
  errorProcessor,
  messageContainer,
  storage,
  getPaymentInformationAction,
  totals,
  $t,
  reloadShippingMethod,
  urlBuilder
) {
  "use strict";

  return function (amount, isApplied, isLoading, creditdata) {
    var credit_amount = amount.call();
    var url = urlBuilder.build("creditlimit/checkout/amountPost");
    var params = {
      credit_amount: Number(credit_amount),
    };

    return $.post(url, params)
      .done(function (response) {
        var res = JSON.parse(response);
        amount(res.credit_amount);
        $("#credit_balance").text(res.getFormatedBalance);
        var deferred = $.Deferred();
        totals.isLoading(true);
        getPaymentInformationAction(deferred);
        reloadShippingMethod();
        $.when(deferred).done(function () {
          isApplied(true);
          totals.isLoading(false);
        });
      })
      .fail(function (response) {
        totals.isLoading(false);
        errorProcessor.process(response, messageContainer);
      })
      .always(function () {
        isLoading(false);
      });
  };
});
