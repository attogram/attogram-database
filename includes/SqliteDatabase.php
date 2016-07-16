<?php  // Attogram Framework - Database Module - SqliteDatabase class v0.3.17

namespace Attogram;

/**
 * Attogram SqliteDatabase
 */
class SqliteDatabase implements AttogramDatabase
{

    public $databaseName;      // (string) path/filename of the SQLite database file
    public $modulesDirectory;  // (string) The Attogram Modules directory
    public $log;               // (object) PSR-3 compliant logger
    public $database;          // (object) The PDO database object

    /**
     * initialize database settings
     * @param string $databaseName relative path to the SQLite database file
     * @param string $modulesDirectory relative path to the Attogram modules directory
     * @param object $log psr3 logger object
     * @return void
     */
    public function __construct( $databaseName, $modulesDirectory, $log )
    {
      $this->databaseName = $databaseName;
      $this->modulesDirectory = $modulesDirectory;
      $this->log = $log;
      $this->log->debug('START SqliteDatabase');
    }

    /**
     * Initialize the database connection
     * @return bool true on successful initialization, false on error
     */
    public function initDB()
    {
        if (is_object($this->database) && get_class($this->database) == 'PDO') {
            return true; // if PDO database object already set
        }
        if (!in_array('sqlite', \PDO::getAvailableDrivers())) {
            $this->log->error('initDB: SQLite PDO driver not found');
            return false;
        }
        if (is_file($this->databaseName) && !is_writeable($this->databaseName)) {
            $this->log->error('initDB: NOTICE: database file not writeable: ' . $this->databaseName);
            // SELECT will work, UPDATE will not work
        }
        if (!is_file($this->databaseName)) {
            $this->log->debug('initDB: NOTICE: creating database file: ' . $this->databaseName);
        }
        try {
            $this->database = new \PDO('sqlite:'. $this->databaseName);
        } catch (\PDOException $e) {
            $this->log->error('initDB: error opening database: ' . $e->getMessage());
            return false;
        }
        $this->log->debug('initDB: Got SQLite database: ' . $this->databaseName);
        return true; // got database, into $this->database
    }

    /**
     * Query the database, return an array of results
     * @param  string $sql  The SQL query
     * @param  array  $bind (optional) Array of name/values to bind into the SQL query
     * @return array        An array of results
     */
    public function query( $sql, array $bind = array() )
    {
      $this->log->debug('QUERY: backtrace=' . ( ($btr = debug_backtrace()) ? $btr[1]['function'] : '?' ) . ' sql=' . $sql);
      if( $bind ) {
        $this->log->debug('QUERY: bind=',$bind);
      }
      if( !$this->initDB() ) {
        $this->log->error('QUERY: Can not get database');
        return array();
      }
      $statement = $this->queryPrepare($sql);
      if( !$statement ) {
        list($sqlstate, $errorCode, $errorString) = @$this->database->errorInfo();
        $this->log->error("QUERY: prepare failed: $sqlstate:$errorCode:$errorString");
        return array();
      }
      while( $binders = each($bind) ) {
        $statement->bindParam( $binders[0], $binders[1] );
        // dev: Warning: PDOStatement::bindParam(): SQLSTATE[HY093]: Invalid parameter number: Columns/Parameters are 1-based
      }
      if( !$statement->execute() ) {
        $this->log->error('QUERY: Can not execute query');
        return array();
      }
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      if( !$result && $this->database->errorCode() != '00000') { // query failed
        $this->log->error('QUERY: Query failed');
        $result = array();
      }
      $this->log->debug('QUERY: result size=' . sizeof($result) );
      return $result;
    }

