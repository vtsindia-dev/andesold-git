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

namespace Ecommerce\Creditlimit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Price
 *
 * Product price model
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    const PRICE_TYPE_FIXED = 1;
    const PRICE_TYPE_DYNAMIC = 0;

    /**
     * @var \Ecommerce\Creditlimit\Helper\Creditproduct
     */
    protected $_creditproductData;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * Price constructor.
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Ecommerce\Creditlimit\Helper\Creditproduct $helperData
     * @param \Magento\Catalog\Helper\Data $catalogData
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Ecommerce\Creditlimit\Helper\Creditproduct $helperData,
        \Magento\Catalog\Helper\Data $catalogData
    ) {
        $this->_creditproductData = $helperData;
        $this->_catalogData = $catalogData;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config
        );
    }

    /**
     * Default action to get price of product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getPrice($product)
    {
        $creditData = $this->getCreditData($product);
        return $creditData['price'];
    }

    /**
     * @inheritDoc
     */
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        if ($amount = $product->getCustomOption('credit_price_amount')) {
            $finalPrice = $amount->getValue();
        }
        return parent::_applyOptionsPrice($product, $qty, $finalPrice);
    }

    /**
     * Get Minimal Price
     *
     * @param Product $product
     * @return array|float
     */
    public function getMinimalPrice($product)
    {
        return $this->getPricesDependingOnTax($product, 'min');
    }

    /**
     * Get Maximal Price
     *
     * @param Product $product
     * @return array|float
     */
    public function getMaximalPrice($product)
    {
        return $this->getPricesDependingOnTax($product, 'max');
    }

    /**
     * Get Prices Depending On Tax
     *
     * @param Product $product
     * @param null|string $which
     * @param null|bool $includeTax
     * @return array|float
     */
    public function getPricesDependingOnTax($product, $which = null, $includeTax = null)
    {
        $creditData = $this->getCreditData($product);
        if (isset($creditData['min_price']) && isset($creditData['max_price'])) {
            $minimalPrice = $this->_catalogData->getTaxPrice($product, $creditData['min_price'], $includeTax);
            $maximalPrice = $this->_catalogData->getTaxPrice($product, $creditData['max_price'], $includeTax);
        } else {
            $minimalPrice = $maximalPrice = $this->_catalogData->getTaxPrice(
                $product,
                $creditData['price'],
                $includeTax
            );
        }

        if ($which == 'max') {
            return $maximalPrice;
        } elseif ($which == 'min') {
            return $minimalPrice;
        }
        return [$minimalPrice, $maximalPrice];
    }

    /**
     * Get Credit Data
     *
     * @param null|Product $product
     * @return array
     */
    public function getCreditData($product = null)
    {
        $data = $this->_creditproductData->getCreditDataByProduct($product);
        switch ($data['type']) {
            case 'range':
                $data['min_price'] = $data['from'];
                $data['max_price'] = $data['to'];
                $data['price'] = $data['from'];

                if (isset($data['storecredit_rate'])) {
                    $data['price'] = $data['price'] * $data['storecredit_rate'];
                    $data['min_price'] = $data['from'] * $data['storecredit_rate'];
                    $data['max_price'] = $data['to'] * $data['storecredit_rate'];
                }

                if ($data['min_price'] == $data['max_price']) {
                    $data['price_type'] = self::PRICE_TYPE_FIXED;
                } else {
                    $data['price_type'] = self::PRICE_TYPE_DYNAMIC;
                }
                break;
            case 'dropdown':
                $data['min_price'] = min($data['prices']);
                $data['max_price'] = max($data['prices']);
                $data['price'] = $data['prices'][0];
                if ($data['min_price'] == $data['max_price']) {
                    $data['price_type'] = self::PRICE_TYPE_FIXED;
                } else {
                    $data['price_type'] = self::PRICE_TYPE_DYNAMIC;
                }
                break;
            case 'static':
                $data['price'] = $data['credit_price'];
                $data['price_type'] = self::PRICE_TYPE_FIXED;
                break;
            default:
                $data['min_price'] = 0;
                $data['price_type'] = self::PRICE_TYPE_DYNAMIC;
                $data['price'] = 0;
        }
        return $data;
    }
}
