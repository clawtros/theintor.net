<?php 
ini_set('display_errors', 'On');     
include( 'scripts/funcs.php' );
include( 'scripts/classes.php' );
$server_name = explode('.',idn_to_utf8($_SERVER['SERVER_NAME']));

$title = str_replace('-',' ',$server_name[0]);
$db = get_db();
$ma = new ModifierApplicator($server_name[0], $registered_modifiers, $_SERVER['REQUEST_URI'], $db);

hit_subdomain($db, $ma->raw_subdomain);

   ?><!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/theintornet.css" />
    <title><?php echo $title ?></title>
    <style type="text/css">
      <?php echo $ma->css_additions; ?>
    </style>
  </head>
  <body>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-16419330-1']);
  _gaq.push(['_setDomainName', '.theintor.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
    <div id="where_things_go">
      <h1 class="phrase"><?php echo $ma->getModifiedSubdomain(); ?></h1>
    </div>
  </body>
  <!-- a member of Adam Benzan's Internet Conglomerate - http://blog.removablefeast.com/ http://cruciverbalizer.com/ -->
</html>
