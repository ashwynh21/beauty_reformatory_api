<?php
  
  // controllers
  use br\Controllers\UserController;
  
  // middleware
  use br\Middleware\UserMiddleware;
  
  // dependencies
  use Doctrine\ORM\EntityManager;
  
  $app->group('/users', function() use($container) {
    $this->post('/authenticate', UserController::class . ':authenticate')
      ->add(new UserMiddleware($container[EntityManager::class]));
    $this->post('/create', UserController::class . ':create')
      ->add(new UserMiddleware($container[EntityManager::class]));
    $this->post('/refresh', UserController::class . ':refresh')
      ->add(new UserMiddleware($container[EntityManager::class]));
  });
