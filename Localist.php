<?php

namespace Stanford\EventDataMigrate;

class Localist {

  /** @const string CONF_FILE - file name containing DB credentials */
  const CONF_FILE = './conf.json';

  const API_ROOT = 'https://stanford.enterprise.localist.com/api/2/';

  /** @var DB $instance - singleton instance of the class **/
  private static $instance;

  /** @var string $token - access token to allow authenticated access to Localist */
  private $token;


  public function auth_api_call( $request, $method = 'GET', $params = '', $data = '' ) {
    $url = self::API_ROOT . $request;
    if ( !empty( $params ) ) {
      $url .= '?' . http_build_query( $params );
    }
    $headers = [
      "Content-Type: application/json",
      "Authorization: Bearer {$this->token}",
    ];

    $ch = curl_init( $url );
    switch ( $method ) {
      case 'POST':
        $payload = json_encode( $data );
        $headers[] = 'Content-Length: ' . strlen( $payload) ;
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        break;
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec( $ch );
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
   * @return Events singleton instance
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