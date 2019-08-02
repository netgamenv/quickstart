<?php

namespace common\entities;

use common\exceptions\InvalidRequestGameException;

/**
 * Class Request
 *
 * @property $session_id
 * @property $action_id
 * @property $parent_action_id
 * @property $original_action_id
 * @property $game_id
 * @property $action
 * @property $amount
 */

class Request
{
    const ACTION_BALANCE = 'balance';
    const ACTION_BET = 'bet';
    const ACTION_WIN = 'win';
    const ACTION_ROLLBACK = 'rollback';

    public $session_id;
    public $action_id;
    public $parent_action_id;
    public $original_action_id;
    public $game_id;
    public $action;
    public $amount;

    /**
     * @param string $content
     * @param string $secret
     *
     * @return string
     */
    public static function getSign(string $content, string $secret): string {
        return hash_hmac('sha256', $content, $secret);
    }

    public static function rules()
    {
        return [
            'session_id' => 'required',
            'action_id' => 'safe',
            'parent_action_id' => 'safe',
            'original_action_id' => 'safe',
            'game_id' => 'safe',
            'action' => 'required',
            'amount' => 'numerical',
        ];
    }

    /**
     * @param array $postData
     * @param string $secret
     *
     * @return Request
     *
     * @throws \Exception
     */
    public static function create(array $postData, string $secret){
        if( !$postData ) {
            throw new InvalidRequestGameException('Post data is empty');
        }

        $request = new self();

        if( !isset($postData['action'])) {
            // Balance request doesn't contain action
            $postData['action'] = self::ACTION_BALANCE;
        }

        foreach( self::rules() as $field => $rule ) {
            if( 'required' == $rule && !array_key_exists($field, $postData)) {
                throw new InvalidRequestGameException("Field {$field} is required!");
            }
            $request->{$field} = $postData[$field];
        }
        $signExpected = self::getSign($postData['raw_request'], $secret);
        $sign = $postData['sign'];
        if( $sign != $signExpected ) {
            throw new InvalidRequestGameException("Sign mismatch! Got '{$sign}', expected '{$signExpected}' ");
        }

        return $request;
    }


}