<?php
  
  // controllers
  use br\Controllers\AttachesController;
  use br\Controllers\FriendsController;
  use br\Controllers\MessageController;
  use br\Controllers\UserController;
  use br\Middleware\AttachesMiddleware;
  use br\Middleware\FriendsMiddleware;
  use br\Middleware\MessageMiddleware;
  use br\Middleware\UserMiddleware;
  use Doctrine\ORM\EntityManager;
  
  // middleware
  
  // dependencies
  
  $app->group('/users', function() use($container) {
    $this->post('/authenticate', UserController::class . ':authenticate')
      ->add(new UserMiddleware($container[EntityManager::class]));
    $this->post('/create', UserController::class . ':create')
      ->add(new UserMiddleware($container[EntityManager::class]));
    $this->post('/refresh', UserController::class . ':refresh')
      ->add(new UserMiddleware($container[EntityManager::class]));
  
    $this->group('', function () use ($container) {
      /*
       * Wrapping this middleware at the bottom to authenticate requests to restrict the groups to users only
       */
    
      $this->group('/profile', function () use ($container) {
        $this->post('/get', UserController::class . ':getprofile');
        $this->post('/update', UserController::class . ':update');
      });
    
      $this->group('/friends', function () use ($container) {
        $this->post('/add', FriendsController::class . ':addrequest');
        $this->post('/approve', FriendsController::class . ':approve');
        $this->post('/decline', FriendsController::class . ':decline');
        $this->post('/block', FriendsController::class . ':block');
        $this->post('/cancel', FriendsController::class . ':cancel');
        $this->post('/remove', FriendsController::class . ':remove');
        $this->post('/get', FriendsController::class . ':get');
      
        $this->group('/messaging', function () use ($container) {
          $this->post('/send', MessageController::class . ':send');
          $this->post('/poll', MessageController::class . ':poll');
        
          /*
           * These routes will deal with file uploads
           */
          $this->group('', function () use ($container) {
            $this->post('/upload', AttachesController::class . ':upload');
            $this->post('/fetch', AttachesController::class . ':fetch');
          })
            ->add(new AttachesMiddleware($container[EntityManager::class]));
        })
          ->add(new MessageMiddleware($container[EntityManager::class]));
      })
        ->add(new FriendsMiddleware($container[EntityManager::class]));
    })// this middleware will wrap to restrict access to users only
    ->add(new UserMiddleware($container[EntityManager::class]));
  });
