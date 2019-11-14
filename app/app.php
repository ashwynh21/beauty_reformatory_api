<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

$app = new Slim\App($container);

$container = $app->getContainer();

// Adjusting container for helper classes
$container['response'] = function($container) {
    $headers = new \Slim\Http\Headers(['Content-Type' => 'application/json; charset=UTF-8']);
    $response = new \br\Helpers\Response(200, $headers);

    return $response->withProtocolVersion($container->get('settings')['httpVersion']);
};
$container['request'] = function ($container) {
  // Replace this class with your extended implementation
  return \br\Helpers\Request::createFromEnvironment($container['environment']);
};

$container['db'] = function(){
    $connection = new PDO('mysql:dbname=beauty_reformatory;host=localhost', 'ashwynh21', 'gbaby100');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $connection;
};
