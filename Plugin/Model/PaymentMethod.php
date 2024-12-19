<?php

namespace Monext\HyvaPayline\Plugin\Model;

use Magento\Quote\Api\Data\CartInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\Method\AbstractMethod;

class PaymentMethod
{
    protected $helperHyva;

    public function __construct(\Monext\HyvaPayline\Helper\Hyva $helperHyva)
    {
        $this->helperHyva = $helperHyva;
    }


    /**
     * @param AbstractMethod $subject
     * @param bool $result
     * @param CartInterface|null $quote
     * @return bool
     */
    public function afterIsAvailable(AbstractMethod $subject, bool $result, CartInterface $quote = null): bool
    {

        if($this->helperHyva->isEnabled()) {
            if($subject->getCode() !== HelperConstants::WEB_PAYMENT_CPT) {
                return false;
            }
        }

        return $result && $this->helperHyva->isCompatible();
    }
}