    /**
     * Query the database, return only true or false
     * @param  string $sql  The SQL query
     * @param  array  $bind (optional) Array of name/values to bind into the SQL query
     * @return bool         true on successful query, false on error
     */
    public function queryb( $sql, array $bind = array() )
    {
      $this->log->debug('QUERYB: backtrace=' . ( ($btr = debug_backtrace()) ? $btr[1]['function'] : '?' ) . ' sql=' . $sql);
      if( $bind ) {
        $this->log->debug('QUERYB: bind=',$bind);
      }
      if( !$this->initDB() ) {
        $this->log->error('QUERYB: Can not get database');
        return false;
      }
      $statement = $this->queryPrepare($sql);
      if( !$statement ) {
        list($sqlstate, $errorCode, $errorString) = @$this->database->errorInfo();
        $this->log->error("QUERYB: prepare failed: $sqlstate:$errorCode:$errorString");
        return false;
      }
      while( $binder = each($bind) ) {
        $statement->bindParam($binder[0], $binder[1]);
      }
      if( !$statement->execute() ) {
        list($sqlstate, $errorCode, $errorString) = @$this->database->errorInfo();
        $this->log->error("QUERYB: execute failed: $sqlstate:$errorCode:$errorString");
        return false;
      }
      $this->log->debug('QUERYB true');
      return true;
     }

    /**
     * Prepare a SQL query
     * @param string $sql The SQL query to prepare
     * @return object|boolean
     */
    public function queryPrepare( $sql )
    {
      $statement = $this->database->prepare($sql);
      if( $statement ) { return $statement; }
      list($sqlstate, $errorCode, $errorString) = @$this->database->errorInfo();
      $this->log->error("QUERY_PREPARE: Can not prepare sql: $sqlstate:$errorCode:$errorString");
      if( $sqlstate == 'HY000' && $errorCode == '1' && preg_match('/^no such table/', $errorString) ) { // table not found
        $table = str_replace('no such table: ', '', $errorString); // get table name
        if( $this->createTable($table) ) { // create table
          $this->log->notice("QUERY_PREPARE: Created table: $table");
          $statement = $this->database->prepare($sql);
          if( $statement ) { return $statement; } // try again
          $this->log->error('QUERY_PREPARE: Still can not prepare sql');
          return false;
        }
        $this->log->error("QUERY_PREPARE: Can not create table: $table");
        return false;
      }
    }

    /**
     * Get the table definitions from all the modules
     * @return boolean
     */
    public function loadTableDefinitions()
    {
      if( isset($this->tables) && is_array($this->tables) ) {
        return true;
      }
      $dirs = Attogram::getAllSubdirectories( $this->modulesDirectory, 'tables');
      if( !$dirs ) {
        $this->log->debug('GET_TABLES: No module tables found');
        return false;
      }
      $this->tables = array();
      foreach( $dirs as $d ) {
        foreach( array_diff(scandir($d), Attogram::getSkipFiles() ) as $f ) {
          $file = $d . '/' . $f;
          if( !is_file($file) || !is_readable($file) || !preg_match('/\.sql$/',$file) ) {
            continue; // .sql files only
          }
          $tableName = str_replace('.sql','',$f);
          $this->tables[$tableName] = file_get_contents($file);
          $this->log->debug('GET_TABLES: got table: ' . $tableName . ' from ' . $file);
        }
      }
      return true;
    }

    /**
     * Create a table in the active SQLite database
     * @param string $table The name of the table to create
     * @return boolean
     */
    public function createTable( $table )
    {
      $this->loadTableDefinitions();
      if( !isset($this->tables[$table]) ) {
        $this->log->error("CREATE_TABLE: Unknown table: $table");
        return false;
      }
      if( !$this->queryb( $this->tables[$table] ) ) {
        $this->log->error("CREATE_TABLE: failed to create: $table");
        return false;
      }
      return true;
    }

    /**
     * Get the count of entries in a table
     * @param string $table    The table name
     * @param string $idField  (optional) The id field used for counting
     * @param string $where    (optional) The SQL WHERE clause to add
     * @return int             The number of entries
     */
    public function getTableCount( $table, $idField = 'id', $where = '' )
    {
      $sql = 'SELECT count(' . $idField . ') AS count FROM ' . $table;
      if( $where ) {
        $sql .= ' ' . $where;
      }
      $count = $this->query($sql);
      if( $count ) {
        return $count[0]['count'];
      }
      return 0;
    }

