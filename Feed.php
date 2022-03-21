<?php

namespace Stanford\EventBookmark;

include_once 'DB.php';

class Feed {

  /** @var Feed $instance - singleton instance of the class **/
  private static $instance;

  /** @var DB $db - connection to database */
  protected $db;


  /***
   * @param int $uid - Localist user id
   * @return array feeds that the Localist user is allowed to add events to
   */
  public function get_user_feeds( $uid ) {
    $query = <<<EOQUERY
SELECT uf.*, f.slug, f.name
  FROM localist_bkmk_user_feed AS uf
  LEFT JOIN localist_bkmk_feed as f ON uf.feed_id=f.id
  WHERE uf.user_id = {$uid};
EOQUERY;

    $result = $this->db->query( $query, MYSQLI_USE_RESULT );
//    echo "\nget_feeds( {$uid} ):\n"; //// DEBUG
//    echo "Result (" . get_class( $result ) . "):\n"; //// DEBUG
//    print_r( $result ); //// DEBUG

    $feeds = [];
    while ( $feed = $result->fetch_object() ) {
      $feeds[] = $feed;
    }
//    echo "Feeds for {$uid}:\n"; //// DEBUG
//    print_r( $feeds ); //// DEBUG
    return $feeds;
  }

  /***********************/
  /***** Class Setup *****/
  /***********************/

  /**
   * Return singleton instance of the class
   *
   * @return Feed
   */
  static public function init( DB $db) {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new Feed( $db );
    }
    return self::$instance;
  }

  /**
   * Build the singleton instance of the class.
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