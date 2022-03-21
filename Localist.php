<?php

namespace Stanford\EventBookmark;

class Localist {

  /** @const string CONF_FILE - file name containing DB credentials */
  const CONF_FILE = './conf.json';

  private $api_root;

  /** @var Feed $instance - singleton instance of the class **/
  private static $instance;

  /** @var string $token - access token to allow authenticated access to Localist */
  private $token;


  public function get_user( int $uid ) {
    $result = $this->auth_api_call( "users/{$uid}" );
    //echo "/nLocalist->get_user( {$uid} ):\n";
    //echo htmlentities( print_r( $result, TRUE ) );
    return isset( $result->user) ? $result->user : FALSE;
  }

  public function get_event( int $event_id ) {
    $result = $this->auth_api_call( "events/{$event_id}" );
    //echo "/nLocalist->get_event( {$event_id} ):\n";
    //echo htmlentities( print_r( $result, TRUE ) );
    return isset( $result->event ) ? $result->event : FALSE;
  }

  public function auth_api_call( $request, $params = '' ) {
    $url = $this->api_root . $request;
    if ( !empty( $params ) ) {
      $url .= '?' . http_build_query( $params );
    }
    $headers = [
      "Content-Type: application/json",
      "Authorization: Bearer {$this->token}",
    ];

    $ch = curl_init( $url );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // echo "Curling {$url}\n"; // DEBUG
    $response = curl_exec( $ch );
    // echo "Response:\n"; printf( $response ); echo "\n"; // DEBUG
    if ( $response === FALSE ) {
      echo "ERROR: Failed to get a response from {$url}<br/>\n";
      echo curl_error( $ch );
      return FALSE;
    }
    curl_close( $ch );

    return json_decode( $response );
  }

  /***********************/
  /***** Class Setup *****/
  /***********************/

  /**
   * Initialize singleton instance
   *
   * @param string $env - 'live' or 'staging'
   * @return Localist singleton instance
   */
  public static function init( string $env = 'live' ) {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new Localist( $env );
    }
    return self::$instance;
  }

  /**
   * Construct instance
   * private to enforce singleton pattern
   *
   * @param string $env - 'live' or 'staging'
   */
  private function __construct( string $env ) {
    $this->api_root = $env == 'live'
      ? 'https://events.stanford.edu/api/2/'
      : 'https://stanford.staging.localist.com/api/2/';
    // get access token for authorized access to Localist
    $this->load_config( $env );
  }

  /**
   * Load the API access token from the configuration file
   *
   * @param string $env - 'live' or 'staging'
   */
  protected function load_config( $env ) {
    try {
      $config = file_get_contents( self::CONF_FILE );
    }
    catch ( \Exception $exception ) {
      echo "ERROR: unable to read file ", self::CONF_FILE;
      echo $exception->getMessage();
      die();
    }
    $conf = \json_decode( $config );
    $this->token = $env == 'live'
      ? $conf->LOCALIST_ACCESS_TOKEN_LIVE
      : $conf->LOCALIST_ACCESS_TOKEN_STAG;
  }

}