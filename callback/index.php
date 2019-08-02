<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;
use common\NetGameProvider;
use common\repositories\PlayersTxtRepository;
use common\repositories\SessionsTxtRepository;
use common\repositories\TransactionsTxtRepository;
use common\repositories\GamesTxtRepository;
use common\decorators\JsonRequestHandlerDecorator;

$dotenv = Dotenv::create(__DIR__ . "/../");
$dotenv->load();

$handler = new JsonRequestHandlerDecorator();
$handler->handle(function(array $requestData, string $requestDataRaw){

    $requestData['sign'] = $_SERVER['HTTP_X_REQUEST_SIGN'] ?? null;
    $requestData['raw_request'] = $requestDataRaw;

    $gamesRepository = new GamesTxtRepository();
    $playersRepository = new PlayersTxtRepository();
    $sessionsRepository = new SessionsTxtRepository();
    $transactionsRepository = new TransactionsTxtRepository();

    $provider = new NetGameProvider( $playersRepository, $gamesRepository, $sessionsRepository, $transactionsRepository, [
        'casino_id' => getenv("NETGAME_CASINO_ID"),
        'casino_secret' => getenv("NETGAME_CASINO_SECRET"),
        'casino_url' => getenv("NETGAME_CASINO_URL"),
    ]);

    return $provider->processCallback($requestData);
});

