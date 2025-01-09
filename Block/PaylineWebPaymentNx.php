<?php

namespace Monext\HyvaPayline\Block;

use Magento\Framework\View\Element\Template;
use Monext\Payline\Model\Method\WebPayment\NxConfigProvider;

class PaylineWebPaymentNx extends PaylineWebPaymentAbstract
{
    private NxConfigProvider $nxConfigProvider;

    public function __construct(
        Template\Context $context,
        NxConfigProvider $nxConfigProvider,
        array $data = []
    ) {
        $this->nxConfigProvider = $nxConfigProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getRedirectMessage(): string
    {
        try {
            $message = $this->nxConfigProvider->getConfig()['payment']['paylineWebPaymentNx']['redirect_message'];
        } catch (\ReflectionException $e) {
            return parent::getRedirectMessage();
        }

        return !empty($message) ? $message : parent::getRedirectMessage();
    }
}
