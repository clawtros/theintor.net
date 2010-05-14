<?php 
ini_set('display_errors', 'On');     
include( 'scripts/funcs.php' );
include( 'scripts/classes.php' );
$server_name = explode('.',$_SERVER['SERVER_NAME']);
$subdomain = str_replace('---','<br/>',$server_name[0]);
$subdomain = str_replace('--','&ndash;', $subdomain);
$subdomain = str_replace('-',' ',$subdomain);
$ma = new ModifierApplicator($subdomain, $registered_modifiers, $_SERVER['REQUEST_URI']);
$db = get_db();
hit_subdomain($db, $subdomain);

   ?><!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/theintornet.css" />
    <title><?php echo $subdomain ?></title>
    <style type="text/css">
      <?php echo $ma->css_additions; ?>
    </style>
  </head>
  <body>
    <div id="where_things_go">
      <h1 class="phrase"><?php echo $ma->getModifiedSubdomain(); ?></h1>
    </div>
  </body>
</html>
