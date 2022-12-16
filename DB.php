<?php

namespace Stanford\EventBookmark;

class DB {

  /** @var DB $instance - singleton instance of the class **/
  private static $instance;

  /** @var \mysqli $mysqli - db connection */
  protected $mysqli;

  /** @const string CONF_FILE - file name containing DB credentials */
  const CONF_FILE = 'conf.json';

  /** @var string $db_host - RDS host - read from CONF_FILE */
  protected $db_host;
  /** @var string $db_port - RDS port - read from CONF_FILE */
  protected $db_port;
  /** @var string $db_user - DB user name - read from CONF_FILE */
  protected $db_user;
  /** @var string $db_pw - DB password - read from CONF_FILE */
  protected $db_pw;
  /** @var string $db_schema - default schema for queries - read from CONF_FILE */
  protected $db_schema;


  /**
   * Execute a query against the DB
   * @param string $query
   * @param int    $result_mode - MYSQLI_USE_RESULT or MYSQLI_STORE_RESULT
   * @return bool|\mysqli_result
   */
  public function query( string $query, int $result_mode = MYSQLI_USE_RESULT ) {
    try {
      return $this->mysqli->query( $query, $result_mode );
    }
    catch ( \Exception $e ) {
      echo "DB::query: {$query}<br/>\n";
      echo "ERROR: ",  $e->getMessage(), "\n";
      echo "TRACE:\n", $e->getTraceAsString(), "\n";
      die();
    }
  }

  public function query_info() {
    return $this->mysqli->info;
  }

  /***********************/
  /***** Class Setup *****/
  /***********************/

  /**
   * Return singleton instance of the class
   *
   * @return DB
   */
  static public function get_instance() {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new DB;
    }
    return self::$instance;
  }

  /**
   * Build the singleton instance of the class.
   * Read db info from CONF_FILE, and instantiate \myscli.
   *
   * Would define as private to enforce singleton pattern, but parent's
   * constructor is public, so this one has to be public.
   */
  public function __construct() {
    $this->load_config(); // get the db connection info
    // Enable error reporting for mysqli before attempting to make a connection
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
      $this->mysqli = new \mysqli( $this->db_host, $this->db_user, $this->db_pw, $this->db_schema, $this->db_port );
      //printf( "Success: Connected to %s\n", $this->mysqli->host_info );
    } catch ( \Exception $e ) {
      echo "<code>\n  <pre>\n";
      echo "ERROR: ",  $e->getMessage(),       "\n";
      echo "TRACE:\n", $e->getTraceAsString(), "\n";
      echo "  </pre>\n</code>\n";
      die();
    }

    // db is utf8
    // $this->mysqli->set_charset( 'utf8mb3' ); // should work, but doesn't
    $this->mysqli->query('SET NAMES utf8mb3 COLLATE utf8_general_ci');
  }

  /**
   * Load the db credentials from the configuration file
   */
  protected function load_config() {
    try {
      $config = file_get_contents( self::CONF_FILE, TRUE );
    }
    catch ( \Exception $exception ) {
      echo "<code>\n  <pre>\n";
      echo "ERROR: unable to read file ", self::CONF_FILE;
      echo $exception->getMessage();
      echo "  </pre>\n</code>\n";
      die();
    }
    $conf = \json_decode( $config );
    $this->db_host   = $conf->DB_HOST;
    $this->db_user   = $conf->DB_USER;
    $this->db_pw     = $conf->DB_PW;
    $this->db_schema = $conf->DB_SCHEMA;
    $this->db_port   = $conf->DB_PORT;
  }
}