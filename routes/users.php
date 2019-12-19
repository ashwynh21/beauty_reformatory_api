<?php
  
  // controllers
  use br\Controllers\AbuseController;
  use br\Controllers\AccountController;
  use br\Controllers\AttachesController;
  use br\Controllers\ChatController;
  use br\Controllers\CircleController;
  use br\Controllers\DailyController;
  use br\Controllers\EmotionController;
  use br\Controllers\EntryController;
  use br\Controllers\FriendshipController;
  use br\Controllers\GoalController;
  use br\Controllers\JournalController;
  use br\Controllers\MemberController;
  use br\Controllers\MessageController;
  use br\Controllers\NoteController;
  use br\Controllers\TaskController;
  use br\Controllers\UploadController;
  use br\Controllers\UserController;
  use br\Middleware\AbuseMiddleware;
  use br\Middleware\AccountMiddleware;
  use br\Middleware\AttachesMiddleware;
  use br\Middleware\ChatMiddleware;
  use br\Middleware\CircleMiddleware;
  use br\Middleware\DailyMiddleware;
  use br\Middleware\EmotionMiddleware;
  use br\Middleware\EntryMiddleware;
  use br\Middleware\FriendshipMiddleware;
  use br\Middleware\GoalMiddleware;
  use br\Middleware\JournalMiddlware;
  use br\Middleware\MemberMiddleware;
  use br\Middleware\MessageMiddleware;
  use br\Middleware\NoteMiddleware;
  use br\Middleware\TaskMiddleware;
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
    $this->post('/recover', UserController::class . ':recover')
      ->add(new UserMiddleware($container[EntityManager::class]));
    $this->post('/find', UserController::class . ':find')
      ->add(new UserMiddleware($container[EntityManager::class]));
  
    $this->group('', function () use ($container) {
      /*
       * Wrapping this middleware at the bottom to authenticate requests to restrict the groups to users only
       */
    
      $this->group('/profile', function () use ($container) {
        $this->post('/get', UserController::class . ':getprofile');
        $this->post('/update', UserController::class . ':update');
      });
  
      $this->group('/abuse', function () use ($container) {
        $this->post('/report', AbuseController::class . ':report')
          ->add(new AbuseMiddleware($container[EntityManager::class]));
      });
      
      $this->group('/friends', function () use ($container) {
        $this->post('/add', FriendshipController::class . ':addrequest');
        $this->post('/approve', FriendshipController::class . ':approve');
        $this->post('/decline', FriendshipController::class . ':decline');
        $this->post('/block', FriendshipController::class . ':block');
        $this->post('/cancel', FriendshipController::class . ':cancel');
        $this->post('/remove', FriendshipController::class . ':remove');
        $this->post('/get', FriendshipController::class . ':get');
  
        $this->post('/getinitiated', FriendshipController::class . ':getinitiated');
        $this->post('/getsubjected', FriendshipController::class . ':getsubjected');
      
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
  
      $this->group('/journal', function () use ($container) {
        $this->post('/create', JournalController::class . ':create');
        $this->post('/toggle', JournalController::class . ':toggle');
  
        $this->group('/daily', function () use ($container) {
          $this->post('/add', DailyController::class . ':add');
          $this->post('/get', DailyController::class . ':get');
          $this->post('/update', DailyController::class . ':update');
          $this->post('/complete', DailyController::class . ':complete');
        })
          ->add(new DailyMiddleware($container[EntityManager::class]));
        
        $this->group('/entries', function () use ($container) {
          $this->post('/add', EntryController::class . ':add');
          $this->post('/get', EntryController::class . ':get');
          $this->post('/update', EntryController::class . ':update');
        })
          ->add(new EntryMiddleware($container[EntityManager::class]));
  
        $this->group('/goals', function () use ($container) {
          $this->post('/add', GoalController::class . ':add');
          $this->post('/get', GoalController::class . ':get');
          $this->post('/update', GoalController::class . ':update');
          $this->post('/complete', GoalController::class . ':complete');
        })
          ->add(new GoalMiddleware($container[EntityManager::class]));
        $this->group('/tasks', function () use ($container) {
          $this->post('/add', TaskController::class . ':add');
          $this->post('/get', TaskController::class . ':get');
          $this->post('/update', TaskController::class . ':update');
          $this->post('/complete', TaskController::class . ':complete');
    
          $this->group('/notes', function () use ($container) {
            $this->post('/take', NoteController::class . ':take');
            $this->post('/get', NoteController::class . ':get');
            $this->post('/update', NoteController::class . ':update');
            $this->post('/remove', NoteController::class . ':remove');
          })
            ->add(new NoteMiddleware($container[EntityManager::class]));
        })
          ->add(new TaskMiddleware($container[EntityManager::class]));
      })
        ->add(new JournalMiddlware($container[EntityManager::class]));
    })// this middleware will wrap to restrict access to users only
    ->add(new UserMiddleware($container[EntityManager::class]));
  
    $this->group('/external', function () use ($container) {
      $this->post('/google_signin', AccountController::class . ':googleauth');
      $this->post('/facebook_signin', AccountController::class . ':facebookauth');
      $this->post('/google_signup', AccountController::class . ':googlecreate');
      $this->post('/facebook_signup', AccountController::class . ':facebookcreate');
    })
      ->add(new AccountMiddleware($container[EntityManager::class]));
  });
