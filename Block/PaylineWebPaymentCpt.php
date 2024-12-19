<?php

namespace Monext\HyvaPayline\Block;

use Magento\Framework\View\Element\Template;
use Monext\Payline\Model\Method\WebPayment\GeneralConfigProvider;

class PaylineWebPaymentCpt extends Template
{

    private $config;

    public function __construct(
        Template\Context $context,
        GeneralConfigProvider $configProvider,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->config = $configProvider;
    }

    public function getConfig()
    {
        return $this->config->getConfig();
    }
}
