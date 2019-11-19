<?php
  
  use br\Helpers\Request;
  use br\Helpers\Response;
  
  define('APP_ROOT', __DIR__);
  
  return array(
    'settings' => array(
        'displayErrorDetails' => true,
        'debug'               => true,
        'determineRouteBeforeAppMiddleware' => true,
      
      'doctrine' => array(
            // if true, metadata caching is forcefully disabled
            'dev_mode' => true,

            // path where the compiled metadata info will be cached
            // make sure the path exists and it is writable
            'cache_dir' => APP_ROOT . '/../resources/var/doctrine',

            // you should add any other path containing annotated entity classes
        'metadata_dirs' => array(APP_ROOT . '/Models'),
        
        'connection' => array(
                'driver' => 'pdo_mysql',
                'host' => 'localhost',
                'port' => 3306,
                'dbname' => 'beauty_reformatory',
                'user' => 'ashwynh21',
                'password' => 'gbaby100',
                'charset' => 'utf8'
        )
      ),
    ),
    'notFoundHandler' => function ($container) {
      return function (Request $request, Response $response) use ($container) {
        return $response->withStatus(404)
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(404)
          ->withResponse('Hi there, welcome to the Beauty Reformatory API!', $request->getParsedBody(), false, 404);
      };
    }
  );
