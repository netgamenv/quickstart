<?php

namespace common\entities;

class Session {
    private $id;
    public function getId(): int
    {
        return $this->id;
    }

    private $uid;
    public function getUid(): string
    {
        return $this->uid;
    }

    private $player;
    public function getPlayerId(): int
    {
        return $this->player;
    }

    private $game;
    public function getGameId(): int
    {
        return $this->game;
    }

    public function __construct(int $id, string $uid, int $player, int $game)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->player = $player;
        $this->game = $game;
    }
}