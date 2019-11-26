<?php
  
  // controllers
  use br\Controllers\AccountController;
  use br\Controllers\AttachesController;
  use br\Controllers\ChatController;
  use br\Controllers\CircleController;
  use br\Controllers\EmotionController;
  use br\Controllers\FriendshipController;
  use br\Controllers\MemberController;
  use br\Controllers\MessageController;
  use br\Controllers\UploadController;
  use br\Controllers\UserController;
  use br\Middleware\AccountMiddleware;
  use br\Middleware\AttachesMiddleware;
  use br\Middleware\ChatMiddleware;
  use br\Middleware\CircleMiddleware;
  use br\Middleware\EmotionMiddleware;
  use br\Middleware\FriendshipMiddleware;
  use br\Middleware\MemberMiddleware;
  use br\Middleware\MessageMiddleware;
  use br\Middleware\UploadMiddleware;
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
        $this->post('/add', FriendshipController::class . ':addrequest');
        $this->post('/approve', FriendshipController::class . ':approve');
        $this->post('/decline', FriendshipController::class . ':decline');
        $this->post('/block', FriendshipController::class . ':block');
        $this->post('/cancel', FriendshipController::class . ':cancel');
        $this->post('/remove', FriendshipController::class . ':remove');
        $this->post('/get', FriendshipController::class . ':get');
      
        $this->group('/messaging', function () use ($container) {
          $this->post('/send', MessageController::class . ':send');
          $this->post('/paged', MessageController::class . ':paginate');
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
        ->add(new FriendshipMiddleware($container[EntityManager::class]));
  
      $this->group('/circles', function () use ($container) {
        $this->post('/create', CircleController::class . ':create');
        $this->post('/update', CircleController::class . ':update');
        $this->post('/remove', CircleController::class . ':remove');
        $this->post('/get', CircleController::class . ':getcircles');
    
        $this->group('/members', function () use ($container) {
          $this->post('/addmember', MemberController::class . ':addmember');
          $this->post('/removemember', MemberController::class . ':removemember');
          $this->post('/transfer', MemberController::class . ':transfer');
      
          $this->group('/chat', function () use ($container) {
            $this->post('/send', ChatController::class . ':send');
            $this->post('/poll', ChatController::class . ':poll');
            $this->post('/paged', ChatController::class . ':paginate');
        
            /*
             * These routes will deal with file uploads
             */
            $this->group('', function () use ($container) {
              $this->post('/upload', UploadController::class . ':upload');
              $this->post('/fetch', UploadController::class . ':fetch');
            })
              ->add(new UploadMiddleware($container[EntityManager::class]));
          })
            ->add(new ChatMiddleware($container[EntityManager::class]));
        })
          ->add(new MemberMiddleware($container[EntityManager::class]));
    
      })
        ->add(new CircleMiddleware($container[EntityManager::class]));
  
      $this->group('/emotions', function () use ($container) {
        $this->post('/create', EmotionController::class . ':create');
        $this->post('/get', EmotionController::class . ':get');
      })
        ->add(new EmotionMiddleware($container[EntityManager::class]));
    })// this middleware will wrap to restrict access to users only
    ->add(new UserMiddleware($container[EntityManager::class]));
  
    $this->group('/external', function () use ($container) {
      $this->post('/google_signin', AccountController::class . ':google');
      $this->post('/facebook_signin', AccountController::class . ':facebook');
    })
      ->add(new AccountMiddleware($container[EntityManager::class]));
  });
