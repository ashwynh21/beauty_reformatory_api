<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Chat;
  use br\Models\Upload;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;
  use finfo;
  
  class UploadController extends Controller
  {
    public function upload(Request $request, Response $response)
    {
      /**
       * This function is responsible for creating and validating file uploads
       * for the user who would like to attach a file to their message in a
       * circle they are a member of.
       */
      /** @var Chat $chat */
      $chat = $request->getAttribute('chat');
      /** @var string $file */
      $file = $request->getAttribute('upload');
      
      try {
        // Just a double check for the chat in case there are unseen ways
        if ($chat) {
          // then we begin the final upload
          /** @var Upload $upload */
          $upload = new Upload();
          $upload->setChat($chat);
          
          $finfo = new finfo(FILEINFO_MIME);
          
          $mime = $finfo->buffer(base64_decode($file));
          
          $upload->setType($mime);
          $upload->setUpload($file);
          
          $chat->addUpload($upload);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$FILE_UPLOAD_SUCCESS[0], $upload->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (ORMException | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function fetch(Request $request, Response $response)
    {
      
      /*
       * This method will handle a users file request on a message. Users validity will
       * be expected as clean at this layer so all we have to do is fetch the right file
       * and send it.
       */
      
      
      try {
        /** @var string $upload */
        $u = $request->getAttribute('upload');
        /** @var Chat $chat */
        $chat = $request->getAttribute('chat');
        
        /** @var Upload $upload */
        $upload = $chat->getUploads()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $u)))->first();
        
        if ($upload) {
          $file = base64_decode(stream_get_contents($upload->getUpload()));
          
          /*
           * I am going to set the header to the MIME type specified in the attachment so the user
           * is able to access the file from just about anywhere without too much of a hastle.
           */
          return $response->withHeader('Content-Type', $upload->getType())->write($file);
          
          /*
           * Keeping this line commented just in case, it will give the typical json response of the system
           */
          // return $response->withResponse(Strings::$FILE_UPLOAD_SUCCESS[0], $this->clean_attaches($attaches->toJSON()), true, 200);
        }
        
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | \Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  }
