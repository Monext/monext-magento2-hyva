<?php

namespace Monext\HyvaPayline\Plugin\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Monext\HyvaPayline\Helper\Hyva as HyvaHelper;
use Monext\Payline\Model\Method\AbstractMethod;

class PaymentMethod
{
    private HyvaHelper $helperHyva;

    public function __construct(
        HyvaHelper $helperHyva
    ) {
        $this->helperHyva = $helperHyva;
    }

    /**
     * @param AbstractMethod $subject
     * @param bool $result
     * @param CartInterface|null $quote
     * @return bool
     * @throws LocalizedException
     */
    public function afterIsAvailable(AbstractMethod $subject, bool $result, CartInterface $quote = null): bool
    {
        if($this->helperHyva->isEnabled()) {
            if(!in_array($subject->getCode(), $this->helperHyva->getHandledPaymentMethods())) {
                return false;
            }
        }

        return $result && $this->helperHyva->isCompatible();
    }
}
