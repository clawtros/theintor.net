<?php

function randcolor() {
  $result = "";
  foreach (range(1,3) as $i) {
    $result .= base_convert(rand(17,255),10,16);
  }
  return $result;
}

abstract class Modifier {
  protected $fragment;
  protected $ereg = "";
  protected $opening_tag = "";
  protected $closing_tag = "";
  protected $css_additions = "";
  protected $help_text = "None yet";

  public function Modifier($fragment) {
    $this->fragment = $fragment;
  }
  public function getRegexp() {
    $translations = array('/' => '',
                          '\d+' => ' followed by numbers',
                          '[0-9a-zA-Z]{6}' => ' followed by 6 digit hexadecimal code',
                          '^' => '',
                          '$' => '');
                          
    return str_replace(array_keys($translations), array_values($translations), $this->ereg);
  }

  public function getSample() {
    $translations = array('/' => '',
                          '\d+' => rand(1,80),
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

class EmboldeningModifier extends Modifier {
  protected $ereg = "/^b$/";
  protected $opening_tag = "<strong>";
  protected $closing_tag = "</strong>";
  protected $help_text = "Applies the &lt;strong&gt; tag";
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


class NoMarginModifier extends Modifier {
  protected $ereg = "/^nm$/";
  protected $help_text = "Kills upper margin";
  protected $css_additions = ".phrase { margin-top:0px; } ";

}


class UppercaseModifier extends Modifier {
  protected $ereg = "/^uc$/";
  protected $help_text = "Converts to UPPERCASE";
  public function getModifiedText($subdomain) {
    return strtoupper($subdomain);
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
    //$parameters = $this->getParameters();
    //return sprintf( ".phrase { text-shadow: #ccc 10px 10px 0px }", $parameters[0] );
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

class CodifyModifier extends Modifier {
  protected $ereg = "/^ascii$/";
  protected $help_text = "Converts text to ascii representation";
  public function getModifiedText($subdomain) {
    $result = "";
    for ($i = 0; $i < strlen($subdomain); $i++) {
      $result .= sprintf("%d ", ord($subdomain[$i]));
    }
    return $result;
  }
}

class BinaryModifier extends Modifier {
  protected $ereg = "/^1101$/";
  protected $help_text = "Converts text to binary representation of ascii values";
  
  public function getModifiedText($subdomain) {
    $result = "";
    for ($i = 0; $i < strlen($subdomain); $i++) {
      $result .= sprintf("%d %s", 
                         decbin(ord($subdomain[$i])), 
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

  public function ModifierApplicator($subdomain, $modifier_candidates, $request_string) {
    $this->subdomain = $subdomain;
    $params = explode('/',$request_string);

    foreach ($params as $param) {
      foreach ($modifier_candidates as $modifier) {
        $test_modifier = new $modifier($param);

        if ($test_modifier->isValid()) {
          $this->css_additions .= $test_modifier->getCssAdditions();
          $this->opening_tags .= $test_modifier->getOpeningTags();
          $this->closing_tags .= $test_modifier->getClosingTags();
          $this->subdomain = $test_modifier->getModifiedText($this->subdomain);
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
      $this->registered_modifiers[$modifier] = new $modifier("");
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

$registered_modifiers = array('EmboldeningModifier',
                              'EmphasisModifier',
                              'UppercaseModifier',
                              'NoMarginModifier',
                              'BGModifier',
                              'FGModifier',
                              'SizeModifier',
                              'MarqueeModifier',
                              'CodifyModifier',
                              'GlowModifier',
                              'ShadowModifier',
                              'BinaryModifier',
                              'OmgWhyModifier',
                              'OlTimeyModifier');


