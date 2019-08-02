<?php

namespace common\entities;

class Player {
    private $id;
    public function getId(): int
    {
        return $this->id;
    }

    private $login;
    public function getLogin(): string
    {
        return $this->login;
    }

    private $currency;
    public function getCurrency(): string
    {
        return $this->currency;
    }

    private $balance;
    public function getBalance(): int
    {
        return $this->balance;
    }

    public function __construct(int $id, string $login, string $currency, int $balance)
    {
        $this->id = $id;
        $this->login = $login;
        $this->currency = $currency;
        $this->balance = $balance;
    }
}
