#!/usr/bin/env php
<?php

namespace Stanford\EventBookmark;

include_once "DB.php";
include_once "LocalistAPI.php";

$db = DB::get_instance();
$localist = LocalistAPI::init();

$event_ids = [];
$fetch_query = <<<EO_FETCH_QUERY
SELECT DISTINCT event_id
  FROM  localist_bkmk_feed_events
  WHERE last_date IS NULL
  ORDER BY event_id
  LIMIT 20
;
EO_FETCH_QUERY;
$results = $db->query( $fetch_query );
while ( $row = $results->fetch_object() ) {
  $event_ids[] = $row->event_id;
}
// print_r( $event_ids );
echo "Retrieved " . count( $event_ids ) . " events.\n\n";
// exit;

$today = date( 'Y-m-d' );

$update_query = <<<EO_UPDATE_QUERY
UPDATE localist_bkmk_feed_events
  SET
    instances = %2\$u,
    last_date = '%3\$s'
  WHERE event_id = %1\$u
;
EO_UPDATE_QUERY;

$delete_query = <<<EO_DELETE_QUERY
DELETE
  FROM localist_bkmk_feed_events
  WHERE event_id = %1\$u
;
EO_DELETE_QUERY;

foreach ( $event_ids as $id ) {
  $event = $localist->get_event( $id );
  echo "Event {$id}: ";
  if ( !$event ) {
    echo "EMPTY\n";
    continue;
  }
  $instances = count( $event->event_instances );
  $last_date = $event->last_date;
  echo "Last date is {$last_date}; Num instances = {$instances}\n";
  if ( $last_date >= $today ) {
    $query = sprintf( $update_query, $id, $instances, $last_date );
  }
  else {
    $query = sprintf( $delete_query, $id );
  }
  echo "{$query}\n";
  $result = $db->query( $query );
  echo "Query info: " . $db->query_info();
  echo "\n----\n";
}
