#!/usr/bin/php
<?php

namespace Stanford\EventBookmark;

include_once "LocalistAPI.php";

$opts     = getopt( 'e:r:i:h', [] );
$env      = isset( $opts[ 'e' ] ) ? $opts[ 'e' ] : 'live';
$resource = isset( $opts[ 'r' ] ) ? $opts[ 'r' ] : 'user';
$ids      = isset( $opts[ 'i' ] ) ? $opts[ 'i' ] : '';

if ( isset( $opts[ 'h' ] ) ) {
  echo "Usage:\n";
  echo "  -e environment - 'live' or 'staging'. Defaults to live.\n";
  echo "  -r resource - what resource to retrieve from Localist, e.g. user, event, department, place. Defaults to user.\n";
  echo "  -i ids - comma separated list of specific ids to retrieve. If not specified, gets the first 10.\n";
  die();
}

$localist = LocalistAPI::init( $env );

switch ( $resource ) {
  case 'user':
    $method = 'get_user';
    break;
  case 'event':
    $method = 'get_event';
    break;
}

if ( empty( $ids ) ) {
  echo "Fetching 10 {$resource}s\n";
  $results = $localist->auth_api_call( $resource . 's' );
  print_r( $results );
} else {
  foreach ( explode( ',', $ids ) as $id ) {
    echo "Fetching {$resource} {$id}\n";
    if ( isset( $method ) ) {
      $results = call_user_func( [$localist, $method ], $id );
    } else {
      $results = $localist->auth_api_call( "{$resource}s/{$id}" );
    }
    print_r( $results );
    echo "\n";
  }
}
