<?php 
include( 'scripts/funcs.php' );

$server_name = explode('.',$_SERVER['SERVER_NAME']);
$subdomain = str_replace('-',' ',$server_name[0]);
ini_set('display_errors', 'On');     
$db = get_db();
hit_subdomain($db, $subdomain);
   ?><!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $subdomain ?></title>
      
    <style type="text/css">
      body { font-family: Franklin Gothic, Futura, Helvetica, Arial, Sans}
      .phrase { margin-top:20%; font-size:48px; text-align:center }
    </style>
  </head>
  <body>
    <h1 class="phrase"><?php echo $subdomain ?></h1>
  </body>
</html>

