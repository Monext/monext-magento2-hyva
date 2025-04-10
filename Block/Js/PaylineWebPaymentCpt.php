<?php

namespace Monext\HyvaPayline\Block\Js;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Monext\Payline\Model\Method\WebPayment\GeneralConfigProvider;
use Monext\Payline\Model\Method\WebPayment\CptConfigProvider;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class PaylineWebPaymentCpt extends PaylineWebPaymentBase
{

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function _construct()
    {
        $this->setData('template', 'Monext_HyvaPayline::checkout/payment/js/script_payment_cpt_widget.phtml');
        if($this->cptConfigProvider->getConfig()['payment']['paylineWebPaymentCpt']['integrationType'] === PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
            $this->setData('template', 'Monext_HyvaPayline::checkout/payment/js/script_payment_cpt_redirect.phtml');
        }

        \Magento\Framework\View\Element\Template::_construct();
    }

    public function getWidgetContext()
    {
        $environment = $this->generalConfigProvider->getConfig()['payline']['general']['environment'];
        $paylineCptConfig = $this->cptConfigProvider->getConfig()['payment']['paylineWebPaymentCpt'];

        $context = [
            "environment"      =>$environment,
            "widgetDisplay"       => $paylineCptConfig['widgetDisplay'],
            //"dataEmbeddedredirectionallowed"=> $paylineCptConfig['dataEmbeddedredirectionallowed']
            "dataEmbeddedredirectionallowed"=> true
        ];

        return json_encode($context);
    }

}
