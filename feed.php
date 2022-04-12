<?php
namespace Stanford\EventBookmark;

header('Content-Type: application/json; charset=utf-8');

if ( empty( $_REQUEST[ 'slug' ] ) ) {
  echo '{error:"No slug specified"}';
  die();
}
$slug = $_REQUEST[ 'slug' ];

include_once 'DB.php';
include_once 'FeedClass.php';
include_once 'Localist.php';

$db        = DB::get_instance();
$localist  = Localist::init( 'staging' ); //// TODO: change to 'live'
$feeder    = Feed::init( $db );
$feed      = $feeder->get_feed( $_REQUEST[ 'slug' ] );
$event_ids =  $feeder->get_feed_events( $feed->id );

$data = new \stdClass;
$data->events = [];
foreach ( $event_ids as $event_id ) {
  $event = $localist->get_event( $event_id );  
  if ( $event->recurring == "true" ) {
    $baseEvent = clone $event;
    unset( $baseEvent->event_instances );
  }
  else {
    $eventObj = new \stdClass;
    $eventObj->event = $event;
    $data->events[] = $eventObj;
  }
}

echo json_encode( $data );
?>
