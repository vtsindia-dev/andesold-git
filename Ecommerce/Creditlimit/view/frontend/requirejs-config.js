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
      productCredit: "Ecommerce_Creditlimit/js/product-credit",
      sendCreditToFriend: "Ecommerce_Creditlimit/js/send-to-friend",
      shareCredit: "Ecommerce_Creditlimit/js/account/share-credit",
      cartCustomerCredit:
        "Ecommerce_Creditlimit/js/view/cart/customer-credit",
    },
  },
  paths: {
    productCredit: "Ecommerce_Creditlimit/js/product-credit",
    sendCreditToFriend: "Ecommerce_Creditlimit/js/send-to-friend",
    shareCredit: "Ecommerce_Creditlimit/js/account/share-credit",
    cartCustomerCredit: "Ecommerce_Creditlimit/js/view/cart/customer-credit",
  },
  shim: {
    // 'productCredit': {
    //     deps: ['jquery']
    // }
  },
};
