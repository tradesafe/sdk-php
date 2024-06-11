<?php

namespace TradeSafe\Traits;

trait ApiClient
{
    public function getClientInfo()
    {
        $query = file_get_contents(__DIR__ . '/../GraphQL/clientInfo.graphql');

        return $this->executeQuery($query);
    }

    public function getProfile()
    {
        $query = file_get_contents(__DIR__ . '/../GraphQL/profile.graphql');

        return $this->executeQuery($query);
    }
}
