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

namespace Ecommerce\Creditlimit\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Sendemail
 *
 * Send email controller
 */
class Sendemail extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Ecommerce\Creditlimit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Ecommerce\Creditlimit\Model\CreditlimitFactory
     */
    protected $_creditlimitFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customersession;

    /**
     * Sendemail constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Ecommerce\Creditlimit\Helper\Account $accountHelper
     * @param \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory
     * @param \Magento\Customer\Model\Session $customersesion
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ecommerce\Creditlimit\Helper\Account $accountHelper,
        \Ecommerce\Creditlimit\Model\CreditlimitFactory $creditlimitFactory,
        \Magento\Customer\Model\Session $customersesion
    ) {
        $this->_accountHelper = $accountHelper;
        $this->_creditlimitFactory = $creditlimitFactory;
        $this->_customersession = $customersesion;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->_accountHelper->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        $this->_customersession->setData("sentemail", 'yes');
        $this->_customersession->setData("is_credit_code", 'yes');
        $email = $this->getRequest()->getParam('email');
        $value = $this->getRequest()->getParam('value');
        $message = $this->getRequest()->getParam('message');
        $ran_num = rand(1, 1000000);
        $keycode = sha1(sha1(sha1($ran_num)));
        $this->_customersession->setData("emailcode", $keycode);
        $this->_creditlimitFactory->create()->sendVerifyEmail($email, $value, $message, $keycode);
        $result = [];
        $result['success'] = 1;
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
