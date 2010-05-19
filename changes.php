<!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/theintornet.css" />
    <title>Changes to The Intornet</title>
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
    <div id="changes_content">
    <h1>Changes to the Intornet - <a href="/about/">About</a> - <a href="/">Index</a></h1>
    <pre><?php 
    exec('git log --pretty=format:\'%ar, message: %s \' --graph ', $results);
    echo implode('<br/>', $results);
     ?>
    </pre>
    </div>
  </body>
</html>
