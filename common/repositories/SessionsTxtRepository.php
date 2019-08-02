<?php

namespace common\repositories;

use common\entities\Session;
use common\exceptions\SessionNotFoundGameException;
use common\interfaces\SessionsRepositoryInterface;

// CSV-based repository example. You can create your own sql-based one
class SessionsTxtRepository
    extends TxtRepository
    implements SessionsRepositoryInterface {

    public function __construct()
    {
        // Columns
        // 0 - id
        // 1 - uid
        // 2 - player_id
        // 3 - game_id
        parent::__construct("sessions.csv", 4);
    }

    private function createSession(array $data): Session
    {
        $id = intval($data[0]);
        $uid = $data[1];
        $playerId = $data[2];
        $gameId = $data[3];

        return new Session($id, $uid, $playerId, $gameId);
    }

    /**
     * @inheritDoc
     */
    public function getByPk(int $id): Session {
        $data = $this->_getItem($id);
        if( !$data ) {
            throw new SessionNotFoundGameException("Session with id {$id} is not found");
        }

        return $this->createSession($data);
    }

    /**
     * @inheritDoc
     */
    public function findByUid(string $uid) {
        $data = $this->_getItem($uid, 2);
        if( !$data ) {
            return null;
        }

        return $this->createSession($data);
    }

    /**
     * @inheritDoc
     */
    public function getByUid(string $uid): Session {
        $session = $this->findByUid($uid);
        if( !$session ) {
            throw new SessionNotFoundGameException("Session with uid {$uid} is not found");
        }

        return $session;
    }

    /**
     * @inheritDoc
     */
    public function create(int $playerId, int $gameId, string $sessionUid): Session
    {
        $id = $this->_getMaxId();
        $this->_setItem($id, [
            $id,
            $sessionUid,
            $playerId,
            $gameId,
        ]);
        return $this->getByPk($id);
    }


}