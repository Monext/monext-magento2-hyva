<?php

namespace Monext\HyvaPayline\Plugin;

class CheckoutCheckEavAttributes
{
    public function afterGetShippingEavAttributeFormFieldsMapping($subject, $result) {
        return $this->resetDummyHyvaBehavior($result);
    }

    public function afterGetBillingEavAttributeFormFieldsMapping($subject, $result) {
        return $this->resetDummyHyvaBehavior($result);
    }

    protected function resetDummyHyvaBehavior($mapping) {

        foreach (\Monext\HyvaPayline\Helper\Hyva::ADDRESS_MADATORY_FIELDS as $field) {
            if(isset($mapping[$field])) {
                $mapping[$field]['required'] = 1;
            }
        }


        if(isset($mapping['wallet_id'])) {
            unset($mapping['wallet_id']);
        }
        return $mapping;
    }
}
