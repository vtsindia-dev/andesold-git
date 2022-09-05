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

define(["Magento_Ui/js/form/element/abstract", "uiRegistry"], function (
  Abstract,
  uiRegistry
) {
  "use strict";

  return Abstract.extend({
    defaults: {
      // valuesForEnable: []
    },
    /**
     * Initialize component.
     *
     * @returns {Element}
     */
    initializex: function () {},
    /**
     * Toggle disabled state.
     *
     * @param {Number} selected
     */
    toggleDisable: function (selected) {
      this.disabled(!(selected in this.valuesForEnable));
      if (selected in this.valuesForEnable) {
        this.enable();
        this.show();
      } else {
        this.disable();
        this.hide();
      }
    },
    /**
     * Change validator
     */
    handleChangeMin: function (max) {
      var val = max.replace(/,/g, "");
      var isDigits = !isNaN(val);
      this.validation["validate-number"] = !isDigits;
      this.validation["less-than-equals-to"] = val;
      this.validate();
    },
    /**
     * Change validator
     */
    handleChangeMax: function (min) {
      var val = min.replace(/,/g, "");
      var isDigits = !isNaN(val);
      this.validation["validate-number"] = !isDigits;
      this.validation["greater-than-equals-to"] = val;
      this.validate();
    },
  });
});
