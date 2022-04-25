<?php
namespace Stanford\EventBookmark;

include_once '../DB.php';
include_once '../FeedAPI.php';
include_once '../LocalistAPI.php';
include_once '../UserAPI.php';

function debug( array $data ) {
  foreach ( $data as $var => $value ) {
    echo "<hr/><h3>{$var}</h3>\n";
    echo "<code>\n  <pre>\n";
    echo htmlentities( print_r( $value, TRUE ) );
    echo "  </pre>\n</code>\n";
  }
}

$db       = DB::get_instance();
$feedAPI  = FeedAPI::init( $db );
$feeds    = $feedAPI->get_feeds();
$userAPI  = UserAPI::init( $db );
$msgClass = 'success';

if ( !empty( $_POST ) ) {
  switch ( $_POST[ 'submit' ] ) {
    case 'Add user':
      $uid = $_POST[ 'userId' ];
      $user = $userAPI->get_user( $uid );
      if ( is_object( $user ) ) {
        $msg = "{$user->name} ({$uid}) already exists.";
	$msgClass = 'warning';
      }
      else {
        $localist = LocalistAPI::init( 'live' );
        $user     = $localist->get_user( $_POST[ 'userId' ] );
        $userName = $user->real_name;
        $userPath = parse_url( $user->localist_url, PHP_URL_PATH );
        $pathBits = explode( '/', $userPath );
        $userSlug = array_pop( $pathBits );
        $query = sprintf(
            'INSERT INTO localist_bkmk_user (`id`, `name`, `slug`) VALUES ( %u, \'%s\', \'%s\' );',
            $uid,
            $userName,
            $userSlug
        );
        $db->query( $query );
        $msg = "Added user {$userName}.";
      }
      break;
    case 'Add feed':
      if ( $feedAPI->feed_exists( $_POST[ 'slug' ] ) ) {
        $msg = "A feed with slug {$_POST[ 'slug' ]} already exists.";
        $msgClass = 'warning';
      }
      else {
        $query = sprintf(
            'INSERT INTO localist_bkmk_feed (`slug`, `name`) VALUES ( \'%s\', \'%s\' );',
            $_POST[ 'slug' ],
            $_POST[ 'name' ]
        );
        $db->query( $query );
        $msg = "Added feed {$_POST[ 'name' ]}.";
      }
      break;
    case 'Add user to feed':
      $uid      = $_POST[ 'userId' ];
      $fid      = $_POST[ 'feedId' ];
      $user     = $userAPI->get_user( $uid );
      $userName = $user->name;
      if ( $feedAPI->user_feed_exists( $uid, $fid ) ) {
        $feed = $feedAPI->get_feed( $fid );
        $msg  = "{$userName} can already add events to {$feed->name} ({$feed->slug}).";
        $msgClass = 'warning';
      }
      else {
        $query = sprintf(
            'INSERT INTO localist_bkmk_user_feed (`user_id`, `feed_id`) VALUES ( %u, %u );',
            $uid,
            $fid
        );
        $db->query( $query );
        $msg = "Added {$userName} to feed " . $feeds[ $fid ]->name . ' (' . $feeds[ $fid ]->slug . ').';
      }
      break;
  }
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
  <title>Bookmark Admin | Stanford Event Calendar</title>

  <!-- Meta -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="author" content="Stanford Event Calendar" />
  <meta name="description" content="Bookmark Localist events." />

  <?php include '../includes/head.html'; ?>
  <style>
    hr {
      margin-top: 1em;
    }
    #message {
      border: 2px solid green;
      padding: 1em;
      background-color: white;
    }
    #message.warning {
      border-color: red;
    }
    select {
      margin-bottom: 1em;
    }
  </style>
</head>

<body class="su-content-page su-bookmark-page">
<?php include '../includes/header.html'; ?>

<!-- main content -->
<section id="main-content" role="main">
  <div class="container">
    <h1>Bookmark Admin</h1>

    <?php if ( !empty( $msg ) ) { ?>
    <section id="message" class="<?php echo $msgClass; ?>">
      <p><?php echo $msg; ?></p>
    </section>
    <?php } ?>

    <h2>Add user</h2>
    <form name="add-user" method="post">
      <label for="userId">Localist user id:</label>
      <input name="userId" type="text" width="15" /><br/>
      <input name="submit" type="submit" value="Add user">
    </form>

    <hr/>

    <h2>Add feed</h2>
    <form name="add-feed" method="post">
      <label for="name">Feed name:</label>
      <input name="name" type="text" width="15" /><br/>
      <label for="slug">Feed slug:</label>
      <input name="slug" type="text" width="15" /><br/>
      <input name="submit" type="submit" value="Add feed">
    </form>

    <hr/>

    <h2>Add user to feed</h2>
    <form name="add-user" method="post">
      <label for="userId">Localist user id:</label>
      <input name="userId" type="text" width="15" /><br/>
      <label for="feedId">Feed:</label>
      <select name="feedId">
        <?php foreach ( $feeds as $id => $feed ) { ?>
          <option value="<?php echo $id; ?>"/><?php echo $feed->name, ' (', $feed->slug, ')'; ?></option>
        <?php } ?>
      </select>
      <input name="submit" type="submit" value="Add user to feed">
    </form>

<!-- --
    <section id="debug">
      <h2>Debug</h2>
      <?php
      debug([
        '$feeds' => $feeds,
	'$localist' => $localist
      ]);
      if ( !empty( $query ) ) {
        debug ([
            '$query' => $query
        ]);
      }
      if ( !empty( $userQuery ) ) {
        debug ([
          '$userQuery' => $userQuery,
          '$user' => $user
        ]);
      }
      if ( !empty( $_POST ) ) {
        debug( [
          '$_POST' => $_POST,
          '$user' => $user,
          '$userName' => $userName
        ] );
      }
      ?>
    </section>
<!-- -->

  </div> <!-- .container -->
</section> <!-- #main-content -->

<!-- BEGIN footer -->
<?php include '../includes/footer.html'; ?>
<!-- END footer -->
</body>
</html>
