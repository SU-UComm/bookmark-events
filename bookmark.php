<?php
namespace Stanford\EventBookmark;

include_once 'DB.php';
include_once 'FeedAPI.php';
include_once 'LocalistAPI.php';

$root = dirname( $_SERVER[ 'PHP_SELF' ] );

ob_start( NULL, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE );
if ( !empty( $_POST ) ) {
  $db        = DB::get_instance();
  $localist  = LocalistAPI::init( 'live' );
  $event     = $localist->get_event( $_POST[ 'eventId' ] );
  $feedAPI   = FeedAPI::init( $db );
  $feed      = $feedAPI->get_feed( $_POST[ 'feedId' ] );
  $added     = $feedAPI->add_event_to_feed( $_POST[ 'eventId' ], $_POST[ 'feedId' ] );
}
$debug = ob_get_contents();
ob_end_clean();
?>
<!DOCTYPE html>
<!--[if IE 7]> <html lang="en" class="ie7"> <![endif]-->
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->

<head>
  <title>Bookmarked | Stanford Event Calendar</title>

  <!-- Meta -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="author" content="Stanford Event Calendar" />
  <meta name="description" content="Bookmark Localist events." />

  <?php include './includes/head.html'; ?>
</head>

<body class="su-content-page su-bookmark-page">
<?php include './includes/header.html'; ?>

<!-- main content -->
<main id="main-content"  role="main">
  <div class="container">
    <h1>Event bookmarked</h1>
    <p>
      <a href="<?php echo $event->localist_url; ?>"><?php echo $event->title; ?></a>
      <?php echo ( $added ) ? ' has been added to ' : ' is already in '; ?>
      <a href="<?php echo $root; ?>/feed.php?slug=<?php echo $feed->slug; ?>"><?php echo $feed->name; ?>.</a>
    </p>
    <?php if ( !empty( $debug ) ) { ?>
    <section id="debug">
      <code>
        <pre>
	  <?php echo $debug; ?>
	</pre>
      </code>
    </section>
    <?php } ?>
  </div>
</main>

<!-- BEGIN footer -->
<?php include './includes/footer.html'; ?>
<!-- END footer -->
</body>
</html>
