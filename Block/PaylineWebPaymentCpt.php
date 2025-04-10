<?php

namespace Monext\HyvaPayline\Block;

use Magento\Framework\View\Element\Template;
use Monext\Payline\Model\Method\WebPayment\GeneralConfigProvider;
use Monext\Payline\Model\Method\WebPayment\CptConfigProvider;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class PaylineWebPaymentCpt extends PaylineWebPaymentAbstract
{
    protected GeneralConfigProvider $generalConfigProvider;

    protected CptConfigProvider $cptConfigProvider;

    public function __construct(
        Template\Context $context,
        GeneralConfigProvider $generalConfigProvider,
        CptConfigProvider $cptConfigProvider,
        array $data = []
    ) {
        $this->generalConfigProvider = $generalConfigProvider;
        $this->cptConfigProvider = $cptConfigProvider;
        parent::__construct($context, $data);
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function _construct()
    {
        $this->setData('template', 'Monext_HyvaPayline::checkout/payment/method/payline_web_payment_cpt_widget.phtml');
        if($this->cptConfigProvider->getConfig()['payment']['paylineWebPaymentCpt']['integrationType'] === PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
            $this->setData('template', 'Monext_HyvaPayline::checkout/payment/method/payline_web_payment_cpt_redirect.phtml');
        }
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getContracts(): array
    {
        return $this->generalConfigProvider->getConfig()['payline']['general']['contracts'];
    }
}
