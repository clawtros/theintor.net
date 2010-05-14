<?php 
ini_set('display_errors', 'On');     
include( 'scripts/funcs.php' );
include( 'scripts/classes.php' );
$help = new HelpGenerator($registered_modifiers);
   ?><!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/theintornet.css" />
    <title>About The Intornet</title>
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
    <div id="about_content">
      <h1>The Intor.Net - A Helpful Guide</h1>
      <p>This is some automated documentation generated from the modifier classes for URLs.  As such, it might be a little weird.</p>
      <p>
        These modifiers are applied to the URLs by following the domain with any number of these things, separated with slashes.  So if we wanted a message of size 150 that was both uppercased and bolded, the url would be:<br/> <a href="http://sample-message.theintor.net/b/uc/s150">http://sample-message.theintor.net/b/uc/s150</a>.
      </p>
      <p>
        One dash in a URL becomes a space, two become a dash and three become a line break.  Here's a sample of some line breaks and text formatting:<br/>
        <a href="http://keep-calm---and---carry-on.theintor.net/uc/bgff0000/cffffff">http://keep-calm---and---carry-on.theintor.net/uc/bgff0000/cffffff</a>
      </p>
      <table>
        <thead>
          <th style="width:150px">Name</th>
          <th style="width:200px">Matches</th>
          <th>Description</th>
          <th style="width:300px;">Sample</th>
        </thead>
        <?php foreach ($help->getAllHelp() as $modifier_help): ?>
        <tr <?php echo ++$count % 2 == 0 ? 'class="alt"' : '' ?>>
          <td>
            <?php echo $modifier_help->name ?>
          </td>
          <td>
            <?php echo $modifier_help->matches ?>
          </td>
          <td>
            <?php echo $modifier_help->description ?>
          </td>
          <td>
            <a href="<?php echo $modifier_help->sample ?>"><?php echo $modifier_help->sample ?></a>
          </td>

        </tr>
        <?php endforeach; ?>

      </table>
    </div>
  </body>
</html>
