<?php
  
  use br\Helpers\Request;
  use br\Helpers\Response;
  use Slim\Http\Headers;
  
  require_once __DIR__ . '/../vendor/autoload.php';
  require_once __DIR__ . '/bootstrap.php';
  
  $app = new Slim\App($container);
  
  $container = $app->getContainer();

// Adjusting container for helper classes
  $container['response'] = function ($container) {
    $headers = new Headers(['Content-Type' => 'text/json']);
    $response = new Response(200, $headers);
    
    return $response->withProtocolVersion($container['settings']['httpVersion']);
  };
  $container['request'] = function ($container) {
    // Replace this class with your extended implementation
    return Request::createFromEnvironment($container['environment']);
  };
  
  $container['db'] = function () {
    $connection = new PDO('mysql:dbname=beauty_reformatory;host=localhost', 'ashwynh21', 'gbaby100');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $connection;
  };
