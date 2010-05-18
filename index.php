<?php 
ini_set('display_errors', 'On');     
include( 'scripts/funcs.php' );
include( 'scripts/classes.php' );
$db = get_db();
$server_name = explode('.',idn_to_utf8($_SERVER['SERVER_NAME']));
if ($server_name[0] == '_') {
   $reqs = explode('/',$_SERVER['REQUEST_URI']);
   $d = base64_decode($reqs[1]);
   $domain = fetch_subdomain_by_id($db, $d);
   if ($domain) {
     header("Status: 301");
     header("Location: http://".$domain['subdomain'].".theintor.net/");
   }
}
$subdomain = get_raw_subdomain();
$title = str_replace('-',' ',$server_name[0]);


$ma = new ModifierApplicator($subdomain, $registered_modifiers, $_SERVER['REQUEST_URI'], $db);
$modified_subdomain = $ma->getModifiedSubdomain();
hit_subdomain($db, $ma->raw_subdomain);

   ?><!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/theintornet.css" />
    <title><?php echo $title ?></title>

    <?php if ($ma->getJsIncludes()): ?>
    <script type="text/javascript">
      var phrase = "<?php echo $modified_subdomain; ?>";
      
    </script>
    <?php endif; ?> 
    <?php foreach ($ma->getJsIncludes() as $js_file): ?>
    <script type="text/javascript" src="/js/<?php echo $js_file ?>.js"></script>
    <?php endforeach; ?>

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
      <?php if ($subdomain): ?>
      <h1 class="phrase" id="phrase"><?php echo $modified_subdomain; ?></h1>
      <?php echo $ma->post_closing_html; ?>
      <?php else: ?>
      <h1 class="frontpage phrase" style="margin-top:0px;">THE INTOR.NET - LAST 25 CONNECTIONS - <a href="/about/">ABOUT</a></h1>
      <img src="http://<?php echo $_SERVER['SERVER_NAME'] ?>/gv.php?l=30&sa=t">
      <div class="connections">
      <?php foreach (get_last_connections($db, 25) as $connection): ?>
      <a href="http://<?php echo urldecode($connection) ?>.theintor.net/r/g/"><?php echo urldecode($connection) ?></a>
      <?php endforeach; ?>
      </div>

      <?php endif; ?>
    </div>
  </body>
  <!-- part of Adam Benzan's Internet Conglomerate - http://blog.removablefeast.com/ http://cruciverbalizer.com/ adam[dot]benzan[at]gmail[dot]com-->
  <!-- Obfuscated URL at: http://_.theintor.net/<?php echo base64_encode($ma->db_record['id']); ?>  -->
</html>
