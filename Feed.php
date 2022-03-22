<?php

namespace Stanford\EventBookmark;

include_once 'DB.php';

class Feed {

  /** @var Feed $instance - singleton instance of the class **/
  private static $instance;

  /** @var DB $db - connection to database */
  protected $db;


  /***
   * Get feeds a user is allowed to add events to
   *
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
/*** ***
    echo "\nget_feeds( {$uid} ):\n"; //// DEBUG
    echo "Result (" . get_class( $result ) . "):\n"; //// DEBUG
    print_r( $result ); //// DEBUG
/*** ***/
    $feeds = [];
    while ( $feed = $result->fetch_object() ) {
      $feeds[] = $feed;
    }
/*** ***
    echo "Feeds for {$uid}:\n"; //// DEBUG
    print_r( $feeds ); //// DEBUG
/*** ***/
    return $feeds;
  }

  /***
   * Add an event to a feed.
   *
   * @param int $feedId - feed id
   * @return stdClass - name of the feed
   */
  public function add_event_to_feed( $eventId, $feedId ) {
    // first make sure the event isn't already in the feed
    $query1 = <<<EOQUERY1
SELECT *
  FROM localist_bkmk_feed_events
  WHERE feed_id={$feedId}
    AND event_id={$eventId};
EOQUERY1;
    $result1 = $this->db->query( $query1, MYSQLI_USE_RESULT );
/*** ***/
    echo "\nadd_event_to_feed( {$eventId}, {$feedId} ):\n"; //// DEBUG
    echo "Result (" . get_class( $result1 ) . "):\n"; //// DEBUG
    print_r( $result1 ); //// DEBUG
    echo "<hr/>";
/*** ***/
    $row = $result1->fetch_object();
/*** ***/
    echo "Row (" . get_class( $row ) . "):\n"; //// DEBUG
    print_r( $row ); //// DEBUG
    echo "<hr/>";
/*** ***/
    if ( $row ) {
      return FALSE;
    }

    $query2 = <<<EOQUERY2
INSERT INTO localist_bkmk_feed_events
  ( feed_id, event_id )
  VALUES ( {$feedId}, {$eventId} );
EOQUERY2;
    $result2 = $this->db->query( $query2, MYSQLI_USE_RESULT );
/*** ***/
    echo "\nadd_event_to_feed( {$eventId}, {$feedId} ):\n"; //// DEBUG
    echo "Result (" . get_class( $result2 ) . "):\n"; //// DEBUG
    print_r( $result2 ); //// DEBUG
/*** ***/
    return TRUE;
  }

  /***
   * Get details about a specific feed
   *
   * @param int $feedId - feed id
   * @return stdClass - feed
   */
  public function get_feed( $feedId ) {

    $query = <<<EOQUERY
SELECT f.*
  FROM localist_bkmk_feed AS f
  WHERE f.id = {$feedId};
EOQUERY;

    $result = $this->db->query( $query, MYSQLI_USE_RESULT );
/*** ***
    echo "\nget_feed( {$feedId} ):\n"; //// DEBUG
    echo "Result (" . get_class( $result ) . "):\n"; //// DEBUG
    print_r( $result ); //// DEBUG
    echo "<hr/>\n";
/*** ***/

    $feed = $result->fetch_object();
/*** ***
    echo "Feed {$feedId}:\n"; //// DEBUG
    print_r( $feed ); //// DEBUG
    echo "<hr/>\n";
/*** ***/
    return $feed;
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