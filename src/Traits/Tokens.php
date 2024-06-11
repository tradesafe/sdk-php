<?php

namespace TradeSafe\Traits;

trait Tokens
{
    public function getToken(string $tokenId, bool $getBankAccount = false)
    {
        $query = file_get_contents(__DIR__ . '/../GraphQL/Queries/Tokens/token.graphql');

        return $this->executeQuery($query, ['tokenId' => $tokenId, 'getBankAccount' => $getBankAccount]);
    }

    public function createToken(array $user, array $organization = null, array $bankAccount = null, array $settings = null)
    {
        $mutation = file_get_contents(__DIR__ . '/../GraphQL/Mutations/Tokens/create.graphql');

        $input = [
            'user' => $user
        ];

        if (!empty($organization)) {
            $input['organization'] = $organization;
        }

        if (!empty($bankAccount)) {
            $input['bankAccount'] = $bankAccount;
        }

        if (!empty($settings)) {
            $input['settings'] = $settings;
        }

        return $this->executeQuery($mutation, ['input' => $input]);
    }
}
