<?php // Attogram Framework - Database Module - Database Tables v0.0.10

namespace Attogram;

$title = 'Database Tables';
$this->pageHeader($title);
print '<div class="container"><h1 class="squished">' . $title . '</h1><hr />';

if (!$this->database->loadTableDefinitions() || !$this->database->tables) {
    print 'ERROR: no table definitions found.</div>';
    $this->pageFooter();
    exit;
}

foreach ($this->database->tables AS $tableName => $tableDefinition) {
    print '<p><h2 style="display:inline;">'.$tableName.'</h3><br /><code>'
        .$this->database->getTableCount($tableName).'</code> entries'
        .'<textarea class="form-control" rows="8">'.$tableDefinition.'</textarea></p>';
}

print '</div>';
$this->pageFooter();
