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
$event_ids = $feeder->get_feed_events( $feed->id );

$data = new \stdClass;
$data->events = [];
foreach ( $event_ids as $event_id ) {
  $eventObj = new \stdClass;
  $event    = $localist->get_event( $event_id );
  if ( strtolower( $event->recurring != 'true' ) ) { // non-recurring event
    $eventObj->event = $event;
    $data->events[]  = $eventObj;
  }
  else { // recurring event
    echo json_encode( $event );
    $instances = $event->event_instances;
    echo json_encode( $instances );
    foreach ( $instances as $instance ) {
      echo json_encode( $instance );
      $event->event_instances = [ $instance ];
      $eventObj->event = $event;
      $data->events[]  = $eventObj;
    }
  }
}

echo json_encode( $data );
?>
