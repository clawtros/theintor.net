<?php

$registered_modifiers = array('UnboldeningModifier',
                              'MessageModifier',
                              'EmphasisModifier',
                              'GraphVizModifier',
                              'MatrixModifier',
                              'DunDunModifier',
                              'FadeInModifier',
                              'UppercaseModifier',
                              'NoMarginModifier',
                              'BGModifier',
                              'ShowRelationshipsModifier',
                              'FGModifier',
                              'SizeModifier',
                              'MarqueeModifier',
                              'RespondsToModifier',
                              'SerifModifier',
                              'CodifyModifier',
                              'GlowModifier',
                              'ShadowModifier',
                              'RemoveResponseModifier',
                              'BinaryModifier',
                              'OmgWhyModifier',
                              'Rot13Modifier',
                              'TranslatePunctuationModifier',
                              'ApproachModifier',
                              'CustomShadowModifier',
                              'TypeModifier',
                              'CssBackgroundGradientModifier',
                              'LetterGradientModifier',
                              'GoogleFontModifier',
                              'TitleCaseModifier',
                              'RotationModifier',
                              'OlTimeyModifier');

function randcolor() {
  $result = "";
  foreach (range(1,3) as $i) {
    $result .= base_convert(rand(17,255),10,16);
  }
  return $result;
}

abstract class Modifier {
  protected $modifies_display=true;
  protected $order = 0;
  protected $subdomain;
  protected $fragment;
  protected $ereg = "";
  protected $opening_tag = "";
  protected $closing_tag = "";
  protected $css_additions = "";
  protected $help_text = "None yet";
  protected $js_includes = array();
  protected $header_additions = "";

  public function Modifier($fragment, $subdomain) {
    $this->fragment = $fragment;
    $this->subdomain = $subdomain;
  }

  public function modifiesDisplay() {
    return $this->modifies_display;
  }

  public function getPreOpeningTags() {}

  public function getPostClosingTags() {}

  public function getJsIncludes() {
    return $this->js_includes;
  }

  public function getOrder() {
    return $this->order;
  }

  public function getHeaderAdditions() {
    return $this->header_additions;
  }

  public function modifyDb($db) { }

  public function getRegexp() {
    $translations = array('/' => '',
                          '\d+' => ' followed by numbers',
                          '[0-9a-fA-F]{6}' => ' followed by 6 digit hexadecimal code',
                          '.*' => ' followed by any (non-slash) characters',
                          '^' => '',
                          '$' => '');
                          
    return str_replace(array_keys($translations), array_values($translations), $this->ereg);
  }

  public function getSample() {
    $translations = array('/' => '',
                          '\d+' => rand(1,80),
                          '.*' => 'hello-world',
                          '[0-9a-fA-F]{6}' => randcolor(),
                          '^' => '',
                          '$' => '');
                          
    return "http://sample-message.theintor.net/".str_replace(array_keys($translations), array_values($translations), $this->ereg);
  }

  public function getHelpText() {
    return $this->help_text;
  }
  public function getOpeningTags() {
    return $this->opening_tag;
  }
  public function getClosingTags() {
    return $this->closing_tag;
  }
  public function getCssAdditions() {
    return $this->css_additions;
  }
  public function getModifiedText($text) {
    return $text;
  }
  public function getFragment() {
    return $this->fragment;
  }
  
  public function isValid() {
    return preg_match($this->ereg, $this->fragment);
  }
  public function getParameters() {
    $result = array();
    preg_match_all($this->ereg, $this->fragment, $result);
    return $result;
  }
}

class UnboldeningModifier extends Modifier {
  protected $ereg = "/^-b$/";
  protected $css_additions = ".phrase { font-weight:normal }";
  protected $help_text = "Switches to normal font-weight";
}

class MessageModifier extends Modifier {
  protected $ereg = "/^q(.*)/";
  protected $help_text = "";
  protected $_db = NULL;
  protected $modifies_display = true;

