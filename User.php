<?php

namespace Stanford\EventBookmark;

include_once 'DB.php';

class User {

  /** @var User $instance - singleton instance of the class **/
  private static $instance;

  /** @var DB $db - connection to database */
  protected $db;


  /***
   * Determine if a user is already registered in the bookmarking db
   *
   * @param int | string $uid - Localist user id (int) or slug (string)
   * @return bool
   */
  public function user_exists( $uid ) {
    $user = $this->get_user( $uid );
    return is_object( $user );
  }

  /***
   * Get a user from the DB
   *
   * @param int | string $uid - Localist user id (int) or slug (string)
   * @return stdClass - feed
   */
  public function get_user( $uid ) {
    $query  = "SELECT * FROM localist_bkmk_user WHERE ";
    $query .= is_numeric( $uid )
        ? "id = '{$uid}';"
        : "slug = '{$uid}';";

    $result = $this->db->query( $query, MYSQLI_USE_RESULT );
    $user = $result->fetch_object();
    $result->close();
    return $user;
  }


  /***********************/
  /***** Class Setup *****/
  /***********************/

  /**
   * Return singleton instance of the class
   *
   * @param DB $db - connection to database
   * @return User
   */
  static public function init( DB $db ) {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new User( $db );
    }
    return self::$instance;
  }

  /**
   * Build the singleton instance of the class.
   *
   * @param DB $db - connection to database
   */
  private function __construct( $db = NULL ) {
    if ( is_a( $db, 'DB' ) ) {
      $this->db = $db;
    }
    else {
      $this->db = DB::get_instance();
    }
  }
}