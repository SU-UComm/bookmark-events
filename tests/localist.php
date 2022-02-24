#!/usr/bin/php
<?php

namespace Stanford\EventBookmark;

include_once "Localist.php";

$opts     = getopt( 'r:i:h', [] );
$resource = isset( $opts[ 'r' ] ) ? $opts[ 'r' ] : 'user';
$ids      = isset( $opts[ 'i' ] ) ? $opts[ 'i' ] : '';

if ( isset( $opts[ 'h' ] ) ) {
  echo "Usage:\n";
  echo "  -r resource - what resource to retrieve from Localist, e.g. user, department, place. Defaults to user.\n";
  echo "  -i ids - comma separated list of specific ids to retrieve. If not specified, gets the first 10.\n";
}

$localist = Localist::init();

if ( empty( $ids ) ) {
  echo "Fetching 10 {$resource}s\n";
  $results = $localist->auth_api_call( $resource . 's' );
  print_r( $results );
} else {
  foreach ( explode( ',', $ids ) as $id ) {
    echo "Fetching {$resource} {$id}\n";
    $results = $localist->auth_api_call( "{$resource}s/{$id}" );
    print_r( $results );
    echo "\n";
  }
}
