<?php

namespace common\entities;

class Transaction {
    private $id;
    public function getId(): int
    {
        return $this->id;
    }

    private $type;
    public function getType(): string
    {
        return $this->type;
    }

    private $parentTransaction;
    public function getParentTransaction()
    {
        return $this->parentTransaction;
    }

    private $uid;
    public function getUid(): string
    {
        return $this->uid;
    }

    private $gameId;
    public function getGameId(): int
    {
        return $this->gameId;
    }

    private $sessionId;
    public function getSessionId(): int
    {
        return $this->sessionId;
    }

    private $playerId;
    public function getPlayerId(): int
    {
        return $this->playerId;
    }

    private $amount;
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function __construct(int $id, string $uid, string $type, int $amount, int $sessionId, int $gameId, int $playerId, Transaction $parentTransaction = null )
    {
        $this->id = $id;
        $this->parentTransaction = $parentTransaction;
        $this->type = $type;
        $this->amount = $amount;
        $this->uid = $uid;
        $this->gameId = $gameId;
        $this->sessionId = $sessionId;
        $this->playerId = $playerId;
    }
}