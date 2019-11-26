<?php
  
  
  namespace br\Constants;
  
  
  class Integers
  {
    /*
     * Member states
     */
    /** @var int */
    static $MEMBER_ACTIVE = 8;
    /** @var int */
    static $MEMBER_REMOVED = 9;
  
    /*
     * Circle states
     */
    /** @var int */
    static $ACTIVE = 6;
    /** @var int */
    static $INACTIVE = 7;
  
    /*
     * Message state
     */
    /** @var int */
    static $SENT = 5;
    /*
     * Token expiration time
     */
    
    /** @var int */
    static $TOKEN_EXPIRATION = 3000;
  
    /*
     * Friendship states
     */
    /** @var int */
    static $PENDING = 0;
    /** @var int */
    static $ACCEPTED = 1;
    /** @var int */
    static $DECLINED = 2;
    /** @var int */
    static $BLOCKED = 3;
    /** @var int */
    static $CANCELLED = 4;
    /** @var int */
    static $REMOVED = 5;
  }
