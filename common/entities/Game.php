<?php

namespace common\entities;

class Game {

    private $id;
    public function getId()
    {
        return $this->id;
    }

    private $uid;
    public function getUid()
    {
        return $this->uid;
    }

    private $name;
    public function getName()
    {
        return $this->name;
    }

    public function __construct(int $id, string $uid, string $name)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->name = $name;
    }
}
