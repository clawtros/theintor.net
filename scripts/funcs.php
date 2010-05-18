<?php

/**
 * return mysqli
 */
function get_db() {
  $ini = parse_ini_file('conf.ini');
  return mysqli_connect($ini['host'],$ini['user'],$ini['password'],$ini['schema']);
}

function get_raw_subdomain() {
  $server_name = explode('.',idn_to_utf8($_SERVER['SERVER_NAME']));
  return implode(".",array_slice($server_name,0,sizeof($server_name)-2));
}

/**
 * adds a subdomain to the database
 *
 * @param mysqli connection db connects to db
 * @param string $subdomain subdomain to check
 * @return boolean
 */
function subdomain_exists($db, $subdomain) {
  $stmt = mysqli_prepare($db, "SELECT subdomain FROM urls WHERE subdomain = ?");
  mysqli_stmt_bind_param($stmt, 's', $subdomain);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  $result = mysqli_stmt_num_rows($stmt) > 0;
  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);  
  return $result;
}

function get_last_connections($db, $limit) {
  $stmt = mysqli_prepare($db, "SELECT responder, target FROM response_lookup INNER JOIN urls ON subdomain=target OR subdomain=responder ORDER BY last_reply_time DESC LIMIT ?");
  mysqli_stmt_bind_param($stmt, 'd', $limit);
  mysqli_stmt_bind_result($stmt, $responder, $target);
  mysqli_stmt_execute($stmt);
  $storage = array();
  while (mysqli_stmt_fetch($stmt)) {
    $storage[$responder] = True;
    $storage[$target] = True;
  }

  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);  
  return array_keys($storage);
}

function get_relationships( $db, $name=null, $max_depth=2, $depth=0) {
  $results = array();

  if ($name) {
    $sql = "SELECT responder, target, last_reply_time FROM response_lookup where responder=? or target=? order by last_reply_time desc ";
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
      foreach ($result as $candidate) {
        if ($candidate != $name) {
          $rels = get_relationships($db, $candidate, $maxdepth, $depth + 1);
          foreach ($rels as $key=>$vals) {
            list($k, $v) = $vals;
            $newresults["\"$k\" -> \"$v\""]=array($k,$v);
          }
        }
      }
    }
    $results = $newresults;
  }
  return $results;
}

/**
 * adds a subdomain to the database
 *
 * @param mysqli connection db connects to db
 * @param string $subdomain subdomain to check
 * @return boolean
 */
function add_subdomain( $db, $subdomain, $response_to="" ) {
  $stmt = mysqli_prepare($db, "insert into urls (`subdomain`, `time_created`) values (?,NOW())");
  mysqli_stmt_bind_param($stmt, 's', $subdomain);
  mysqli_stmt_execute($stmt);

  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);
}

function fetch_subdomain( $db, $subdomain ) {
  $stmt = mysqli_prepare($db, "select id, subdomain, hits, time_created, last_view, request_uri from urls where subdomain = ?");
  mysqli_stmt_bind_param($stmt, 's', $subdomain);
  mysqli_stmt_bind_result($stmt, $id, $sd, $hits, $time_created, $last_view, $request_uri);
  $result = NULL;
  mysqli_stmt_execute($stmt);
  while (mysqli_stmt_fetch($stmt)) {
    $result = array('id'=>$id,  
                    'subdomain'=>$subdomain, 
                    'hits'=>$hits, 
                    'time_created'=>$time_created, 
                    'last_view'=>$last_view, 
                    'request_uri'=>$request_uri
                    );
  }
  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);
  return $result;
}

function fetch_subdomain_by_id( $db, $id ) {
  $stmt = mysqli_prepare($db, "select subdomain, hits, time_created, last_view, request_uri from urls where id = ?");
  mysqli_stmt_bind_param($stmt, 's', $id);
  mysqli_stmt_bind_result($stmt, $sd, $hits, $time_created, $last_view, $request_uri);
  $result = NULL;
  mysqli_stmt_execute($stmt);
  while (mysqli_stmt_fetch($stmt)) {
    $result = array('subdomain'=>$sd, 
                    'hits'=>$hits, 
                    'time_created'=>$time_created, 
                    'last_view'=>$last_view, 
                    'request_uri'=>$request_uri
                    );
  }
  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);
  return $result;
}


/**
 * increments hit count and last viewed on a given subdomain
 *
 * @param mysqli $db <description>
 * @param string $subdomain <description>
 * @return boolean
 */
function update_subdomain( $db, $subdomain, $request_uri="" ) {
  $stmt = mysqli_prepare($db, "SELECT subdomain, hits FROM urls WHERE subdomain = ?");
  mysqli_stmt_bind_param($stmt, 's', $subdomain);
  mysqli_stmt_bind_result($stmt, $sd, $count);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_fetch($stmt);
  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);  

  $count = $count + 1;
  $stmt = mysqli_prepare($db, "update urls set last_view=NOW(), hits=?, request_uri=? where subdomain = ?");
  mysqli_stmt_bind_param($stmt, 'dss', $count,  $request_uri, $subdomain);
  mysqli_stmt_execute($stmt);

  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);  

}

/**
 * initializes subdomain view inserting if necessary
 *
 * @param mysqli $db <description>
 * @param string $subdomain <description>
 * @return void
 */
function hit_subdomain( $db, $subdomain ) {
  if (subdomain_exists($db, $subdomain)) {
    //update_subdomain($db, $subdomain);
    return fetch_subdomain($db, $subdomain);
  } else {
    add_subdomain($db, $subdomain);
  }
}

function parse_request() {

}
