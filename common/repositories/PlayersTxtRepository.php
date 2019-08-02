<?php

namespace common\repositories;

use common\entities\Player;
use common\exceptions\InsufficientBalanceGameException;
use common\exceptions\PlayerNotFoundGameException;
use common\interfaces\PlayersRepositoryInterface;

// CSV-based repository example. You can create your own sql-based one
class PlayersTxtRepository
    extends TxtRepository
    implements PlayersRepositoryInterface
{
    public function __construct()
    {
        // Columns
        // 0 - id
        // 1 - login
        // 2 - currency
        // 3 - balance
        parent::__construct("players.csv", 4);
    }

    /**
     * @inheritDoc
     */
    public function getByPk(int $playerId): Player
    {
        $data = $this->_getItem($playerId);
        if( !$data ) {
            throw new PlayerNotFoundGameException("Player #{$playerId} is not found");
        }

        return new Player($data[0], $data[1], $data[2], $data[3]);
    }

    /**
     * @inheritDoc
     */
    public function getByLogin(string $login): Player
    {
        $data = $this->_getItem($login, 2);
        if( !$data ) {
            throw new PlayerNotFoundGameException("Player with login {$login} is not found");
        }

        return new Player($data[0], $data[1], $data[2], $data[3]);
    }

    /**
     * @inheritDoc
     */
    public function incrementPlayerBalance(Player $player, int $amount): Player
    {
        $this->_setItem($player->getId(), [
            $player->getId(),
            $player->getLogin(),
            $player->getCurrency(),
            $player->getBalance() + $amount,
        ]);
        return $this->getByPk($player->getId());
    }

    /**
     * @inheritDoc
     */
    public function decrementPlayerBalance(Player $player, int $amount): Player
    {
        if( $player->getBalance() - $amount < 0 ) {
            $message = sprintf("Player has only %.2f %s, but %.2f %s needed to withdraw",
                ($player->getBalance() / 100 ), $player->getCurrency(),
                ($amount / 100), $player->getCurrency() );
            throw new InsufficientBalanceGameException($message);
        }
        $this->_setItem($player->getId(), [
            $player->getId(),
            $player->getLogin(),
            $player->getCurrency(),
            $player->getBalance() - $amount,
        ]);
        return $this->getByPk($player->getId());
    }

}