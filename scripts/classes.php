<?php

function randcolor() {
  $result = "";
  foreach (range(1,3) as $i) {
    $result .= base_convert(rand(17,255),10,16);
  }
  return $result;
}

abstract class Modifier {
  protected $subdomain;
  protected $fragment;
  protected $ereg = "";
  protected $opening_tag = "";
  protected $closing_tag = "";
  protected $css_additions = "";
  protected $help_text = "None yet";

  public function Modifier($fragment, $subdomain) {
    $this->fragment = $fragment;
    $this->subdomain = $subdomain;
  }

  public function modifyDb($db) { }

  public function getRegexp() {
    $translations = array('/' => '',
                          '\d+' => ' followed by numbers',
                          '[0-9a-zA-Z]{6}' => ' followed by 6 digit hexadecimal code',
                          '.*' => ' followed by any (non-slash) characters',
                          '^' => '',
                          '$' => '');
                          
    return str_replace(array_keys($translations), array_values($translations), $this->ereg);
  }

  public function getSample() {
    $translations = array('/' => '',
                          '\d+' => rand(1,80),
                          '.*' => 'hello-world',
                          '[0-9a-zA-Z]{6}' => randcolor(),
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
  public function isValid() {
    return preg_match($this->ereg, $this->fragment);
  }
  public function getParameters() {
    return array();
  }
}

class UnboldeningModifier extends Modifier {
  protected $ereg = "/^-b$/";
  protected $css_additions = ".phrase { font-weight:normal }";
  protected $help_text = "Switches to normal font-weight";
}

class OmgWhyModifier extends Modifier {
  protected $ereg = "/^fffuuuu$/";
  protected $help_text = "Really irritate viewer";
  protected $opening_tag = "<blink>";
  protected $closing_tag = "</blink>";
}

class MarqueeModifier extends Modifier {
  protected $ereg = "/^mq$/";
  protected $help_text = "Scrolling scrolling scrolling";
  protected $opening_tag = "<marquee>";
  protected $closing_tag = "</marquee>";

}

class ShowRelationshipsModifier extends Modifier {
  protected $ereg = "/^r$/";
  protected $help_text = "Show relationships to other URLs";

  public function getClosingTags() {
    $result = "";
    $db = get_db();
    $stmt = mysqli_prepare($db, "SELECT responder, target, last_reply_time FROM response_lookup WHERE responder = ? OR target = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $this->subdomain, $this->subdomain);

    mysqli_stmt_bind_result($stmt, $responder, $target, $last_reply_time);
    mysqli_stmt_execute($stmt);
    
    $result = '<ul class="relations">';
    while (mysqli_stmt_fetch($stmt)) {
      $result .= "<li><a href=\"http://$responder.theintor.net\">$responder</a> referred to <a href=\"http://$target.theintor.net\">$target</a><!-- on $last_reply_time --></li>";
    }
    $result .= "</ul>";

    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);  

    return $result;
  }
}

class RespondsToModifier extends Modifier {
  protected $ereg = "/^@.*$/";
  protected $help_text = "Associates this URL with another URL, the characters specified are exactly what comes before .theintor.net";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function modifyDb() {
    $db = get_db();
    $params = $this->getParameters();
    $target = $params[0];

    $stmt = mysqli_prepare($db, "REPLACE INTO response_lookup (responder, target, last_reply_time) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, 'ss', $this->subdomain, $target);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);  
    
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

class SizeModifier extends Modifier {
  protected $ereg = "/^s\d+/";
  protected $help_text = "Changes font size to numbers specified (in pixels)";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( ".phrase { font-size: %dpx }", $parameters[0] );
  }
}

class GlowModifier extends Modifier {
  protected $ereg = "/^g\d+$/";
  protected $opening_tag = '';
  protected $help_text = "Applies a glow effect with specific radius";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( ".phrase { text-shadow: #ff5 0px 0px %dpx }", $parameters[0] );
  }
}


class BGModifier extends Modifier {
  protected $ereg = "/^bg[0-9a-zA-Z]{6}$/";
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
  protected $ereg = "/^c[0-9a-zA-Z]{6}$/";
  protected $help_text = "Alters text color";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( "body { color: #%s }", $parameters[0] );
    
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

class BinaryModifier extends Modifier {
  protected $ereg = "/^1101$/";
  protected $help_text = "Converts text to binary representation of ascii values";
  
  public function getModifiedText($text) {
    $result = "";
    for ($i = 0; $i < strlen($text); $i++) {
      $result .= sprintf("%d %s", 
                         decbin(ord($text[$i])), 
                         $subdomain[$i] == " " ? "<br/>" : "");
    }
    return $result;
  }
}

class ModifierApplicator {
  private $valid_modifiers = array();
  public $css_additions = "";
  public $opening_tags = "";
  public $closing_tags = "";
  public $subdomain;
  public $raw_subdomain;

  public function getTitle() {
    return str_replace('-',' ',$raw_subdomain);
  }

  public function ModifierApplicator($raw_subdomain, $modifier_candidates, $request_string, $db) {
    $subdomain = str_replace('---','<br/>',$raw_subdomain);
    $subdomain = str_replace('--','&#8211;', $subdomain);
    $subdomain = str_replace('-',' ',$subdomain);

    $this->raw_subdomain = $raw_subdomain;
    $this->subdomain = $subdomain;

    $this->subdomain = $subdomain;
    $params = explode('/',$request_string);

    foreach ($params as $param) {
      foreach ($modifier_candidates as $modifier) {
        $test_modifier = new $modifier($param, $this->raw_subdomain);

        if ($test_modifier->isValid()) {
          $this->css_additions .= $test_modifier->getCssAdditions();
          $this->opening_tags .= $test_modifier->getOpeningTags();
          $this->closing_tags .= $test_modifier->getClosingTags();
          $this->subdomain = $test_modifier->getModifiedText($this->subdomain);
          $test_modifier->modifyDb($db, $this->raw_subdomain);
          array_push($this->valid_modifiers, $test_modifier);          
        }
      }
    }
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

$registered_modifiers = array('UnboldeningModifier',
                              'EmphasisModifier',
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
                              'BinaryModifier',
                              'OmgWhyModifier',
                              'OlTimeyModifier');


