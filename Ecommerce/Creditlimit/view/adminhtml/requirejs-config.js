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

var config = {
  map: {
    "*": {
      customerCreditForm: "Ecommerce_Creditlimit/js/order/creditlimit",
      script_colorpicker: "Ecommerce_Creditlimit/js/script_colorpicker",
    },
  },
  paths: {
    customerCreditForm: "Ecommerce_Creditlimit/js/order/creditlimit",
    script_colorpicker: "Ecommerce_Creditlimit/js/script_colorpicker",
  },
  shim: {
    // 'script_colorpicker': {
    //     deps: ['jquery', 'Ecommerce_Creditlimit/js/jquery/colorpicker']
    // }
  },
};
