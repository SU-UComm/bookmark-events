<?php

namespace Stanford\EventBookmark;

include_once 'DB.php';
include_once 'LocalistAPI.php';

class FeedAPI {

  /** @var FeedAPI $instance - singleton instance of the class **/
  private static $instance;

  /** @var DB $db - connection to database */
  protected $db;

  /** @var LocalistAPI $localist - connection to Localist */
  protected $localist;


  /***
   * Determine if user is allowed to add events to specified feed
   *
   * @param int $uid - Localist user id
   * @param int $fid - feed id
   * @return bool
   */
  public function user_feed_exists( $uid, $fid ) {
    $feeds = $this->get_user_feeds( $uid );
    return isset( $feeds[ $fid ] );
  }

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
      $feeds[ $feed->feed_id ] = $feed;
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

    // fetch the event from Localist
    $event = $this->localist->get_event( $eventId );
    if ( $event === FALSE ) {
      // if the event doesn't exist, bail
      return FALSE;
    }

    // add the event to the feed
    $instances = count( $event->event_instances );
    $last_date = $event->last_date;
    $query2 = <<<EOQUERY2
INSERT INTO localist_bkmk_feed_events
  ( feed_id, event_id, instances, last_date )
  VALUES ( {$feedId}, {$eventId}, {$instances}, '{$last_date}' );
EOQUERY2;
    $result2 = $this->db->query( $query2, MYSQLI_USE_RESULT );
    return TRUE;
  }

  /***
   * Determine if a feed with the specified slug already exists
   *
   * @param string $slug
   * @return bool
   */
  public function feed_exists( $slug ) {
    $feed = $this->get_feed( $slug );
    return is_object( $feed );
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
   * Get all feeds
   *
   * @return array
   */
  public function get_feeds() {
    $query  = "SELECT * FROM localist_bkmk_feed ORDER BY `name` ASC;";
    $result = $this->db->query( $query, MYSQLI_USE_RESULT );
    $feeds  = [];
    while ( $feed = $result->fetch_object() ) {
      $feeds[ $feed->id ] = $feed;
    }
    $result->close();
    return $feeds;
  }

  /***
   * Get events in a specific feed
   *
   * @param int  $feedId      - feed id
   * @param bool $includePast - TRUE to include past events
   * @return stdClass - feed
   */
  public function get_feed_events( $feedId, $includePast=FALSE ) {
    $feedId = intval( $feedId );
    $query = <<<EOQUERY
SELECT fe.*
  FROM localist_bkmk_feed_events AS fe
  WHERE fe.feed_id = {$feedId}
EOQUERY;
    $query .= $includePast ? ";" : "\n  AND   fe.last_date >= CURDATE();";

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
   * @return FeedAPI
   */
  static public function init( DB $db ) {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new FeedAPI( $db );
    }
    return self::$instance;
  }

  /**
   * Build the singleton instance of the class.
   *
   * @param DB $db - connection to database
   * @param LocalistAPI $localist - connection to Localist
   */
  private function __construct( $db = NULL, $localist = NULL ) {
    if ( is_a( $db, 'DB' ) ) {
      $this->db = $db;
    }
    else {
      $this->db = DB::get_instance();
    }

    if ( is_a( $localist, 'LocalistAPI' ) ) {
      $this->localist = $localist;
    }
    else {
      $this->localist = LocalistAPI::init();
    }
  }
}