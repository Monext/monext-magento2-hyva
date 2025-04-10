<?php

namespace Monext\HyvaPayline\Block\Js;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Monext\HyvaPayline\Block\PaylineWebPaymentCpt;
use Monext\Payline\Model\Method\WebPayment\GeneralConfigProvider;
use Monext\Payline\Model\Method\WebPayment\CptConfigProvider;

class PaylineWebPaymentBase extends PaylineWebPaymentCpt
{
    protected $cspNonceProvider = null;

    protected $objectManager;


    public function __construct(
        Template\Context $context,
        GeneralConfigProvider $generalConfigProvider,
        CptConfigProvider $cptConfigProvider,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $generalConfigProvider, $cptConfigProvider, $data);

        $this->objectManager = $objectManager;

        //Keep compatibility with 2.4.6
        if(class_exists(\Magento\Csp\Helper\CspNonceProvider::class)) {
            $this->cspNonceProvider = $this->objectManager->get(\Magento\Csp\Helper\CspNonceProvider::class);
        }
    }

    /**
     *
     *
     * @return array|string|string[]|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if($this->cspNonceProvider) {
            $openscriptPattern = '/<(script)(.*)>/';
            $nonceTiInject = 'nonce="' . $this->cspNonceProvider->generateNonce() .'"';
            if(preg_match($openscriptPattern, $html, $matches) ) {
                $html = preg_replace($openscriptPattern, '<${1} ' . $nonceTiInject . '${2}>', $html);
            }
        }

        return $html;
    }

}
