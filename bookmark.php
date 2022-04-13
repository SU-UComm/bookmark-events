<?php
namespace Stanford\EventBookmark;

include_once 'DB.php';
include_once 'Feeder.php';
include_once 'Localist.php';

ob_start( NULL, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE );
if ( !empty( $_POST ) ) {
  $db        = DB::get_instance();
  $localist  = Localist::init( 'staging' ); //// TODO: change to 'live'
  $event     = $localist->get_event( $_POST[ 'eventId' ] );
  $feeder    = Feeder::init( $db );
  $feed      = $feeder->get_feed( $_POST[ 'feedId' ] );
  $added     = $feeder->add_event_to_feed( $_POST[ 'eventId' ], $_POST[ 'feedId' ] );
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

  <?php include '../v5/includes/head.html'; ?>
</head>

<body class="content-page">
<?php include '../v5/includes/header.html'; ?>

<!-- main content -->
<section id="main-content"  role="main">
  <div class="container">
    <h1>Event bookmarked</h1>
    <p>
      <a href="<?php echo $event->localist_url; ?>"><?php echo $event->title; ?></a>
      <?php if ( $added ) { ?>
      has been added to <?php echo $feed->name; ?>.
      <?php } else { ?>
      is already in <?php echo $feed->name; ?>.
      <?php } ?>
    </p>
    <p>
      <a href="/bookmark/feed.php?slug=<?php echo $feed->slug; ?>">View the feed.</a>
    </p>
    <section id="debug">
      <code>
        <pre>
	  <?php echo $debug; ?>
	</pre>
      </code>
    </section>
  </div>
</section>

<!-- BEGIN footer -->
<?php include '../v5/includes/footer.html'; ?>
<!-- END footer -->
</body>
</html>
