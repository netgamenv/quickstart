<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;
use common\NetGameProvider;
use common\repositories\PlayersTxtRepository;
use common\repositories\SessionsTxtRepository;
use common\repositories\TransactionsTxtRepository;
use common\repositories\GamesTxtRepository;

$dotenv = Dotenv::create(__DIR__ . "/../");
$dotenv->load();

$gamesRepository = new GamesTxtRepository();
if( !array_key_exists("game_id", $_GET) ) { ?>
<h1>Select game to play</h1>
<ul>
<?php foreach( $gamesRepository->getAll() as $game ) { ?>
    <li><?= $game->getName() ?>: <a href="?game_id=<?= $game->getId() ?>&isMoney=true">Play</a> | <a href="?game_id=<?= $game->getId() ?>">Demo</a></li>
<?php } ?>
</ul>
<?php exit;}

$playersRepository = new PlayersTxtRepository();
$sessionsRepository = new SessionsTxtRepository();
$transactionsRepository = new TransactionsTxtRepository();

$provider = new NetGameProvider( $playersRepository, $gamesRepository, $sessionsRepository, $transactionsRepository, [
    'casino_id' => getenv("NETGAME_CASINO_ID"),
    'casino_secret' => getenv("NETGAME_CASINO_SECRET"),
    'casino_url' => getenv("NETGAME_CASINO_URL"),
]);

$isMoney = ( $_GET['isMoney'] ?? '' ) === 'true';
$gameId = $_GET['game_id'];

$player = $playersRepository->getByPk(1);
$game = $gamesRepository->getByPk($gameId);
$url = $provider->getGameUrl($game, !$isMoney, $player);
?>
<iframe width="1024" height="600" name="<?= time() ?>" id="main-game-frame" src="<?= $url ?>" frameborder="0" vspace="0" hspace="0" marginwidth="0" marginheight="0" seamless allowfullscreen></iframe>

