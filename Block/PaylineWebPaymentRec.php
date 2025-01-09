<?php

namespace Monext\HyvaPayline\Block;

use Magento\Framework\View\Element\Template;
use Monext\Payline\Model\Method\WebPayment\GeneralConfigProvider;
use Monext\Payline\Model\Method\WebPayment\RecConfigProvider;

class PaylineWebPaymentRec extends PaylineWebPaymentAbstract
{
    private GeneralConfigProvider $generalConfigProvider;
    private RecConfigProvider $recConfigProvider;

    public function __construct(
        Template\Context $context,
        GeneralConfigProvider $generalConfigProvider,
        RecConfigProvider $recConfigProvider,
        array $data = []
    ) {
        $this->generalConfigProvider = $generalConfigProvider;
        $this->recConfigProvider = $recConfigProvider;
        parent::__construct($context, $data);
    }
}
