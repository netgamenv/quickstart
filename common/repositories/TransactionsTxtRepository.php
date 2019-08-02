<?php

namespace common\repositories;

use common\entities\Transaction;
use common\exceptions\TransactionNotFoundGameException;
use common\interfaces\TransactionsRepositoryInterface;

// CSV-based repository example. You can create your own sql-based one
class TransactionsTxtRepository
    extends TxtRepository
    implements TransactionsRepositoryInterface
{
    public function __construct()
    {
        // Columns
        // 0 - id
        // 1 - parent_id
        // 2 - uid
        // 3 - type
        // 4 - amount
        // 5 - session_id
        // 6 - game_id
        // 7 - player_id
        parent::__construct("transactions.csv", 8);
    }

    public function getByPk(int $id): Transaction
    {
        $data = $this->_getItem($id);
        if( !$data ) {
            throw new TransactionNotFoundGameException("Transaction with id {$id} is not found");
        }

        $sessionId = $data[5];
        $gameId = $data[6];
        $playerId = $data[7];
        if( $data[1] ) {
            $parentTransaction = $this->getByPk($data[1]);
        } else {
            $parentTransaction = null;
        }
        return new Transaction($data[0], $data[2], $data[3], $data[4], $sessionId, $gameId, $playerId, $parentTransaction );
    }

    public function findByUid(string $uid)
    {
        $data = $this->_getItem($uid, 3);
        if( !$data ) {
            return null;
        }

        return $this->getByPk($data[0]);
    }

    public function getByUid(string $uid): Transaction
    {
        $transaction = $this->findByUid($uid);
        if( !$transaction ) {
            throw new TransactionNotFoundGameException("Transaction with action_id {$uid} is not found");
        }

        return $transaction;
    }

    public function create(string $uid, string $type, int $amount, int $sessionId, int $gameId, int $playerId, Transaction $parentTransaction = null): Transaction
    {
        $id = $this->_getMaxId();
        $parentId = $parentTransaction ? $parentTransaction->getId() : '';
        $this->_setItem($id, [
            $id,
            $parentId,
            $uid,
            $type,
            $amount,
            $sessionId,
            $gameId,
            $playerId,
        ]);
        return $this->getByPk($id);
    }

}