<?php
namespace Stanford\EventBookmark;

include_once 'DB.php';
include_once 'FeedAPI.php';
include_once 'LocalistAPI.php';

$root = dirname( $_SERVER[ 'PHP_SELF' ] );

if ( !empty( $_POST ) ) {
  $db        = DB::get_instance();
  $feedAPI   = FeedAPI::init( $db );
  $feeds     = $feedAPI->get_user_feeds( $_POST[ 'userId' ] );
  $num_feeds = count( $feeds );
  $localist  = LocalistAPI::init( 'staging' ); //// TODO: change to 'live'
  $user      = $localist->get_user(  $_POST[ 'userId'  ] );
  $event     = $localist->get_event( $_POST[ 'eventId' ] );
}
?>
<!DOCTYPE html>
<!--[if IE 7]> <html lang="en" class="ie7"> <![endif]-->
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->

<head>
  <title>Bookmark | Stanford Event Calendar</title>

  <!-- Meta -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="author" content="Stanford Event Calendar" />
  <meta name="description" content="Bookmark Localist events." />

  <?php include '../v5/includes/head.html'; ?>
  <style>
    input[type="radio"] {
      margin-bottom: 1em;
      margin-right:  0.5em;
    }
  </style>
</head>

<body class="content-page">
<?php include '../v5/includes/header.html'; ?>

<!-- main content -->
<section id="main-content"  role="main">
  <div class="container">
    <h1>Bookmark a Localist event</h1>

<?php if ( !empty( $_POST ) ) { ?>
    <h2>Add to feed</h2>
    <?php if ( $num_feeds == 0 ) { ?>
      <p>
        You are not yet registered to bookmark events in any feeds. Please
        <a href="http://stanford.io/contact-events-calendar">submit a ticket</a>
        to request access to bookmarking. Please indicate in your ticket what
        feed(s) you'd like to add events to.
      </p>
    <?php } else { ?>
    <p>
      Add <a href="<?php echo $event->localist_url; ?>"><?php echo $event->title; ?></a> to:
    </p>
    <form id="bookmark-form" method="post" action="<?php echo $root; ?>/bookmark.php">
        <input name="eventId" type="hidden" value="<?php echo $_POST[ 'eventId' ]; ?>">
      <?php if ( $num_feeds <= 5 ) { ?>
        <?php foreach ( $feeds as $fid => $theFeed ) { ?>
	      <input name="feedId" type="radio" value="<?php echo $fid; ?>" />&nbsp;&nbsp;<?php echo $theFeed; ?></br>
        <?php } ?>
      <?php } else { ?>
        <select name="feedId">
        <?php foreach ( $feeds as $fid => $theFeed ) { ?>
	        <option value="<?php echo $fid; ?>"/><?php echo $theFeed; ?></option>
        <?php } ?>
	      </select>
      <?php } ?>
      <input type="submit" value="Submit">
    </form>
    <?php } ?>
<?php } else { ?>
    <form id="test-form" method="post">
      <label for="userId">User id:</label>
      <input name="userId" type="text" width="15" /><br/>
      <label for="eventId">Event id:</label>
      <input name="eventId" type="text" width="15" /><br/>
      <input type="submit" value="Bookmark this event">
    </form>
<?php } ?>
  </div>
</section>

<!-- BEGIN footer -->
<?php include '../v5/includes/footer.html'; ?>
<!-- END footer -->
</body>
</html>
