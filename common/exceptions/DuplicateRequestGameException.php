<?php

namespace common\exceptions;

use common\entities\Transaction;

class DuplicateRequestGameException extends \Exception {
    /** @var Transaction $_transaction */
    private $_transaction;
    public function getTransaction(): Transaction
    {
        return $this->_transaction;
    }

    /**
     * @param Transaction $transaction
     * @param string $message
     */
    public function __construct(Transaction $transaction, string $message = 'Duplicate request')
    {
        $this->_transaction = $transaction;

        parent::__construct($message);
    }


}