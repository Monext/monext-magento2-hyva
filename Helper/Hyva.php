<?php

namespace Monext\HyvaPayline\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Monext\Payline\Helper\Constants as HelperConstants;

class Hyva extends AbstractHelper
{
    const ADDRESS_MADATORY_FIELDS = [
        'firstname',
        'lastname',
        'street',
        'city',
        'postcode',
        'country_id',
        'email',
    ];


    /**
     * @return array
     */
    public function getHandledPaymentMethods(): array
    {
        return [
            HelperConstants::WEB_PAYMENT_CPT,
            HelperConstants::WEB_PAYMENT_NX,
            HelperConstants::WEB_PAYMENT_REC,
        ];
    }

    /**
     * @return bool
     */
    public function isCompatible(): bool
    {
        if($this->isReactCheckoutEnabled()) {
            return false;
        }

        if($this->isCheckoutEnabled() && !$this->_moduleManager->isEnabled('Monext_HyvaPayline')) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return ($this->isCheckoutEnabled() && $this->_moduleManager->isEnabled('Monext_HyvaPayline'));
    }

    /**
     * Do not expose Payline will module not fully compatible
     *
     * @return bool
     */
    protected function isCheckoutEnabled(): bool
    {
        return $this->_moduleManager->isEnabled('Hyva_Checkout')  &&
                $this->scopeConfig->getValue(
                    'hyva_themes_checkout/general/checkout',
                    ScopeInterface::SCOPE_STORE
                ) !== 'magento_luma';
    }

    /**
     * @return bool
     */
    protected function isReactCheckoutEnabled(): bool
    {
        return $this->_moduleManager->isEnabled('Hyva_ReactCheckout') &&
            $this->scopeConfig->isSetFlag(
                'hyva_react_checkout/general/enable',
                ScopeInterface::SCOPE_STORE
            );
    }
}
