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
    $feeds = [];
    while ( $feed = $result->fetch_object() ) {
      $feeds[] = $feed;
    }
    $result->close();
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
    $row = $result1->fetch_object();
    $result1->close();
    if ( $row ) {
      return FALSE;
    }

    $query2 = <<<EOQUERY2
INSERT INTO localist_bkmk_feed_events
  ( feed_id, event_id )
  VALUES ( {$feedId}, {$eventId} );
EOQUERY2;
    $result2 = $this->db->query( $query2, MYSQLI_USE_RESULT );
    return TRUE;
  }

  /***
   * Get details about a specific feed
   *
   * @param int | string $feed - feed id (int) or slug (string)
   * @return stdClass - feed
   */
  public function get_feed( $feed ) {

    $query  = "SELECT * FROM localist_bkmk_feed WHERE ";
    $query .= is_numeric( $feed )
      ? "id = '{$feed}';"
      : "slug = '{$feed}';";

    $result = $this->db->query( $query, MYSQLI_USE_RESULT );
    $feed = $result->fetch_object();
    $result->close();
    return $feed;
  }

  /***
   * Get events in a specific feed
   *
   * @param int $feedId - feed id
   * @return stdClass - feed
   */
  public function get_feed_events( $feedId ) {

    $query = <<<EOQUERY
SELECT fe.*
  FROM localist_bkmk_feed_events AS fe
  WHERE fe.feed_id = '{$feedId}';
EOQUERY;

    $result = $this->db->query( $query, MYSQLI_USE_RESULT );

    $events = [];
    while ( $event = $result->fetch_object() ) {
      $events[] = $event->event_id;
    }
    return $events;
  }

  /***********************/
  /***** Class Setup *****/
  /***********************/

  /**
   * Return singleton instance of the class
   *
   * @param DB $db - connection to database
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