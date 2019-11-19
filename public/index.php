<?php
require __DIR__ . '/../app/app.php';

require __DIR__ . '/../routes/users.php';
require __DIR__ . '/../routes/cors.php';
  
  try {
    $app->run();
  } catch (Throwable $e) {
    json_encode($e);
  }
