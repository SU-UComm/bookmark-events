#!/usr/bin/env php
<?php

namespace Stanford\EventBookmark;

include_once "DB.php";
include_once "FeedAPI.php";

$opts = getopt( 'f:u:h', [] );
$fids = isset( $opts[ 'f' ] ) ? $opts[ 'f' ] : '';
$uids = isset( $opts[ 'u' ] ) ? $opts[ 'u' ] : '';

if ( isset( $opts[ 'h' ] ) ) {
  display_help();
}
echo "You asked for feed(s) {$fids}\n";
echo "You asked for user(s) {$uids}\n";

$db = DB::get_instance();
$feedAPI = FeedAPI::init( $db );

if ( !empty( $fids ) ) {
  foreach ( explode( ',', $fids ) as $fid ) {
    $feed = $feedAPI->get_feed( $fid );
    echo "Feed {$fid}:\n";
    print_r( $feed );
    echo "\n";
  }
}

if ( !empty( $uids ) ) {
  foreach ( explode( ',', $uids ) as $uid ) {
    $feeds = $feedAPI->get_user_feeds( $uid );
    echo "Feeds for user {$uid}:\n";
    print_r( $feeds );
    echo "\n";
  }
}

function display_help() {
  echo "Usage:\n";
  echo "  -f ids - comma separated list of feed ids to retrieve\n";
  echo "  -u ids - comma separated list of user ids to retrieve\n";
  die();
}