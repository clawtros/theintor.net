<?php
include( 'scripts/funcs.php' );
include( 'scripts/classes.php' );

$parsed_ini = parse_ini_file("scripts/conf.ini");
$server_name = explode('.',$_SERVER['SERVER_NAME']);

$subdomain = get_raw_subdomain();
//var_dump(get_raw_subdomain()); die;
$result = "";
$db = get_db();

function get_relationships($name=null, $depth=1, $max_depth=2) {
  global $db;
  $results = array();

  if ($name) {
    $sql = "SELECT responder, target, last_reply_time FROM response_lookup where responder=? or target=?";
    if ($_GET['l']) { $sql .= ' limit '.(int)$_GET['l']; }
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $name, $name);
  } else {
    $sql = "SELECT responder, target, last_reply_time FROM response_lookup order by last_reply_time desc";
    if ($_GET['l']) { $sql .= ' limit '.(int)$_GET['l']; }
    $stmt = mysqli_prepare($db,$sql); 
  }
  mysqli_stmt_bind_result($stmt, $responder, $target, $last_reply_time);
  mysqli_stmt_execute($stmt);

  while (mysqli_stmt_fetch($stmt)) {
    $results["\"$responder\" -> \"$target\""] = array($responder, $target);
  }
  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);  

  if ($depth < $max_depth && $name) {
    $newresults = $results;
    foreach ($results as $result) {
      list($responder, $target) = $result;
      if ($target != $name) {
        $rels = get_relationships($target, $depth + 1);
        foreach ($rels as $key=>$vals) {
          list($k, $v) = $vals;
          $newresults["\"$k\" -> \"$v\""]=array($k,$v);
        }
      }
      if ($responder != $name) {
          $rels = get_relationships($responder, $depth + 1);
          foreach ($rels as $key=>$vals) {
            list($k, $v) = $vals;
            $newresults["\"$k\" -> \"$v\""]=array($k,$v);
          }
      }
    }
    $results = $newresults;
  }
  return $results;
}

$results = get_relationships($subdomain);
$result_string = implode(';', array_keys($results));

$dot_str = "digraph test {  graph [truecolor bgcolor=\"#ffffff00\"] 
".$result_string." }";
$exec_str = "echo '$dot_str' | ".$parsed_ini['graphviz_location']." -Gsize=10,15 -Tpng -Nstyle=filled -Kfdp";
if (!$_GET['dbg']) {
  header("Content-Type: image/png");
  passthru($exec_str);
} else {
  print_r($result_string);
  print_r($results);
}

?>
