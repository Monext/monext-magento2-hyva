<?php
namespace Monext\HyvaPayline\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Monext\Payline\Model\Method\WebPayment\GeneralConfigProvider;
use Payplug\Payments\Model\Payment\Standard\ConfigProvider as Config;

class PaylineWebPaymentCpt extends Template
{
    /**
     * @var Session
     */
    private $customerSession;

    private $config;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        GeneralConfigProvider $configProvider,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->config = $configProvider;
        $this->customerSession = $customerSession;
    }

    public function getConfig()
    {
        return $this->config->getConfig();
    }
}
