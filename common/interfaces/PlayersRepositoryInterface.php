<?php

namespace common\interfaces;

use common\entities\Player;
use common\exceptions\InsufficientBalanceGameException;
use common\exceptions\PlayerNotFoundGameException;

interface PlayersRepositoryInterface {
    /**
     * @param int $id
     * @return Player
     * @throws PlayerNotFoundGameException
     */
    public function getByPk(int $id): Player;

    /**
     * @param string $login
     * @return Player
     * @throws PlayerNotFoundGameException
     */
    public function getByLogin(string $login): Player;

    /**
     * @param Player $player
     * @param int $amount
     * @return Player
     */
    public function incrementPlayerBalance(Player $player, int $amount): Player;

    /**
     * @param Player $player
     * @param int $amount
     * @return Player
     * @throws InsufficientBalanceGameException
     */
    public function decrementPlayerBalance(Player $player, int $amount): Player;
}