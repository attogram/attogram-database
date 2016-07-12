<?php // Attogram Framework - Base Module - Events log v0.1.5

namespace Attogram;

list( $limit, $offset ) = $this->database->getSetLimitAndOffset(
  $defaultLimit  = 1000,
  $defaultOffset = 0,
  $maxLimit      = 10000,
  $minLimit      = 10
);

$sql = 'SELECT * FROM event ORDER BY id DESC LIMIT ' . $limit;
if( $offset ) {
  $sql .= ' OFFSET ' . $offset;
}

$events = $this->database->query($sql);

$count = $this->database->get_table_count('event');

$this->pageHeader('⌚ Event Log');

print '<div class="container"><h1 class="squished">⌚ Event Log</h1>';

print $this->database->pager( $count, $limit, $offset );

foreach( $events as $v ) {
  $vm = explode( ' ', $v['message'] );
  $datetime = ltrim( $vm[0], '[' ) . ' ' . rtrim( $vm[1], ']');
  $type = rtrim( $vm[2], ':' );
  $type = preg_replace('/^event\./', '', $type);
  $trash = array_shift($vm); $trash = array_shift($vm); $trash = array_shift($vm);
  $message = implode(' ', $vm);
  $message = rtrim($message); $message = rtrim($message, '[..]'); $message = rtrim($message);
  $message = rtrim($message); $message = rtrim($message, '[..]'); $message = rtrim($message);
  print '<div class="row" style="border:1px solid #ccc;">'
  . '<div class="col-sm-2"><small>' . $datetime . '</small></div>'
  . '<div class="col-sm-1"><small>' . $type . '</small></div>'
  . '<div class="col-sm-9">' . $message . '</div>'
  . '</div>';
}

print $this->database->pager( $count, $limit, $offset );

print '</div>';

$this->pageFooter();
