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
/*global define*/
define(["Ecommerce_Creditlimit/js/view/summary/creditdiscount"], function (
  Component
) {
  "use strict";
  return Component.extend({
    defaults: {
      template: "Ecommerce_Creditlimit/cart/totals/creditdiscount",
    },
    /**
     * @override
     *
     * @returns {boolean}
     */
    isDisplayed: function () {
      return this.getPureValue() != 0;
    },
  });
});
