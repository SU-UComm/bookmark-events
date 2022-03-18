<?php
namespace Stanford\EventBookmark;

include_once 'DB.php';
include_once 'Feed.php';
include_once 'Localist.php';

if ( !empty( $_POST ) ) {
  $db       = DB::get_instance();
  $feed     = Feed::init( $db );
  $feeds    = $feed->get_user_feeds( $_POST[ 'userId' ] );
  $localist = Localist::init( 'staging' ); //// TODO: change to 'live'
  $event    = $localist->get_event( $_POST[ 'eventId' ] );
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
</head>

<body class="content-page">
<?php include '../v5/includes/header.html'; ?>

<!-- main content -->
<section id="main-content"  role="main">
  <div class="container">
    <h1>Bookmark a Localist event</h1>

<?php if ( !empty( $_POST ) ) { ?>
    <h2>$_POST</h2>
    <code>
      <pre>
        <?php print_r( $_POST ); ?>
      </pre>
    </code>

    <h2>$feeds</h2>
    <code>
      <pre>
        <?php print_r( $feeds ); ?>
      </pre>
    </code>

    <h2>$event</h2>
    <code>
      <pre>
        <?php print_r( htmlentities( $event ) ); ?>
      </pre>
    </code>

    <h2>Select feed</h2>
    <p>
      Which feed would you like to include
      <a href="<?php echo $event->localist_url; ?>"><?php echo $event->title; ?></a>
      in?
    </p>
    <form id="bookmark-form" method="post">
      <input type="submit" value="Submit">
    </form>
<?php } else { ?>
    <form id="test-form" method="post">
      <label for="userId">User id:</label>
      <input id="userId" type="number" width="15" /><br/>
      <label for="eventId">Event id:</label>
      <input id="eventId" type="number" width="15" /><br/>
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
