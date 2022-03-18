#!/usr/bin/php
<?php

namespace Stanford\EventBookmark;

include_once "DB.php";
include_once "Feed.php";

if ( $argc < 2 ) display_help();

global $db;
$db = DB::get_instance();
$feed = Feed::init( $db );

for ( $i=1; $i<$argc; $i++ ) {
  $uid = $argv[ $i ];
  if ( is_numeric( $uid ) ) {
    echo "----\nFetching feeds for uid {$uid}\n";
    $feeds = $feed->get_user_feeds( $uid );
    print_r( $feeds );
  }
  else {
    echo "****\n{$argv[ $i ]} is not a valid user id\n";
  }
}

function display_help() {
  echo "Usage: ./tests/", basename(__FILE__ ), " user_id [user_ids]\n";
  die;
}