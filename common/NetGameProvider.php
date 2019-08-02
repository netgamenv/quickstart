<?php

namespace common;

use common\entities\Game;
use common\entities\Player;
use common\entities\Request;
use common\entities\Session;
use common\entities\Transaction;
use common\exceptions\DuplicateRequestGameException;
use common\exceptions\GameNotFoundGameException;
use common\exceptions\IllegalRequestGameException;
use common\exceptions\InsufficientBalanceGameException;
use common\exceptions\InvalidResponseGameException;
use common\exceptions\PlayerNotFoundGameException;
use common\exceptions\TransactionNotFoundGameException;
use common\interfaces\GamesRepositoryInterface;
use common\interfaces\PlayersRepositoryInterface;
use common\interfaces\SessionsRepositoryInterface;
use common\interfaces\TransactionsRepositoryInterface;
use GuzzleHttp\Client;
use common\facades\Logging;

class NetGameProvider
{
    const API_SUCCESS_STATUS = 0;
    const API_FAIL_STATUS = 1;

    private $_playersRepository;
    private $_sessionsRepository;
    private $_transactionsRepository;
    private $_gamesRepository;

    private $_casinoId;
    private $_casinoSecret;
    private $_casinoUrl;

    public function __construct(
        PlayersRepositoryInterface $playersRepository,
        GamesRepositoryInterface $gamesRepository,
        SessionsRepositoryInterface $sessionsRepository,
        TransactionsRepositoryInterface $transactionsRepository,
        array $config
    )
    {
        $this->_playersRepository = $playersRepository;
        $this->_gamesRepository = $gamesRepository;
        $this->_transactionsRepository = $transactionsRepository;
        $this->_sessionsRepository = $sessionsRepository;

        $this->_casinoId = $config['casino_id'];
        $this->_casinoSecret = $config['casino_secret'];
        $this->_casinoUrl = $config['casino_url'];
    }

    /**
     * Request to API
     *
     * @param string $action
     * @param array $params
     *
     * @param bool $isPost
     * @return array
     *
     * @throws
     *
     * @codeCoverageIgnore
     */
    private function _request(string $action, array $params, bool $isPost = true ): array {

        $url = rtrim($this->_casinoUrl, "/") . '/' . ltrim($action,"/");

        $client = new Client();

        if( $isPost ) {
            $requestRaw = json_encode($params);

            $headers = [
                'X-REQUEST-SIGN' => Request::getSign($requestRaw, $this->_casinoSecret),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];
            Logging::info("Request: {$url}\nHeaders:" . json_encode($headers,JSON_PRETTY_PRINT) . "\nParams: " . json_encode($params,JSON_PRETTY_PRINT) );

            $response = $client->post($url, [
                'body' => $requestRaw,
                'headers' => $headers,
            ]);

        } else {
            Logging::info("Request: {$url}\nParams: " . json_encode($params,JSON_PRETTY_PRINT) );

            $response = $client->get($url, [
                'query' => $params,
            ]);

        }

        $responseDataRaw = $response->getBody()->getContents();
        $responseData = json_decode($responseDataRaw, true);
        if (!$responseData) {
            throw new \Exception("Failed to decode response: {$responseDataRaw}\nRequest: {$url}\nParams: " . json_encode($params,
                    JSON_PRETTY_PRINT));
        }

        Logging::info("Response: " . json_encode($responseData,JSON_PRETTY_PRINT) );

        return $responseData;
    }

    /**
     * @param Game $game
     * @param bool $isDemo
     * @param Player $player
     * @return mixed|string
     * @throws PlayerNotFoundGameException
     * @throws \Exception
     */
    public function getGameUrl(Game $game, bool $isDemo, Player $player)
    {
        if ( $isDemo ) {
            $launcherParams = [
                'casino_id' => $this->_casinoId,
                'game' => $game->getUid(),
            ];

            $responseData = $this->_request('/online/demo/', $launcherParams);
            $launchUrl = $responseData['launch_game'];

        } else {

            $launcherParams = [
                'casino_id' => $this->_casinoId,
                'game' => $game->getUid(),
                'login' => $player->getLogin(),
                'currency' => $player->getCurrency(),
                'locale' => 'ru',
            ];

            $responseData = $this->_request('/online/create/', $launcherParams);
            $sessionId = $responseData['session_id'] ?? '';
            if( !$sessionId ){
                throw new InvalidResponseGameException('Response does not contain session_id');
            }

            $session = $this->_sessionsRepository->findByUid($sessionId);
            if( !$session ) {
                $this->_sessionsRepository->create($player->getId(), $game->getId(), $sessionId);
            }

            $launchUrl = $responseData['launch_game'];
        }

        return $launchUrl;
    }

