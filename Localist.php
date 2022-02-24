<?php

namespace Stanford\EventBookmark;

class Localist {

  /** @const string CONF_FILE - file name containing DB credentials */
  const CONF_FILE = './conf.json';

  const API_ROOT = 'https://events.stanford.edu/api/2/';

  /** @var DB $instance - singleton instance of the class **/
  private static $instance;

  /** @var string $token - access token to allow authenticated access to Localist */
  private $token;


  public function auth_api_call( $request, $params = '' ) {
    $url = self::API_ROOT . $request;
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
    curl_close( $ch );

    return json_decode( $response );
  }

  /**
   * Create singleton instance.
   * private to enforce singleton pattern
   */
  private function __construct() {
    $this->load_config();
  }

  /**
   * @return Localist singleton instance
   */
  public static function init() {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new Localist();
    }
    return self::$instance;
  }

  /**
   * Load the db credentials from the configuration file
   */
  protected function load_config() {
    try {
      $config = file_get_contents( self::CONF_FILE );
    }
    catch ( \Exception $exception ) {
      echo "ERROR: unable to read file ", self::CONF_FILE;
      echo $exception->getMessage();
      die();
    }
    $conf = \json_decode( $config );
    $this->token = $conf->LOCALIST_ACCESS_TOKEN;
  }

}