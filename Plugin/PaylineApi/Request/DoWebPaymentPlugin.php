<?php

namespace Monext\HyvaPayline\Plugin\PaylineApi\Request;

use Magento\Quote\Api\Data\AddressInterface;
use Monext\HyvaPayline\Helper\Hyva;

class DoWebPaymentPlugin
{
    const FROM_MONEXT_PLUGIN = 'from_monext_plugin';

    private  $shippingAddress;

    private  $billingAddress;

    /**
     * @param \Monext\Payline\PaylineApi\Request\DoWebPayment $subject
     * @param $billingAddress
     * @return array
     */
    public function  beforeSetBillingAddress(\Monext\Payline\PaylineApi\Request\DoWebPayment $subject, $billingAddress)
    {
        $this->billingAddress = $billingAddress;

        $this->hydrateBillingWithShipping($subject);

        return [$billingAddress];
    }

    /**
     * Store the shipping address in the subject
     *
     * @param AddressInterface|null $shippingAddress
     * @return $this
     */
    public function beforeSetShippingAddress(\Monext\Payline\PaylineApi\Request\DoWebPayment $subject, $shippingAddress = null)
    {
        $this->shippingAddress = $shippingAddress;

        $this->hydrateBillingWithShipping($subject);


        return [$shippingAddress];
    }

    private function hydrateBillingWithShipping($subject)
    {
        if($this->shippingAddress !== null && $this->billingAddress !== null) {
            $shippingData = $this->shippingAddress->getData();
            $billingData = array_merge($this->billingAddress->getData(),
                array_filter($shippingData, fn ($value, $key) => $value && in_array($key,Hyva::ADDRESS_MADATORY_FIELDS), ARRAY_FILTER_USE_BOTH)
            );
            $this->billingAddress->setData($billingData);

            //Avoid infinite loop
            if(!$this->billingAddress->getData(self::FROM_MONEXT_PLUGIN)) {
                $this->billingAddress->setData(self::FROM_MONEXT_PLUGIN, true);
                $subject->setBillingAddress($this->billingAddress);
            }

        }
    }
}
