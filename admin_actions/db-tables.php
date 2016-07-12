<?php // Attogram Framework - Database Module - Database Tables v0.0.54

namespace attogram;

$title = 'Database Tables';
$this->pageHeader($title );
print '<div class="container"><h1 class="squished">' . $title . '</h1><hr />';

if( !$this->database->get_tables() || !$this->database->tables ) {
  print 'ERROR: no table definitions found.</div>';
  $this->pageFooter();
  exit;
}

foreach( $this->database->tables AS $table_name => $table_definition ) {
  $count = $this->database->get_table_count( $table_name );
  print '<p>'
  . "<strong>$table_name</strong> table: <code>$count</code> entries"
  . '<textarea class="form-control" rows="8">' . $table_definition . '</textarea>'
  . '</p>';
}

print '</div>';
$this->pageFooter();
