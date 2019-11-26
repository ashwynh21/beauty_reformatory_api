<?php
  
  
  namespace br\Controllers;
  
  // helpers
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Circle;
  use br\Models\User;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;
  use finfo;
  
  class CircleController extends Controller
  {
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response)
    {
      /*
       * As the name suggests this function is the controller function for creating a circle
       * This function will be used by users, the way im going to implement this is using the
       * user's object to run a function that will cascade the circle so that doctrine creates
       * the circle.
       */
      try {
        /** @var Circle $circle */
        $circle = Circle::fromJSON($request->getAttribute('circle'));
        /** @var User $user */
        $user = $request->getAttribute('user');
        
        if ($circle) {
          /*
           * Once checked we get the user to add the circle there.
           */
          $circle->setStatus(Integers::$ACTIVE);
          /*
           * Lets then check to see if there is a cover image that has been added and handle it as well
           */
          if ($circle->getCover()) {
            $finfo = new finfo(FILEINFO_MIME);
            $mime = explode(';', $finfo->buffer(base64_decode($circle->getCover())))[0];
            
            /*
             * Make sure that the image has a jpeg or png type
             */
            if (!$this->validate_cover($mime)) {
              $circle->setCover(null);
            }
          }
          $circle->setCreator($user);
          $user->addCircle($circle);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$CIRCLE_CREATED[0], $this->clean_circle($circle->toJSON()), true, 200);
        }
        
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (\Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function update(Request $request, Response $response)
    {
      /*
       * This function will be used to update a circle that belongs to a given user, the user will be checked for
       * ownership at this level of the API.
       */
      /** @var object $circle */
      $c = $request->getAttribute('circle');
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      try {
        if ($c) {
          /*
           * Then here we begin checking the users ownership on this circle
           */
          /** @var Circle $circle */
          $circle = $user->getCircles()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $c->id)))->first();
          if ($circle) {
            if ($circle->getStatus() === Integers::$INACTIVE)
              throw new Exception(Strings::$CIRCLE_NOT_EXIST[0]);
            /*
             * Then finally we can update the circle
             */
            $circle = $this->clean_circle($this->update_circle($circle, $c));
            $this->manager->flush();
            
            return $response->withResponse(Strings::$CIRCLE_UPDATED[0], $circle->toJSON(), true, 200);
          }
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
        
      } catch (\Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function remove(Request $request, Response $response)
    {
      /*
       * In this function we will be allowing the user to remove their friendship circle by
       * simply deeming it inactive. Of course from the database side the circle will still
       * exist, the only difference is that the data will not be accessible anymore
       */
      
      /** @var object $c */
      $circle = $request->getAttribute('circle');
      
      try {
        /*
         * Then here we begin checking the users ownership on this circle
         */
        if ($circle) {
          if ($circle->getStatus() === Integers::$INACTIVE)
            throw new Exception(Strings::$CIRCLE_NOT_EXIST[0]);
          /*
           * Then finally we can update the circle
           */
          $circle->setStatus(Integers::$INACTIVE);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$CIRCLE_REMOVED[0], $circle->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
        
      } catch (\Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function getcircles(Request $request, Response $response)
    {
      /*
       * This function will simply return the circles belonging to a user
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      try {
        if (!$user)
          throw new Exception(Strings::$SOMETHING_WRONG[0]);
        /**
         * @var ArrayCollection<Circle> $circles
         */
        $circles = $user->getCircles()->map(function (Circle $circle) {
          return $this->clean_circle($circle->toJSON());
        })->toArray();
        
        return $response->withResponse(Strings::$CIRCLES_GOT[0], $circles, true, 200);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    private function update_circle(Circle $circle, object $c)
    {
      if (isset($c->name))
        $circle->setName($c->name);
      if (isset($c->status))
        $circle->setStatus($c->status);
      if (isset($c->cover)) {
        $finfo = new finfo(FILEINFO_MIME);
        $mime = explode(';', $finfo->buffer(base64_decode($c->cover)))[0];
        if ($this->validate_cover($mime))
          $circle->setCover($c->cover);
      }
      
      return $circle;
    }
    
    /**
     * @param object $circle
     * @return object
     */
    private function clean_circle(object $circle)
    {
      return $circle;
    }
    
    /**
     * @param string $mime
     * @return bool
     */
    private function validate_cover(string $mime)
    {
      $types = array(
        'image/jpx',
        'image/jpm',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png'
      );
      
      return array_search($mime, $types) > 0;
    }
    /*
    private function mime2ext($mime) {
      $mime_map = [
        'video/3gpp2'                                                               => '3g2',
        'video/3gp'                                                                 => '3gp',
        'video/3gpp'                                                                => '3gp',
        'application/x-compressed'                                                  => '7zip',
        'audio/x-acc'                                                               => 'aac',
        'audio/ac3'                                                                 => 'ac3',
        'application/postscript'                                                    => 'ai',
        'audio/x-aiff'                                                              => 'aif',
        'audio/aiff'                                                                => 'aif',
        'audio/x-au'                                                                => 'au',
        'video/x-msvideo'                                                           => 'avi',
        'video/msvideo'                                                             => 'avi',
        'video/avi'                                                                 => 'avi',
        'application/x-troff-msvideo'                                               => 'avi',
        'application/macbinary'                                                     => 'bin',
        'application/mac-binary'                                                    => 'bin',
        'application/x-binary'                                                      => 'bin',
        'application/x-macbinary'                                                   => 'bin',
        'image/bmp'                                                                 => 'bmp',
        'image/x-bmp'                                                               => 'bmp',
        'image/x-bitmap'                                                            => 'bmp',
        'image/x-xbitmap'                                                           => 'bmp',
        'image/x-win-bitmap'                                                        => 'bmp',
        'image/x-windows-bmp'                                                       => 'bmp',
        'image/ms-bmp'                                                              => 'bmp',
        'image/x-ms-bmp'                                                            => 'bmp',
        'application/bmp'                                                           => 'bmp',
        'application/x-bmp'                                                         => 'bmp',
        'application/x-win-bitmap'                                                  => 'bmp',
        'application/cdr'                                                           => 'cdr',
        'application/coreldraw'                                                     => 'cdr',
        'application/x-cdr'                                                         => 'cdr',
        'application/x-coreldraw'                                                   => 'cdr',
        'image/cdr'                                                                 => 'cdr',
        'image/x-cdr'                                                               => 'cdr',
        'zz-application/zz-winassoc-cdr'                                            => 'cdr',
        'application/mac-compactpro'                                                => 'cpt',
        'application/pkix-crl'                                                      => 'crl',
        'application/pkcs-crl'                                                      => 'crl',
        'application/x-x509-ca-cert'                                                => 'crt',
        'application/pkix-cert'                                                     => 'crt',
        'text/css'                                                                  => 'css',
        'text/x-comma-separated-values'                                             => 'csv',
        'text/comma-separated-values'                                               => 'csv',
        'application/vnd.msexcel'                                                   => 'csv',
        'application/x-director'                                                    => 'dcr',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/x-dvi'                                                         => 'dvi',
        'message/rfc822'                                                            => 'eml',
        'application/x-msdownload'                                                  => 'exe',
        'video/x-f4v'                                                               => 'f4v',
        'audio/x-flac'                                                              => 'flac',
        'video/x-flv'                                                               => 'flv',
        'image/gif'                                                                 => 'gif',
        'application/gpg-keys'                                                      => 'gpg',
        'application/x-gtar'                                                        => 'gtar',
        'application/x-gzip'                                                        => 'gzip',
        'application/mac-binhex40'                                                  => 'hqx',
        'application/mac-binhex'                                                    => 'hqx',
        'application/x-binhex40'                                                    => 'hqx',
        'application/x-mac-binhex40'                                                => 'hqx',
        'text/html'                                                                 => 'html',
        'image/x-icon'                                                              => 'ico',
        'image/x-ico'                                                               => 'ico',
        'image/vnd.microsoft.icon'                                                  => 'ico',
        'text/calendar'                                                             => 'ics',
        'application/java-archive'                                                  => 'jar',
        'application/x-java-application'                                            => 'jar',
        'application/x-jar'                                                         => 'jar',
        'image/jp2'                                                                 => 'jp2',
        'video/mj2'                                                                 => 'jp2',
        'image/jpx'                                                                 => 'jp2',
        'image/jpm'                                                                 => 'jp2',
        'image/jpeg'                                                                => 'jpeg',
        'image/pjpeg'                                                               => 'jpeg',
        'application/x-javascript'                                                  => 'js',
        'application/json'                                                          => 'json',
        'text/json'                                                                 => 'json',
        'application/vnd.google-earth.kml+xml'                                      => 'kml',
        'application/vnd.google-earth.kmz'                                          => 'kmz',
        'text/x-log'                                                                => 'log',
        'audio/x-m4a'                                                               => 'm4a',
        'audio/mp4'                                                                 => 'm4a',
        'application/vnd.mpegurl'                                                   => 'm4u',
        'audio/midi'                                                                => 'mid',
        'application/vnd.mif'                                                       => 'mif',
        'video/quicktime'                                                           => 'mov',
        'video/x-sgi-movie'                                                         => 'movie',
        'audio/mpeg'                                                                => 'mp3',
        'audio/mpg'                                                                 => 'mp3',
        'audio/mpeg3'                                                               => 'mp3',
        'audio/mp3'                                                                 => 'mp3',
        'video/mp4'                                                                 => 'mp4',
        'video/mpeg'                                                                => 'mpeg',
        'application/oda'                                                           => 'oda',
        'audio/ogg'                                                                 => 'ogg',
        'video/ogg'                                                                 => 'ogg',
        'application/ogg'                                                           => 'ogg',
        'application/x-pkcs10'                                                      => 'p10',
        'application/pkcs10'                                                        => 'p10',
        'application/x-pkcs12'                                                      => 'p12',
        'application/x-pkcs7-signature'                                             => 'p7a',
        'application/pkcs7-mime'                                                    => 'p7c',
        'application/x-pkcs7-mime'                                                  => 'p7c',
        'application/x-pkcs7-certreqresp'                                           => 'p7r',
        'application/pkcs7-signature'                                               => 'p7s',
        'application/pdf'                                                           => 'pdf',
        'application/octet-stream'                                                  => 'pdf',
        'application/x-x509-user-cert'                                              => 'pem',
        'application/x-pem-file'                                                    => 'pem',
        'application/pgp'                                                           => 'pgp',
        'application/x-httpd-php'                                                   => 'php',
        'application/php'                                                           => 'php',
        'application/x-php'                                                         => 'php',
        'text/php'                                                                  => 'php',
        'text/x-php'                                                                => 'php',
        'application/x-httpd-php-source'                                            => 'php',
        'image/png'                                                                 => 'png',
        'image/x-png'                                                               => 'png',
        'application/powerpoint'                                                    => 'ppt',
        'application/vnd.ms-powerpoint'                                             => 'ppt',
        'application/vnd.ms-office'                                                 => 'ppt',
        'application/msword'                                                        => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/x-photoshop'                                                   => 'psd',
        'image/vnd.adobe.photoshop'                                                 => 'psd',
        'audio/x-realaudio'                                                         => 'ra',
        'audio/x-pn-realaudio'                                                      => 'ram',
        'application/x-rar'                                                         => 'rar',
        'application/rar'                                                           => 'rar',
        'application/x-rar-compressed'                                              => 'rar',
        'audio/x-pn-realaudio-plugin'                                               => 'rpm',
        'application/x-pkcs7'                                                       => 'rsa',
        'text/rtf'                                                                  => 'rtf',
        'text/richtext'                                                             => 'rtx',
        'video/vnd.rn-realvideo'                                                    => 'rv',
        'application/x-stuffit'                                                     => 'sit',
        'application/smil'                                                          => 'smil',
        'text/srt'                                                                  => 'srt',
        'image/svg+xml'                                                             => 'svg',
        'application/x-shockwave-flash'                                             => 'swf',
        'application/x-tar'                                                         => 'tar',
        'application/x-gzip-compressed'                                             => 'tgz',
        'image/tiff'                                                                => 'tiff',
        'text/plain'                                                                => 'txt',
        'text/x-vcard'                                                              => 'vcf',
        'application/videolan'                                                      => 'vlc',
        'text/vtt'                                                                  => 'vtt',
        'audio/x-wav'                                                               => 'wav',
        'audio/wave'                                                                => 'wav',
        'audio/wav'                                                                 => 'wav',
        'application/wbxml'                                                         => 'wbxml',
        'video/webm'                                                                => 'webm',
        'audio/x-ms-wma'                                                            => 'wma',
        'application/wmlc'                                                          => 'wmlc',
        'video/x-ms-wmv'                                                            => 'wmv',
        'video/x-ms-asf'                                                            => 'wmv',
        'application/xhtml+xml'                                                     => 'xhtml',
        'application/excel'                                                         => 'xl',
        'application/msexcel'                                                       => 'xls',
        'application/x-msexcel'                                                     => 'xls',
        'application/x-ms-excel'                                                    => 'xls',
        'application/x-excel'                                                       => 'xls',
        'application/x-dos_ms_excel'                                                => 'xls',
        'application/xls'                                                           => 'xls',
        'application/x-xls'                                                         => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'application/vnd.ms-excel'                                                  => 'xlsx',
        'application/xml'                                                           => 'xml',
        'text/xml'                                                                  => 'xml',
        'text/xsl'                                                                  => 'xsl',
        'application/xspf+xml'                                                      => 'xspf',
        'application/x-compress'                                                    => 'z',
        'application/x-zip'                                                         => 'zip',
        'application/zip'                                                           => 'zip',
        'application/x-zip-compressed'                                              => 'zip',
        'application/s-compressed'                                                  => 'zip',
        'multipart/x-zip'                                                           => 'zip',
        'text/x-scriptzsh'                                                          => 'zsh',
      ];
    
      return isset($mime_map[$mime]) ? $mime_map[$mime] : false;
    }
    */
  }