  public function modifyDb($db, $sd) {
    $this->_db = $db;
  }

  public function getModifiedText($text) {
    global $registered_modifiers;

    $parameters = $this->getParameters();

    $rs = explode('/',$_SERVER['REQUEST_URI']);
    $match_index = 0;
    foreach ($rs as $r) {
      $match_index++;
      if (preg_match(str_replace('(.*)',$parameters[1][0], $this->ereg), $r)) {
        break;
      } 
    }
    $rs = implode('/',array_slice($rs, $match_index));
    $ma = new ModifierApplicator(strip_tags(urldecode($parameters[1][0])), $registered_modifiers, $rs, $this->_db);
    return $ma->getModifiedSubdomain();
  }

}

class TranslatePunctuationModifier extends Modifier {
  protected $ereg = "/^p$/";
  protected $help_text = "Translates certain strings in urls to punctuation (period, bang, questionmark, interrobang, omgomg)";
  public function getModifiedText($text) {
    $translations = array('period'=>'.',
      'interrobang'=>'&#8253;',
      '_'=>'&rsquo;',
      'bang'=>'!',
      'comma'=>',',
      'questionmark'=>'?',
      'omgomg'=>'!!!!!');
    return str_replace(array_keys($translations), array_values($translations), $text);
  }
    public function getSample() {
        return "http://sample_s-messagebang-interrobang-questionmarkomgomg.theintor.net/p";
    }
}

class OmgWhyModifier extends Modifier {
  protected $ereg = "/^fffuuuu$/";
  protected $help_text = "Really irritate viewer";
  protected $opening_tag = "<blink>";
  protected $closing_tag = "</blink>";
}

class CssBackgroundGradientModifier extends Modifier {
  protected $ereg = "/^bg[0-9a-fA-F]{6},[0-9a-fA-F]{6}$/";

  public function getParameters() {
    return explode(',',substr($this->fragment, 2));
  }

  public function getSample() {
    return "http://sample-message.theintor.net/bg".randcolor().",".randcolor();
  }

