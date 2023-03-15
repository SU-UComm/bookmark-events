<?php
namespace Stanford\EventBookmark;


header('Content-Type: application/json; charset=utf-8');

// handle query params:
// slug:   which feed to pull - required
// all:    include all events, including past events - optional
// repeat: for repeating events, how many days worth of instances - optional, defaults to 1
if ( !array_key_exists( "slug", $_REQUEST ) || empty( $_REQUEST[ 'slug' ] ) ) {
  echo '{error:"No slug specified"}'; // we emitted the json header above, so emit error msgs in json fmt
  die();
}
$slug = $_REQUEST[ 'slug' ];

$all  = array_key_exists( "all", $_REQUEST ) ? TRUE : FALSE;

$repeat_days = 365 * 2; // default to emitting 2 years of repeating events
if ( array_key_exists( 'repeat', $_REQUEST ) ) {
  $repeat_days = empty( $_REQUEST[ 'repeat' ] ) ? 1 : intval( $_REQUEST[ 'repeat' ] );
}

// now down to business
include_once 'DB.php';
include_once 'FeedAPI.php';
include_once 'LocalistAPI.php';

$db        = DB::get_instance();
$localist  = LocalistAPI::init( 'live' );
$feedAPI   = FeedAPI::init( $db );
$feed      = $feedAPI->get_feed( $_REQUEST[ 'slug' ] );
$event_ids = $feedAPI->get_feed_events( $feed->id, $all );

$today     = date( 'Y-m-d' ); // get today's date as yyyy-mm-dd

$data = new \stdClass;
$data->events = [];
foreach ( $event_ids as $event_id ) {
  $eventObj = new \stdClass;
  $event    = $localist->get_event( $event_id );
  if ( count( $event->event_instances ) == 1 ) { // non-recurring event
    $eventObj->event = $event;
    $data->events[]  = clone $eventObj;
  }
  else { // recurring event
    $days = []; // what days' instances have we emitted
    $instances = $event->event_instances;
    foreach ( $instances as $instance ) {
      $event_date = substr( $instance->event_instance->start, 0, 10 ); // first 10 chars are yyyy-mm-dd
      if ( !$all && strcmp( $event_date, $today ) <= 0 ) {
        // skip instances that happened before today
        continue;
      }
      $event->event_instances = [ $instance ];
      $eventObj->event = clone $event;
      $data->events[]  = clone $eventObj;
      if ( !array_key_exists( $event_date, $days ) ) {
        $days[ $event_date ] = TRUE;
      }
      if ( sizeof( $days ) >= $repeat_days ) {
        // we've reached our limit of days, move on to the next event
        break;
      }
    }
  }
}

echo json_encode( $data );
?>
