#!/usr/bin/php
<?php

namespace Stanford\EventBookmark;

include_once "DB.php";
include_once "UserAPI.php";

$opts = getopt( 'i:h', [] );
$uids = isset( $opts[ 'i' ] ) ? $opts[ 'i' ] : '';

if ( isset( $opts[ 'h' ] ) ) {
  echo "Usage:\n";
  echo "  -i ids - comma separated list of specific ids to retrieve. If not specified, gets the first 10.\n";
  die();
}

$db = DB::get_instance();
$userAPI = UserAPI::init( $db );

if ( empty( $uids ) ) {
  echo "Please specify at least one Localist user id.\n";
  die();
}
foreach ( explode( ',', $uids ) as $id ) {
  $user = $userAPI->get_user( $id );
  if ( is_object( $user ) ) {
    print_r( $user );
  }
  else {
    echo '$user is type ', gettype( $user ), "\n";
  }
  echo "\n";
}