  public function getCssAdditions() {
    $params = $this->getParameters();
    return sprintf("
body {
background: -webkit-gradient(
    linear,
    left top,
    left bottom,
    from(#%s),
    to(#%s)
);
background: -moz-linear-gradient( top,#%s,#%s );}
", $params[0], $params[1], $params[0], $params[1]);
  }
}



class Rot13Modifier extends Modifier {
  protected $ereg = "/^r13$/";
  protected $help_text = "Applies an ROT13 filter.  Note: using this filter an even number of times does nothing.";
  public function getModifiedText($text) {
    return str_rot13($text);
  }
}

class MarqueeModifier extends Modifier {
  protected $ereg = "/^mq$/";
  protected $help_text = "Scrolling scrolling scrolling";
  protected $opening_tag = "<marquee>";
  protected $closing_tag = "</marquee>";

}


class DunDunModifier extends Modifier {
  protected $ereg = "/^dundun$/";
  protected $help_text = "Dun dun";
  protected $modifies_display = false;

  public function getPostClosingTags() {
    return '<object width="0" height="0">
<param name="movie" value="/doinkdoink.swf">
<embed src="/doinkdoink.swf" width="0" height="0">
</embed>
</object>';
  }
}

class ShowRelationshipsModifier extends Modifier {
  protected $ereg = "/^r$/";
  protected $help_text = "Show relationships to other URLs";
  protected $modifies_display = false;

  public function getPostClosingTags() {
    $result = "";
    $db = get_db();
    $stmt = mysqli_prepare($db, "SELECT responder, target, last_reply_time FROM response_lookup WHERE responder = ? OR target = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $this->subdomain, $this->subdomain);

    mysqli_stmt_bind_result($stmt, $responder, $target, $last_reply_time);
    mysqli_stmt_execute($stmt);
    
    $result = '<ul class="relations">';
    while (mysqli_stmt_fetch($stmt)) {
      $target = urldecode($target);
      $result .= "<li><a href=\"http://$responder.theintor.net/g/r/\">$responder</a> referred to <a href=\"http://$target.theintor.net/g/r/\">$target</a><!-- on $last_reply_time --></li>";
    }
    $result .= "</ul>";

    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);  

    return $result;
  }
}

class GraphVizModifier extends Modifier {
  protected $ereg = "/^g$/";
  protected $help_text = "Renders a graph of relationships";
  protected $modifies_display = false;

  public function getPostClosingTags() {
    return '<img src="http://'.$_SERVER['SERVER_NAME'].'/gv.php?l=13" alt="graph viz" class="graphviz_image" />';
  }
}

class RemoveResponseModifier extends Modifier {

  protected $modifies_display = false;
  protected $ereg = "/^-@.*$/";
  protected $help_text = "Removes a URL association";

  public function getParameters() {
    return array(substr($this->fragment, 2));
  }

  public function modifyDb() {
    $db = get_db();
    $params = $this->getParameters();
    $target = $params[0];

    $stmt = mysqli_prepare($db, "delete from response_lookup where responder=? and target=?");
    mysqli_stmt_bind_param($stmt, 'ss', $this->subdomain, $target);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);  
    
  }
}

class RespondsToModifier extends Modifier {
  protected $modifies_display = false;
  protected $ereg = "/^@.*$/";
  protected $help_text = "Associates this URL with another URL, the characters specified are exactly what comes before .theintor.net";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function modifyDb() {
    $db = get_db();
    $params = $this->getParameters();
    $target = utf8_encode(preg_replace('/[\:\&\<\>\[\]\+\(\)\\\'\"\;\/\?\*]/','',strtolower($params[0])));
    if ($target) {
        $stmt = mysqli_prepare($db, "REPLACE INTO response_lookup (responder, target, last_reply_time) VALUES (?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, 'ss', $this->subdomain, $target);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_free_result($stmt);
        mysqli_stmt_close($stmt);
    }
  }
}

class NoMarginModifier extends Modifier {
  protected $ereg = "/^nm$/";
  protected $help_text = "Kills upper margin";
  protected $css_additions = ".phrase { margin-top:0px; } ";
}

class UppercaseModifier extends Modifier {
  protected $ereg = "/^uc$/";
  protected $help_text = "Converts to UPPERCASE";
  public function getModifiedText($text) {
    return strtoupper($text);
  }
}



class RotationModifier extends Modifier {
  protected $ereg = "/^ro\d+/";
  protected $help_text = "rotates";
  public function getParameters() {
    return array(substr($this->fragment, 2));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( "
#phrase { 
-moz-transform:rotate(%ddeg);
-webkit-transform:rotate(%ddeg); }
", $parameters[0], $parameters[0]  );
  }
}

class SizeModifier extends Modifier {
  protected $ereg = "/^s\d+/";
  protected $help_text = "Changes font size to numbers specified (in pixels)";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( ".phrase { font-size: %dpx; }", $parameters[0] );
  }
}

class GlowModifier extends Modifier {
  protected $ereg = "/^g\d+$/";
  protected $opening_tag = '';
  protected $help_text = "Applies a glow effect with specified number as pixel radius";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( ".phrase { text-shadow: #ff5 0px 0px %dpx }", $parameters[0] );
  }
}


class BGModifier extends Modifier {
  protected $ereg = "/^bg[0-9a-fA-F]{6}$/";
  protected $help_text = "Alters background-color";
  public function getParameters() {
    return array(substr($this->fragment, 2));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( "body { background-color: #%s }", $parameters[0] );
    
  }
}


class FGModifier extends Modifier {
  protected $ereg = "/^c[0-9a-fA-F]{6}$/";
  protected $help_text = "Alters text color";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( "body { color: #%s }", $parameters[0] );
    
  }
}


class B64Modifier extends Modifier {
  protected $ereg = "/^b64$/";
  protected $modifies_display = false;
  public function getModifiedText($text) {
    return base64_encode($text);
  }
}

class MatrixModifier extends Modifier {
  protected $ereg = "/^mx$/";
  protected $opening_tag = '';

  public function getCssAdditions() {
    return "body { font-family: courier, monospace; background-color: #000; color:#0f0 } #phrase { font-weight: normal; text-shadow:#af6 0px 0px 10px; } ";
  }
}

class ShadowModifier extends Modifier {
  protected $ereg = "/^sh$/";
  protected $opening_tag = '';
  protected $help_text = "Applies a drop shadow";

  public function getCssAdditions() {
    return ".phrase { text-shadow: #ddd 10px 10px 0px }";
  }
}

class CustomShadowModifier extends Modifier {
  protected $ereg = "/^sh[0-9a-fA-F]{6}$/";
  protected $opening_tag = '';
  protected $help_text = "Applies a drop shadow with custom colour";
  

  public function getParameters() {
    return array(substr($this->fragment, 2));
  }
  
  public function getCssAdditions() {
    $params = $this->getParameters();
    return ".phrase { text-shadow: #".$params[0]." 10px 10px 0px }";
  }
}

class EmphasisModifier extends Modifier {
  protected $ereg = "/^i$/";
  protected $opening_tag = "<em>";
  protected $closing_tag = "</em>";
  protected $help_text = "Applies the &lt;em&gt; tag";
}

class OlTimeyModifier extends Modifier {
  protected $ereg = "/^oltimey$/";
  protected $help_text = "Like a silent movie card";
  protected $css_additions = "body { background-color: #000; color: #fff; border: 3px double #fff; height: 95%; } .phrase { margin-bottom: 20% }";
}

class SerifModifier extends Modifier {
  protected $ereg = "/^srf/";
  protected $help_text = "Switches to Serif font families";
  protected $css_additions = "body { font-family: Georgia, Times, serif}";
}

class CodifyModifier extends Modifier {
  protected $ereg = "/^ascii$/";
  protected $help_text = "Converts text to ascii representation";
  public function getModifiedText($text) {
    $result = "";
    for ($i = 0; $i < strlen($text); $i++) {
      $result .= sprintf("%d ", ord($text[$i]));
    }
    return $result;
  }
}

function split_channels($hex_color) {
  $r = base_convert(substr($hex_color, 0, 2), 16, 10);
  $g = base_convert(substr($hex_color, 2, 2), 16, 10);
  $b = base_convert(substr($hex_color, 4, 2), 16, 10);
  
  return array($r, $g, $b);
}

function join_channels($channels) {
  $result = "";
  foreach ($channels as $channel) {
    $hex_channel = base_convert($channel, 10, 16);
    if (strlen($hex_channel) == 1) $result .= "0";
    $result .= $hex_channel;
  }
  return $result;
}

function string_to_entities($text) {
  $entities = array();
  $storage = "";
  for ($i = 0; $i < strlen($text); $i++) {
    $chr = $text[$i];
    if (!preg_match("/[\<\&]/",$chr) && strlen($storage) == 0) {
      array_push($entities, $chr);
    } else {
      $storage .= $chr;
      if (preg_match("/[\>\;]/", $chr)) {
        array_push($entities, $storage);
        $storage = "";
      }
    }
  }
  
  return $entities;
  
}

class LetterGradientModifier extends Modifier {
  protected $ereg = "/^lg[0-9a-fA-F]{6},[0-9a-fA-F]{6}$/";
  protected $help_text = "Per-letter gradient using spans that might break everything";

  public function getSample() {
    return "http://sample-message.theintor.net/lg".randcolor().",".randcolor();
  }

  public function getParameters() {
    return explode(',',substr($this->fragment, 2));
  }
  
  public function getModifiedText($text) {
    $text = htmlspecialchars_decode($text);
    $entities = string_to_entities($text);
    $textlength = sizeof($entities);
    
    
    list($from, $to) = $this->getParameters();
    list($from_r, $from_g, $from_b) = split_channels($from);
    list($to_r, $to_g, $to_b) = split_channels($to);
    
    $dr = ($from_r - $to_r) / $textlength;
    $dg = ($from_g - $to_g) / $textlength;
    $db = ($from_b - $to_b) / $textlength;

    $result = "";
    $i = 0;
    foreach ($entities as $entity) {
      $i++;
      $result .= preg_match("/^\</", $entity) ? 
        $entity :
        sprintf("<span style=\"color:rgb(%d,%d,%d)\">%s</span>", 
                $from_r - $dr*$i, $from_g - $dg*$i, $from_b - $db*$i, $entity);
    }

    return $result;
  }

}

class GoogleFontModifier extends Modifier {
  protected $ereg = "/^f\d+$/";
  public $_fontNames = array('Cantarell', 
                             'Crimson Text', 
                             'Droid Sans', 
                             'Droid Sans Mono', 
                             'Droid Serif', 'IM Fell DW Pica', 'Inconsolata', 'Josefin Sans Std Light', 'Lobster', 'Molengo', 'Nobile', 'OFL Sorts Mill Goudy TT', 'Old Standard TT', 'Reenie Beanie', 'Tangerine', 'Vollkorn', 'Yanone Kaffeesatz');

  public function getHelpText() {
    $result = "Switches body font to one from the google webfonts API:<small>  ";
    foreach ($this->_fontNames as $id=>$font) {
      $result .= $id.':'.$font.', ';
    }
    $result = substr($result, 0, -1);
    $result .= "</small>";
    return $result;
  }

  public function getSample() {    
    return "http://the-quick-brown-fox-jumped-over-the-lazy-dog.theintor.net/f".rand(0,sizeof($this->_fontNames)-1);
  }

  public function getParameters() {
    $id = (int)substr($this->fragment, 1);
    if ($id > sizeof($this->_fontNames)) {
      $font = 'Tangerine';
    } else {
      $font = $this->_fontNames[$id];
    }
    return array($font);
  }

  public function getHeaderAdditions() {
    $params = $this->getParameters();
    return "<link href='http://fonts.googleapis.com/css?family=".$params[0]."' rel='stylesheet' type='text/css'>";
  }

  public function getCssAdditions() {
    $params = $this->getParameters();
    return 'body { font-family: "'.$params[0].'" }';
  }

}


class TitleCaseModifier extends Modifier {

  protected $ereg = "/^tc$/";
  protected $help_text = "Title-cases text";
  
  public function getModifiedText($text) {
    return mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
  }
}

class BinaryModifier extends Modifier {

  protected $ereg = "/^1101$/";
  protected $help_text = "Converts text to binary representation of ascii values";
  
  public function getModifiedText($text) {
    $result = "";
    for ($i = 0; $i < strlen($text); $i++) {
      $result .= sprintf("%d %s", 
                         decbin(ord($text[$i])), 
                         $text[$i] == " " ? "<br/>" : "");
    }
    return $result;
  }
}

class FadeInModifier extends Modifier {
  protected $ereg = "/^fi$/";
  protected $js_includes = array('jquery', 'fadein');
}

class ApproachModifier extends Modifier {
  protected $ereg = "/^a$/";
  protected $js_includes = array('jquery', 'jquery.approach','apply-approach');
}

class TypeModifier extends Modifier {
  protected $ereg = "/^t$/";
  protected $js_includes = array('jquery', 'type');
}


class ModifierApplicator {
  private $valid_modifiers = array();
  public $css_additions = "";
  public $opening_tags = "";
  public $closing_tags = "";
  public $subdomain;
  public $raw_subdomain;
  public $db_record = array();
  public $post_closing_html;
  public $js_includes = array(); 
  public $pre_opening_html = "";
  private $header_additions = "";
  private $_db;

  public function getTitle() {
    return str_replace('-',' ',$raw_subdomain);
  }

  public function ModifierApplicator($raw_subdomain, $modifier_candidates, $request_string, $db) {
    $this->_db = $db;
        
    $subdomain = str_replace('---','<br/>',$raw_subdomain);
    $subdomain = str_replace('--','&#8211;', $subdomain);
    $subdomain = str_replace('-',' ',$subdomain);

    $this->raw_subdomain = $raw_subdomain;
    $this->subdomain = $subdomain;
    $this->db_record = fetch_subdomain($this->_db, $raw_subdomain);
    $this->valid_modifiers = $this->getModifiersFromRequestUri($request_string, $modifier_candidates);

    if ($this->useDefaultParams()) {

      
      if ($this->db_record) {
        $this->valid_modifiers = array_merge($this->valid_modifiers, 
                                             $this->getModifiersFromRequestUri($this->db_record['request_uri'], $modifier_candidates, true));
      }

    } else {
      $display_uri = $this->getDisplayUri();
      if ($display_uri) update_subdomain($db, $raw_subdomain, $display_uri);
    }

  }

  public function getHeaderAdditions() {
    return $this->header_additions;
  }

  public function getDisplayUri() {
    $result = array();
    foreach ($this->valid_modifiers as $modifier) {
      if ($modifier->modifiesDisplay()) $result["/".$modifier->getFragment()] = true;
    }
    return implode("",array_keys($result));
  }

  public function getNonDisplayUri() {
    $result = "";
    foreach ($this->valid_modifiers as $modifier) {
      if (!$modifier->modifiesDisplay()) $result .= "/".$modifier->getFragment();
    }
    return $result;
  }

  private function getModifiersFromRequestUri($request_string, $modifier_candidates, $display_only = false) {
    $params = explode('/',$request_string);
    $valid_modifiers = array();

    foreach ($params as $param) {
      foreach ($modifier_candidates as $modifier) {
        $test_modifier = new $modifier($param, $this->raw_subdomain);

        if ($test_modifier->isValid() && (($display_only && $test_modifier->modifiesDisplay()) || !$display_only)) {
          $test_modifier->modifyDb($this->_db, $this->raw_subdomain);
          $this->mergeJsIncludes($test_modifier->getJsIncludes());
          $this->header_additions .= $test_modifier->getHeaderAdditions();
          $this->pre_opening_html .= $test_modifier->getPreOpeningTags();
          $this->post_closing_html .= $test_modifier->getPostClosingTags();
          $this->css_additions .= $test_modifier->getCssAdditions();
          $this->opening_tags .= $test_modifier->getOpeningTags();
          $this->closing_tags .= $test_modifier->getClosingTags();
          $this->subdomain = $test_modifier->getModifiedText($this->subdomain);

          array_push($valid_modifiers, $test_modifier);          

        }
      }
    }
    
    return $valid_modifiers;
  }  

  public function mergeJsIncludes($includes) {
    foreach ($includes as $js) {
      $this->js_includes[$js] = true;
    }
  }

  public function getJsIncludes() {
    return array_keys($this->js_includes);
  }

  public function useDefaultParams() {
    foreach ($this->valid_modifiers as $modifier) {
      if ($modifier->modifiesDisplay()) return false;
    }
    
    return true;
  }
  
  public function getModifiedSubdomain() {
    return $this->opening_tags.$this->subdomain.$this->closing_tags;
  }

}

class HelpGenerator {
  private $registered_modifiers;
  public function HelpGenerator($modifier_candidates) {
    foreach ($modifier_candidates as $modifier) {
      $this->registered_modifiers[$modifier] = new $modifier("","sample-domain");
    }
  }
  
  public function getAllHelp() {
    $results = array();
    ksort($this->registered_modifiers);
    foreach ($this->registered_modifiers as $modifier_name => $modifier) {
      $result = new stdClass();
      $result->name = str_replace("Modifier", "", $modifier_name);
      $result->matches = $modifier->getRegexp();
      $result->description = $modifier->getHelpText();
      $result->sample = $modifier->getSample();
      array_push($results, $result);
    }

    return $results;
  }
}



