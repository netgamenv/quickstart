<?php

namespace common\interfaces;

use common\entities\Transaction;
use common\exceptions\TransactionNotFoundGameException;

interface TransactionsRepositoryInterface {
    /**
     * @param int $id
     * @return Transaction
     * @throws TransactionNotFoundGameException
     */
    public function getByPk(int $id): Transaction;

    /**
     * @param string $uid
     * @return Transaction|null
     */
    public function findByUid(string $uid);

    /**
     * @param string $uid
     * @return Transaction
     * @throws TransactionNotFoundGameException
     */
    public function getByUid(string $uid): Transaction;

    public function create(string $uid, string $type, int $amount, int $session, int $game, int $player, Transaction $parentTransaction = null): Transaction;
}