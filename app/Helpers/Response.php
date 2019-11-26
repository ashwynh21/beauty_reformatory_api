<?php
  
  
  namespace br\Helpers;
  
  
  class Response extends  \Slim\Http\Response {
    private $data;
    
    /**
     * @return mixed
     */
    public function getData() {
      return $this->data;
    }
    
    /**
     * @param $data
     * @return Response
     */
    public function putData($data) {
      $clone = clone $this;
      $clone->data = $data;
      return $clone;
    }
    
    /**
     * @param string $message
     * @param string | array | object $payload
     * @param bool $result
     * @param int $status
     * @param string $debug
     * @return Response
     */
    public function withResponse($message = '', $payload = '', $result = false, $status = 401, $debug = ''){
      $response = [
        'message' => $message,
        'payload' => $payload,
        'result' => $result,
        'status' => $status,
        'debug' => $debug
      ];
      $this->data = $response;
      return $this->withJson($response, $status);
    }
  }
