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
      <table>
        <thead>
          <th>Name</th>
          <th>Matches</th>
          <th>Description</th>
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
        </tr>
        <?php endforeach; ?>

      </table>
    </div>
  </body>
</html>
