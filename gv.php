<?php
include( 'scripts/funcs.php' );
include( 'scripts/classes.php' );

$parsed_ini = parse_ini_file("scripts/conf.ini");

$result = "";
$db = get_db();
$stmt = mysqli_prepare($db, "SELECT responder, target, last_reply_time FROM response_lookup");
//mysqli_stmt_bind_param($stmt, 'ss', $this->subdomain, $this->subdomain);

mysqli_stmt_bind_result($stmt, $responder, $target, $last_reply_time);
mysqli_stmt_execute($stmt);

$results = array();

while (mysqli_stmt_fetch($stmt)) {
  array_push($results, "\"$responder\" -> \"$target\"");
}

$result_string = implode(';', $results);

mysqli_stmt_free_result($stmt);
mysqli_stmt_close($stmt);  

$dot_str = "digraph test { ".$result_string." }";
$exec_str = "echo '$dot_str' | ".$parsed_ini['graphviz_location']." -Gsize=[6,6] -Tpng -Nstyle=filled ";


header("Content-Type: image/png");
passthru($exec_str);

?>