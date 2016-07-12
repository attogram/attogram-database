<?php  // Attogram Framework - Database Module - AttogramDatabase interface 0.0.5

namespace Attogram;

/**
 * Attogram Database Object Interface
 */
interface AttogramDatabase
{

  /**
   * Query the database, return an array of results
   * @param  string $sql  The SQL query
   * @param  array  $bind (optional) Array of name/values to bind into the SQL query
   * @return array        An array of results
   */
  public function query( $sql, array $bind = array() );

  /**
   * Query the database, return only true or false
   * @param  string $sql  The SQL query
   * @param  array  $bind (optional) Array of name/values to bind into the SQL query
   * @return bool         true on successful query, false on error
   */
  public function queryb( $sql, array $bind = array() );

  /**
   * Initialize the database connection
   * @return bool true on successful initialization, false on error
   */
  public function initDB();

} // end interface AttogramDatabase
