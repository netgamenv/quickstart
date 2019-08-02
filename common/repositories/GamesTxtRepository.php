<?php

namespace common\repositories;

use common\entities\Game;
use common\exceptions\GameNotFoundGameException;
use common\interfaces\GamesRepositoryInterface;

// CSV-based repository example. You can create your own sql-based one
class GamesTxtRepository
    extends TxtRepository
    implements GamesRepositoryInterface {

    public function __construct()
    {
        // Columns
        // 0 - id
        // 1 - uid
        // 2 - name
        parent::__construct("games.csv", 3);
    }


    /**
     * @inheritDoc
     */
    public function getByPk(int $id): Game
    {
        $data = $this->_getItem($id);
        if( !$data ) {
            throw new GameNotFoundGameException("Game with id {$id} is not found");
        }

        return new Game($data[0], $data[1], $data[2]);
    }


    /**
     * @inheritDoc
     */
    public function getByUid(string $uid): Game {
        $data = $this->_getItem($uid, 2);
        if( !$data ) {
            throw new GameNotFoundGameException("Game with uid {$uid} is not found");
        }

        return new Game($data[0], $data[1], $data[2]);
    }

    /**
     * @return Game[]
     */
    public function getAll()
    {
        $result = [];
        foreach( $this->_getItems() as $data ) {
            $result[] = new Game($data[0], $data[1], $data[2]);
        }
        return $result;
    }

}