    /**
     * tabler - HTML table with view of database table content, plus optional admin links
     *
     * @param  string $table         The table name
     * @param  string $tableId      The name of the table ID field (or equivilant )
     * @param  string $nameSingular The name of what we are editing, singular form
     * @param  string $namePlural   The name of what we are editing, plural form
     * @param  array  $col           Column Display Info - array of array('class'=>'...', 'title'=>'...', 'key'=>'...')
     * @param  string $sql           SQL query to view contents of table
     * @param  string $countSql     SQL query to get total number of items in table
     * @param  string $publicLink   URL to the public version of this view
     * @param  string $adminLink    URL to the admin version of this view
     * @param  bool   $showEdit     Show edit tools
     * @param  int    $perPage      (optional) The number of results to show per page. Defaults to 50
     *
     * @return string                HTML fragment
     */
    public function tabler( $table, $tableId, $nameSingular, $namePlural, $publicLink, array $col, $sql, $adminLink, $showEdit, $perPage )
    {

      $count = 0;
      $result = $this->query( 'SELECT count(' . $tableId . ') AS count FROM ' . $table );
      if( $result ) {
        $count = $result[0]['count'];
      }

      list( $limit, $offset ) = $this->getSetLimitAndOffset(
        $perPage, // $defaultLimit
        0, // $defaultOffset
        1000, // $maxLimit
        5 // $minLimit
      );

      $this->log->debug("TABLER: count=$count limit=$limit offset=$offset");
      $sql .= " LIMIT $limit";
      if( $offset ) {
        $sql .= ", $offset";
      }
      $result = $this->query($sql);

      $adminCreate = $adminEdit = $adminDelete = '';
      if( $showEdit ) {
        $adminCreate = '../db-admin/?table=' . $table .'&amp;action=row_create';
        $adminEdit = '../db-admin/?table=' . $table . '&amp;action=row_editordelete&amp;type=edit&amp;pk='; // [#ID]
        $adminDelete = '../db-admin/?table=' . $table . '&amp;action=row_editordelete&amp;type=delete&amp;pk='; // [#ID]
      }
      print '<div class="container">';
      print $this->pager( $count, $limit, $offset );

      print '<p>';

      if( $showEdit ) {
        if( $publicLink ) {
          print '<a href="' . $publicLink . '">👤 view</a> &nbsp; &nbsp; &nbsp; ';
        }
        print '<a target="_db" href="' . $adminCreate . '">➕ new ' . $nameSingular . '</a>';
      }

      print '</p><table class="table table-bordered table-hover table-condensed"><colgroup>';

      foreach( $col as $column ) {
        print '<col class="' . $column['class'] . '">';
      }
      if( $showEdit ) {
        print '<col class="col-md-1">';
      }
      print '</colgroup><thead><tr class="active">';

      foreach( $col as $column ) {
        print '<th>' . $column['title'] . '</th>';
      }
      if( $showEdit ) {
        print '<th><nobr><small>'
        . 'edit <span class="glyphicon glyphicon-wrench" title="edit"></span>'
        . ' &nbsp; '
        . '<span class="glyphicon glyphicon-remove-circle" title="delete"></span> delete'
        . '</small></nobr></th>';
      }
      print '</tr></thead><tbody>';

      foreach( $result as $row ) {
        print '<tr>';
        foreach( $col as $column ) {
          print '<td>' . htmlentities($row[ $column['key'] ]) . '</td>';
        }
        if( $showEdit ) {
          print '<td> &nbsp; &nbsp; '
          . '<a target="_db" href="' . $adminEdit . '[' . $row['id'] . ']">'
          . '<span class="glyphicon glyphicon-wrench" title="edit"></span></a>'
          . ' &nbsp; &nbsp; '
          . '<a target="_db" href="' . $adminDelete . '[' . $row['id'] . ']">'
          . '<span class="glyphicon glyphicon-remove-circle" title="delete"></span></a>'
          . '</td>'
          ;
        }
        print '</tr>';
      }
      print '</tbody></table></div>';
    }

