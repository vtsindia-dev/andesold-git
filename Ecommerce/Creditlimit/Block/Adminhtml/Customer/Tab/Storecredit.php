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

namespace Ecommerce\Creditlimit\Block\Adminhtml\Customer\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Class Storecredit
 *
 * Store credit block
 */
class Storecredit extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_customerCreditFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_coreHelper;

    /**
     * Storecredit constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Pricing\Helper\Data $coreHelper
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $customerCreditFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Pricing\Helper\Data $coreHelper,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $customerCreditFactory,
        array $data = []
    ) {
        $this->_coreHelper = $coreHelper;
        $this->_customerCreditFactory = $customerCreditFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'creditlimit_fieldset',
            [
                'legend' => __('Credit Information')
            ]
        );

        $fieldset->addField(
            'credit_balance',
            'note',
            [
                'label' => __('Current  Balance'),
                'title' => __('Current  Balance'),
                'text' => $this->getBalanceCredit(),
            ]
        );
        $fieldset->addField(
            'credit_value',
            'text',
            [
                'label' => __('Enter The Amount'),
                'title' => __('Enter The Amount'),
                'name' => 'credit_value',
                'data-form-part' => $this->getData('target_form'),
                'note' => 'add - before for debit and + for credit'
            ]
        );
        $fieldset->addField(
            'description',
            'textarea',
            [
                'label' => __('Comment'),
                'title' => __('Comment'),
                'name' => 'description',
                'data-form-part' => $this->getData('target_form'),
            ]
        );
        $fieldset->addField(
            'sendemail',
            'checkbox',
            [
                'type' => 'checkbox',
                'name' => 'send_mail',
                'data-form-part' => $this->getData('target_form'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'style' => 'display:none'
            ]
        );

        $form->addFieldset(
            'balance_history_fieldset',
            [
                'legend' => __('Transaction History')
            ]
        )->setRenderer($this->_layout
            ->getBlockSingleton(\Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class)
            ->setTemplate('Ecommerce_Creditlimit::creditlimit/transactionhistory.phtml'));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Get Credit
     *
     * @return mixed
     */
    public function getCredit()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        return $this->_customerCreditFactory->create()->load($customerId, 'customer_id');
    }

    /**
     * @inheritDoc
     */
    public function getTabLabel()
    {
        return _('Credit Limit');
    }

    /**
     * @inheritDoc
     */
    public function getTabTitle()
    {
        return __('Credit Limit');
    }

    /**
     * @inheritDoc
     */
    public function canShowTab()
    {
        if ($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isHidden()
    {
        if ($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            return false;
        }
        return true;
    }

    /**
     * Get Balance Credit
     *
     * @return float|string
     */
    public function getBalanceCredit()
    {
        $customerCredit = $this->getCredit()->getCreditBalance();
        return $this->_coreHelper->currency($customerCredit);
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
}
