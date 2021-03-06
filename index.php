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

};

$worker->onClose = function ($connection) use($worker, $pdo) {
    echo 'Connection closed' . PHP_EOL;
};

$worker->onConnect = function ($connection) use($pdo, $worker) {
    $connection->send('Пользователь присоединился');

    /**
     * здесь, делаем запрос в базу на пользователей и перерисовываем меню контактов.
     * Код - 11
     */
    $users = $pdo->query('select * from users')->fetchAll();
    $response['code'] = 11;
    foreach ($users as $user) {
        $response['names'][] = $user['username'];
    }

    $response = json_encode($response);

    $connection->send($response);

};

$worker->onMessage = function ($connection, $date) use( $pdo, $worker) {
    $request = json_decode($date, true);

    if($request['status'] == 1) {
//        $pdo = (new \app\models\SQLiteConnection())->connect();
        $username = $pdo->query('select * from users u where u.username = ' . '\'' . $request['name'] . '\'' . 'limit 1' )->fetch();
        if($username === false) {
            $response['code'] = 2;
        }
        else {
            $response['code'] = 1;
            $response['name'] = $username['username'];
        }


        $response = json_encode($response);
        $connection->send($response);
    }

    if($request['status'] == 200) {
        $messages = $pdo->query('select mg.message, u.username, mg.createdate
                                            from messages_global mg
                                            join users u on u.id = mg.userid
                                          order by mg.createdate')->fetchAll();

        $response['code'] = 200;
        foreach ($messages as $message) {
            $response['messages'][] = [$message['message'], $message['username'], $message['createdate'] ];
        }
        $response = json_encode($response);
        $connection->send($response);

    }

    if($request['status'] == 500) {
//        echo $request['name'];
        $username = $pdo->query('select * from users u where u.username = ' . '\'' . $request['name'] . '\'' . 'limit 1' )->fetch();
//        var_dump($username);

        $sql = 'insert into messages_global(userid, createdate, message)
                values(' . $username['id'] . ',  strftime("%Y-%m-%d %H:%M", "now", "localtime"), \''.$request['message'].'\')';

        echo $sql;
        $pdo->prepare($sql)->execute();

        $messages = $pdo->query('select mg.message, u.username, mg.createdate
                                            from messages_global mg
                                            join users u on u.id = mg.userid
                                          order by mg.createdate')->fetchAll();

        $response['code'] = 500;
        foreach ($messages as $message) {
            $response['messages'][] = [$message['message'], $message['username'], $message['createdate'] ];
        }
        $response = json_encode($response);
        foreach($worker->connections as $clientConnection) {
            $clientConnection->send($response);
        }


//        echo $request['message'];

//        $messages = $pdo->query('select mg.message, u.username, mg.createdate
//                                            from messages_global mg
//                                            join users u on u.id = mg.userid
//                                          order by mg.createdate')->fetchAll();
//
//        $response['code'] = 200;
//        foreach ($messages as $message) {
//            $response['messages'][] = [$message['message'], $message['username'], $message['createdate'] ];
//        }
//        $response = json_encode($response);
//        $connection->send($response);

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
