<?php
require 'vendor/autoload.php';
require 'autoload.php';

/**
 * Код
 * 1 - пользователь авторизован
 * 2 - ошибка авторизации
 * 11 - получение всех пользователей с бд
 */
$worker = new \Workerman\Worker('websocket://127.0.0.1:8001');
$pdo = (new \app\models\SQLiteConnection())->connect();
$worker->count = 1;

$worker->onWorkerStart = function($worker) use($pdo)
{
    /**
     * здесь, каждые n секунд, делаем запрос в базу на пользователей и перерисовываем меню контактов.
     * Код - 11
     */
    \Workerman\Lib\Timer::add(3, function() use($pdo, $worker) {
        $users = $pdo->query('select * from users')->fetchAll();
        $response['code'] = 11;
        foreach ($users as $user) {
            $response['names'][] = $user['username'];
        }

        $response = json_encode($response);
        foreach($worker->connections as $clientConnection) {
            $clientConnection->send($response);
        }
    });
};

$worker->onClose = function ($connection) use($worker) {
    echo 'Connection closed' . PHP_EOL;
};

$worker->onConnect = function ($connection) use($pdo, $worker) {
    $connection->send('Пользователь присоединился');



    /**
     * каждые три секунды отправляем инфу об актуальных пользователях
     */

};

$worker->onMessage = function ($connection, $date) use( $pdo, $worker) {
    $request = json_decode($date, true);

    if($request['status'] == 1) {
        $pdo = (new \app\models\SQLiteConnection())->connect();
        $username = $pdo->query('select * from users u where u.username = ' . '\'' . $request['name'] . '\'' . 'limit 1' )->fetch();
        if($username === false) {
            $responce['code'] = 2;
        }
        else {
            $responce['code'] = 1;
            $responce['name'] = $username['username'];
        }


        $responce = json_encode($responce);
        $connection->send($responce);
    }

//    echo($request['name']);
//    $response = $request['name'];
////    echo(count($worker->connections));
//    foreach($worker->connections as $clientConnection) {
//        $clientConnection->send($response);
//    }
};




//echo $_SESSION['hello'];

//use app\models\SQLiteConnection;

//$pdo = new SQLiteConnection();
//$connect = $pdo->connect();
//////$connect = new PDO('sqlite:db/chat');
//$data = $connect->query('select id, username from users');
//$row = $data->fetchAll();

//var_dump($row);

//
//$users = [];
//$color = ['#85144b', '#ab81a3', '#883526', '#0277BD', '#015d06', '#f5bf1e'];
//
//$worker->onClose = function ($connection) use($worker, &$users) {
//
//    $response['message'] = ' корабль покинули ' . $users[$connection->id]['name'] . ' :( ';
//    $response['color'] = $users[$connection->id]['color'];
//    $response['name'] = $users[$connection->id]['name'];
//    $response['status'] = 2;
//    unset($users[$connection->id]);
//    $response = json_encode($response);
//
//    foreach($worker->connections as $clientConnection) {
//        $clientConnection->send($response);
//    }
//
//};
//





//
//$worker->onMessage = function ($connection, $data) use ($worker, &$users, $color) {
//
//    $request = json_decode($data, true);
//    $response = ['message' => 'something useless'];
//
//    if(! $users[$connection->id]) {
//        $users[$connection->id]['name'] = $request['name'];
//        $numberColor = rand(0,5);
//        $users[$connection->id]['color'] = $color[$numberColor];
//    }
//
//    if($request['status'] == 100) {
//        $response['message'] = $request['message'];
//        $response['status'] = 100;
//        $response['color'] = $users[$connection->id]['color'];
//        $response['name'] = $users[$connection->id]['name'];
//    }
//
////    var_dump($request);
//
//    if($request['status'] == 4) {
////        var_dump($users);
////        echo $users[$connection->id];
//        $response['color'] = $users[$connection->id]['color'];
//        $response['message'] =  $users[$connection->id]['name'] . ' пытается что то написать....';
//        $response['status'] = 4;
//        $response['name'] = $users[$connection->id]['name'];
//    }
//
//
//
//    if($request['status'] == 1) {
//        $response['message'] = 'Пользователь ' . $users[$connection->id]['name'] . ' прибыл на станцию :) ';
//        $response['status'] = 1;
//        $response['name'] = $users[$connection->id]['name'];
//        $response['color'] = $users[$connection->id]['color'];
//    }
//
//
//
//    $response = json_encode($response);
////    echo(count($worker->connections));
//    foreach($worker->connections as $clientConnection) {
//        $clientConnection->send($response);
//
//    }
//};
//

//
//$worker->onMessage = function($connection, $data) {
//   $connection->send($data);
//};
//
//
//
\Workerman\Worker::runAll();
//
//
