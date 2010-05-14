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
    <div id="about_content">
      <h1>The Intor.Net - A Helpful Guide</h1>
      <p>This is some automated documentation generated from the modifier classes for URLs.  As such, it might be a little weird.</p>
      <p>
        These modifiers are applied to the URLs by following the domain with any number of these thiengs, separated with slashes.  So if we wanted a message of size 150 that was both uppercased and bolded, the url would be:<br/> <a href="http://sample-message.theintor.net/b/uc/s150">http://sample-message.theintor.net/b/uc/s150</a>.
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