    /**
     * Show pagination links
     * @param  int    $count   The Total Resultset Count
     * @param  int    $limit   The # of results to list per page
     * @param  int    $offset  The item # to start the list
     * @param  string $preQS  (optional) URL Query String to prepend to pagination links, pairs of  name=value&name=value&...
     * @return string          HTML fragment
     */
    public function pager( $count, $limit, $offset, $preQS = '' )
    {

      if( $limit > $count ) {
        $limit = $count;
      }
      if( $offset > $count ) {
        $offset = $count - $limit;
      }
      $startCount = $offset + 1;
      $endCount = $offset + $limit;
      if( !$endCount ) {
        $startCount = 0;
      }
      if( $endCount > $count ) {
        $endCount = $count;
      }

      $result = '<p class="small">Showing # ' . "<strong>$startCount</strong> - <strong>$endCount</strong> of <code>$count</code> results</p>";

      if( $limit <= 0 ) {
        $totalPages = 0;
      } else {
        $totalPages = ceil( $count / $limit );
        if( $totalPages == 1 ) {
          $totalPages = 0;
        }
      }

      if( $totalPages ) {
        $result .= '<ul class="pagination pagination-sm squished">';
        $pOffset = 0;
        $urlStart = '?';
        if( $preQS ) {
          $urlStart .= $preQS . '&amp;';
        }
        for( $x = 0; $x < $totalPages; $x++ ) {
          $active = '';
          if( $startCount == $pOffset + 1 ) {
            $active = ' class="active"';
          }
          $url = $urlStart . 'l=' . $limit . '&amp;o=' . $pOffset;
          $result .= '<li' . $active . '><a href="' . $url . '">' . ($x+1) . '</a></li>';
          $pOffset += $limit;
        }
        $result .= '</ul>';
      }

      return $result;
    }

    /**
     * Get requested Query limit and offset from HTTP GET variables,
     * error check, and then return valid limit and offset
     * @param  int    $defaultLimit  (optional) The default limit, if not set.   Defaults to 1000
     * @param  int    $defaultOffset (optional) The default offset, if not set.  Defaults to 0
     * @param  int    $maxLimit      (optional) The maximum allowed limit value. Defaults to 5000
     * @param  int    $minLimit      (optional) The minimum allowed limit value. Defaults to 100
     * @return array                  Array of (limit,offset)
     */
    public function getSetLimitAndOffset( $defaultLimit = 1000, $defaultOffset = 0, $maxLimit = 5000, $minLimit = 100 )
    {
      //$this->log->debug("getSetLimitAndOffset: default_limit=$defaultLimit default_offset=$defaultOffset max_limit=$maxLimit min_limit=$minLimit ");
      $limit = $defaultLimit;
      $offset = $defaultOffset;
      if( isset($_GET['l']) && $_GET['l'] ) { // LIMIT
        $limit = (int)$_GET['l'];
        $offset = $defaultOffset;
        if( isset($_GET['o']) && $_GET['o'] ) { // OFFSET
          $offset = (int)$_GET['o'];
        }
      }
      if( $limit > $maxLimit ) {
        $this->log->notice("getSetLimitAndOffset: limit=$limit too large.  Setting to max limit=$maxLimit");
        $limit = $maxLimit;
      }
      if( $limit < $minLimit ) {
        $this->log->notice("getSetLimitAndOffset: limit=$limit too small.  Setting to min limit=$minLimit");
        $limit = $minLimit;
      }
      //$this->log->debug("getSetLimitAndOffset: limit=$limit offset=$offset");
      return array( $limit, $offset );
    }

} // END of class SqliteDatabase
