<?php
include( 'scripts/funcs.php' );
include( 'scripts/classes.php' );

$parsed_ini = parse_ini_file("scripts/conf.ini");
$server_name = explode('.',$_SERVER['SERVER_NAME']);

$subdomain = get_raw_subdomain();
//var_dump(get_raw_subdomain()); die;
$result = "";
$db = get_db();

function sanitize_subdomain($subdomain) {
  return $subdomain;
}

function format_result($result) {
  return '"'.sanitize_subdomain($result[0]).'" -> "'.sanitize_subdomain($result[1]).'" [color=dodgerblue4];';
}

$results = get_relationships( $db, $subdomain, 2);
$result_string = implode('', array_map('format_result', $results));
$dot_str = "digraph test { ".($subdomain ? " \"$subdomain\" [shape=polygon, color=gold]; " : "" ). "
graph [truecolor bgcolor=\"#ffffff00\"] 
".urldecode($result_string)." }";
$tmp_file = tempnam($parsed_ini['tmp_location'], 'intornet');
$handle = fopen($tmp_file, "w");
fwrite($handle, $dot_str);
$exec_str = "cat $tmp_file | ".$parsed_ini['graphviz_location']." -Gsize=10,15 -Tpng -Nstyle=filled -Kfdp";
if (!$_GET['dbg']) {
  header("Content-Type: image/png");
  passthru($exec_str);
  fclose($handle);
  unlink($tmp_file);
} else {
  print_r($result_string);
  //  print_r($results);
}

?>
