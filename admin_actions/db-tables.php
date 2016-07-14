<?php // Attogram Framework - Database Module - Database Tables v0.0.8

namespace Attogram;

$title = 'Database Tables';
$this->pageHeader($title);
print '<div class="container"><h1 class="squished">' . $title . '</h1><hr />';

if (!$this->database->loadTableDefinitions() || !$this->database->tables) {
    print 'ERROR: no table definitions found.</div>';
    $this->pageFooter();
    exit;
}

foreach ($this->database->tables AS $tableName => $table_definition) {
    $count = $this->database->getTableCount($tableName);
    print '<p><strong>$tableName</strong> table: <code>'.$count.'</code> entries'
        . '<textarea class="form-control" rows="8">'.$table_definition.'</textarea></p>';
}

print '</div>';
$this->pageFooter();
