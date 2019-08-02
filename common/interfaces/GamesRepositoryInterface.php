<?php

namespace common\interfaces;

use common\entities\Game;
use common\exceptions\GameNotFoundGameException;

interface GamesRepositoryInterface {
    /**
     * @param int $id
     * @return Game
     * @throws GameNotFoundGameException
     */
    public function getByPk(int $id): Game;

    /**
     * @param string $uid
     * @return Game
     * @throws GameNotFoundGameException
     */
    public function getByUid(string $uid): Game;

    /**
     * @return Game[]
     */
    public function getAll();
}