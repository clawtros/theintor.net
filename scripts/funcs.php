<?php

/**
 * return mysqli
 */
function get_db() {
  $ini = parse_ini_file('conf.ini');
  return mysqli_connect($ini['host'],$ini['user'],$ini['password'],$ini['schema']);
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

/**
 * increments hit count and last viewed on a given subdomain
 *
 * @param mysqli $db <description>
 * @param string $subdomain <description>
 * @return boolean
 */
function update_subdomain( $db, $subdomain ) {
  $stmt = mysqli_prepare($db, "SELECT subdomain, hits FROM urls WHERE subdomain = ?");
  mysqli_stmt_bind_param($stmt, 's', $subdomain);
  mysqli_stmt_bind_result($stmt, $sd, $count);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_fetch($stmt);
  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);  

  $count = $count + 1;
  $stmt = mysqli_prepare($db, "update urls set last_view=NOW(), hits=? where subdomain = ?");
  mysqli_stmt_bind_param($stmt, 'ds', $count, $subdomain);
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
    update_subdomain($db, $subdomain);
  } else {
    add_subdomain($db, $subdomain);
  }
}

function parse_request() {

}
