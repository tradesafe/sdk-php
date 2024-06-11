<?php

namespace TradeSafe\Traits;

Trait Transactions
{
    private function transactionsSchema()
    {
        static $transactionsSchema;

        if ($transactionsSchema) {
            return $transactionsSchema;
        }

        $transactionsSchema = file_get_contents(__DIR__ . '/../GraphQL/transactions.graphql');

        return $transactionsSchema;
    }

    public function getTransactions()
    {
        return $this->executeQuery($this->transactionsSchema(), [], 'transactions');
    }

    public function getTransaction(string $transactionId)
    {
        return $this->executeQuery($this->transactionsSchema(), [
            'id' => $transactionId
        ], 'transaction');
    }

    public function createTransaction(array $input)
    {
        return $this->executeQuery($this->transactionsSchema(), ['input' => $input], 'createTransaction');
    }

    public function cancelTransaction(string $transactionId)
    {
        return $this->executeQuery($this->transactionsSchema(), ['id' => $transactionId], 'cancelTransaction');
    }

    public function getCheckoutLink(string $transactionId, $embed = false, $paymentMethods = null)
    {
        $schema = file_get_contents(__DIR__ . '/../GraphQL/checkout.graphql');

        $variables = [
            'transactionId' => $transactionId
        ];

        if ($embed) {
            $variables['embed'] = $embed;
        }

        if ($paymentMethods) {
            $variables['paymentMethods'] = $paymentMethods;
        }

        return $this->executeQuery($schema, $variables, 'checkoutLink');
    }
}
