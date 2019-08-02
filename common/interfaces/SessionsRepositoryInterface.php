<?php

namespace common\interfaces;

use common\entities\Session;
use common\exceptions\SessionNotFoundGameException;

interface SessionsRepositoryInterface {
    /**
     * @param int $id
     * @return Session
     * @throws SessionNotFoundGameException
     */
    public function getByPk(int $id): Session;

    /**
     * @param string $uid
     * @return Session|null
     */
    public function findByUid(string $uid);

    /**
     * @param string $uid
     * @return Session
     * @throws SessionNotFoundGameException
     */
    public function getByUid(string $uid): Session;

    /**
     * @param int $playerId
     * @param int $gameId
     * @param string $sessionUid
     * @return Session
     */
    public function create(int $playerId, int $gameId, string $sessionUid): Session;
}