<?php

namespace TradeSafe\Traits;

trait Allocations
{
    private function allocationsSchema()
    {
        static $transactionsSchema;

        if ($transactionsSchema) {
            return $transactionsSchema;
        }

        $transactionsSchema = file_get_contents(__DIR__ . '/../GraphQL/allocations.graphql');

        return $transactionsSchema;
    }

    public function allocationStartDelivery(string $allocationId)
    {
        return $this->executeQuery($this->allocationsSchema(), [
            'id' => $allocationId
        ], 'allocationStartDelivery');
    }

    public function allocationCompleteDelivery(string $allocationId)
    {
        return $this->executeQuery($this->allocationsSchema(), [
            'id' => $allocationId
        ], 'allocationCompleteDelivery');
    }
}
