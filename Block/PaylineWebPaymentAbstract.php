<?php

namespace Monext\HyvaPayline\Block;

use Magento\Framework\View\Element\Template;

class PaylineWebPaymentAbstract extends Template
{
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getRedirectMessage(): string
    {
       return 'You will be redirected on payline payment gateway after place order.';
    }

    /**
     * @return string
     */
    public function getPaylineLogoUrl(): string
    {
       return $this->getViewFileUrl('Monext_Payline::images/monext/payline-logo.png');
    }
}
