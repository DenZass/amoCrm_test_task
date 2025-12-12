<?php

declare(strict_types=1);

use App\Core\App;

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: *");
    exit(0);
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-type");
header("Access-Control-Allow-Headers: *");

require_once './autoload.php';


$app = new App();

//эндпоинт для задания 2.1
$app->router->get('/add_to_client_waiting_list', 'App\Controllers\AmoCrmController', 'addToClientWaitingList');
//эндпоинт для задания 2.2
$app->router->get('/copy_at_stage_client_confirmed', 'App\Controllers\AmoCrmController', 'copyLeadsAtStageClientConfirmed');


$app->run();