    public function processCallback(array $requestData)
    {
        try {
            $request = Request::create($requestData, $this->_casinoSecret);
            if( $request->action != Request::ACTION_BALANCE ) {
                $this->checkDuplicate($request);
            }
            $session = $this->_sessionsRepository->getByUid($request->session_id);

            switch($request->action) {
                case Request::ACTION_BALANCE:
                    $responseData = $this->processBalance($request, $session);
                    break;
                case Request::ACTION_BET:
                    $responseData = $this->processBet($request, $session);
                    break;
                case Request::ACTION_WIN:
                    $responseData = $this->processWin($request, $session);
                    break;
                case Request::ACTION_ROLLBACK:
                    $responseData = $this->processRollback($request, $session);
                    break;
                default:
                    throw new IllegalRequestGameException("Undefined request type: " . $request->action);
            }

        } catch( DuplicateRequestGameException $e ) {
            Logging::warning('Duplicate request: ' . json_encode($requestData, JSON_PRETTY_PRINT));
            $transaction = $e->getTransaction();
            $balance = 0;
            try {
                $this->_playersRepository->getByPk($transaction->getPlayerId());
            }catch (\Throwable $e){
                Logging::error($e);;
            }
            $responseData = [
                'action_id' => $transaction->getUid(),
                'balance' => $balance,
                'tx_id' => intval($transaction->getId()), // protection against wrong json_encode behavior. We need id, but it creates string
            ];
        } catch (InsufficientBalanceGameException $e) {

            Logging::error($e);
            $responseData = [
                'code' => 100,
                'message' => 'Insufficient balance',
            ];

        } catch( PlayerNotFoundGameException $e ) {

            Logging::error($e);
            $responseData = [
                'code' => 100,
                'message' => 'Player is not found',
            ];

        } catch( GameNotFoundGameException $e ) {

            Logging::error($e);
            $responseData = [
                'code' => 100,
                'message' => 'Game is not found',
            ];

        } catch (TransactionNotFoundGameException $e) {

            Logging::error($e);
            $responseData = [
                'code' => 100,
                'message' => 'Operation is not found',
            ];

        } catch (IllegalRequestGameException $e) {

            Logging::error($e);

            $responseData = [
                'code' => 100,
                'message' => 'Invalid request',
            ];

        } catch (\Throwable $e) {

            Logging::error("Request was: " . json_encode($requestData, JSON_PRETTY_PRINT));

            Logging::error($e);

            $responseData = [
                'code' => 100,
                'message' => 'Internal error',
            ];
        }


        return $responseData;
    }

    /**
     * @param Request $request
     * @throws DuplicateRequestGameException
     */
    private function checkDuplicate(Request $request){
        /** @var Transaction $transaction */
        $transaction = $this->_transactionsRepository->findByUid($request->action_id);
        if( $transaction ) {
            throw new DuplicateRequestGameException($transaction);
        }
    }

    /**
     * @param Request $request
     * @param Session $session
     *
     * @return array
     *
     * @throws PlayerNotFoundGameException
     */
    private function processBalance(Request $request, Session $session) {

        $player = $this->_playersRepository->getByPk($session->getPlayerId());

        $responseData = [
            'balance' => $player->getBalance(),
        ];

        return $responseData;
    }

    /**
     * @param Request $request
     * @param Session $session
     *
     * @return array
     *
     * @throws InsufficientBalanceGameException
     * @throws GameNotFoundGameException
     * @throws PlayerNotFoundGameException
     */
    private function processBet(Request $request, Session $session)
    {
        $game = $this->_gamesRepository->getByUid($request->game_id);
        $player = $this->_playersRepository->getByPk($session->getPlayerId());

        $transaction = $this->_transactionsRepository->create( $request->action_id, 'bet', $request->amount, $session->getId(), $game->getId(), $player->getId());
        $player = $this->_playersRepository->decrementPlayerBalance($player, $request->amount );

        $responseData = [
            'action_id' => $request->action_id,
            'balance' => $player->getBalance(),
            'tx_id' => intval($transaction->getId()), // protection against wrong json_encode behavior. We need id, but it creates string
        ];

        return $responseData;
    }

    /**
     * @param Request $request
     * @param Session $session
     *
     * @return array
     *
     * @throws TransactionNotFoundGameException
     * @throws GameNotFoundGameException
     * @throws PlayerNotFoundGameException
     */
    private function processWin(Request $request, Session $session)
    {
        $game = $this->_gamesRepository->getByUid($request->game_id);
        $parentTransaction = $this->_transactionsRepository->getByUid($request->parent_action_id);

        $player = $this->_playersRepository->getByPk($session->getPlayerId());
        $transaction = $this->_transactionsRepository->create( $request->action_id, 'win', $request->amount, $session->getId(), $game->getId(), $player->getId(), $parentTransaction);
        $player = $this->_playersRepository->incrementPlayerBalance($player, $request->amount );

        $responseData = [
            'action_id' => $request->action_id,
            'balance' => $player->getBalance(),
            'tx_id' => intval($transaction->getId()), // protection against wrong json_encode behavior. We need id, but it creates string
        ];

        return $responseData;
    }

    /**
     * @param Request $request
     * @param Session $session
     *
     * @return array
     *
     * @throws GameNotFoundGameException
     * @throws IllegalRequestGameException
     * @throws TransactionNotFoundGameException
     * @throws PlayerNotFoundGameException
     */
    private function processRollback(Request $request, Session $session)
    {
        $originalTransaction = $this->_transactionsRepository->getByUid($request->original_action_id);

        $game = $this->_gamesRepository->getByUid($request->game_id);
        if( $game->getId() != $originalTransaction->getGameId() ) {
            throw new IllegalRequestGameException("Invalid game id");
        }

        if( 'bet' != $originalTransaction->getType() ) {
            throw new IllegalRequestGameException("Invalid request type");
        }

        if( $request->amount != $originalTransaction->getAmount() ) {
            throw new IllegalRequestGameException("Invalid request amount");
        }

        $player = $this->_playersRepository->getByPk($session->getPlayerId());
        $player = $this->_playersRepository->incrementPlayerBalance($player, $request->amount );
        $transaction = $this->_transactionsRepository->create( $request->action_id, 'rollback', $request->amount, $session->getId(), $game->getId(), $player->getId(), $originalTransaction);

        $responseData = [
            'action_id' => $request->action_id,
            'balance' => $player->getBalance(),
            'tx_id' => intval($transaction->getId()), // protection against wrong json_encode behavior. We need id, but it creates string
        ];

        return $responseData;
    }
}