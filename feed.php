<?php
namespace Stanford\EventBookmark;

header('Content-Type: application/json; charset=utf-8');

if ( empty( $_REQUEST[ 'slug' ] ) ) {
  echo '{error:"No slug specified"}';
  die();
}
$slug = $_REQUEST[ 'slug' ];
$all  = array_key_exists( "all", $_REQUEST ) ? TRUE : FALSE;

include_once 'DB.php';
include_once 'FeedAPI.php';
include_once 'LocalistAPI.php';

$db        = DB::get_instance();
$localist  = LocalistAPI::init( 'live' );
$feedAPI   = FeedAPI::init( $db );
$feed      = $feedAPI->get_feed( $_REQUEST[ 'slug' ] );
$event_ids = $feedAPI->get_feed_events( $feed->id, $all );

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
    $instances = $event->event_instances;
    foreach ( $instances as $instance ) {
      $event->event_instances = [ $instance ];
      $eventObj->event = clone $event;
      $data->events[]  = clone $eventObj;
    }
  }
}

echo json_encode( $data );
?>
