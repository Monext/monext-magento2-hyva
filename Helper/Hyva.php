<?php

namespace Monext\HyvaPayline\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Hyva extends AbstractHelper
{

    public function isCompatible()
    {
        if($this->isReactCheckoutEnabled()) {
            return false;
        }

        if($this->isCheckoutEnabled() && !$this->_moduleManager->isEnabled('Monext_HyvaPayline')) {
            return false;
        }

        return true;
    }


    public function isEnabled()
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
        return $this->_moduleManager->isEnabled('Hyva_ReactCheckout')  && $this->scopeConfig->isSetFlag(
                'hyva_react_checkout/general/enable',
                ScopeInterface::SCOPE_STORE
            );
    }


}
