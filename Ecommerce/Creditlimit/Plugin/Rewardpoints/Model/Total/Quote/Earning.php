<?php

namespace Ecommerce\Creditlimit\Plugin\Rewardpoints\Model\Total\Quote;

/**
 * Add store credit as a ignoring product
 */
class Earning
{
    /**
     * Add store credit as a ignoring product
     *
     * @param \Ecommerce\Rewardpoints\Model\Total\Quote\Earning $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIgnoringProducts(
        \Ecommerce\Rewardpoints\Model\Total\Quote\Earning $subject,
        $result
    ) {
        $result[] = \Ecommerce\Creditlimit\Model\Product\Type::TYPE_CODE;
        return $result;
    }
}
