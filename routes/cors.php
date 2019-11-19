<?php
  
  use br\Helpers\Request;
  use br\Helpers\Response;

//  Handling CORS with a simple lazy CORS

$app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
    return $response->withStatus(200);
});
$app->add(function (Request $req, Response $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
});
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function(Request $req, Response $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
  
  return $handler($req, $res)
    ->withHeader('Content-Type', 'application/json')
    ->withStatus(404)
    ->withResponse('Oops, we do not understand!', $req->getParsedBody(), false, 404);
});
