<?php

abstract class Modifier {
  protected $fragment;
  protected $ereg = "";
  protected $opening_tag = "";
  protected $closing_tag = "";
  protected $css_additions = "";

  public function Modifier($fragment) {
    $this->fragment = $fragment;
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
  protected $opening_tag = "<b>";
  protected $closing_tag = "</b>";
}

class EmbiggeningModifier extends Modifier {
  protected $ereg = "/^s_$/";
  protected $opening_tag = "<em>";
  protected $closing_tag = "</em>";
}


class UppercaseModifier extends Modifier {
  protected $ereg = "/^uc$/";
  public function getModifiedText($subdomain) {
    return strtoupper($subdomain);
  }
}

class SizeModifier extends Modifier {
  protected $ereg = "/^s\d+/";
  public function getParameters() {
    return array(substr($this->fragment, 1));
  }

  public function getCssAdditions() {
    $parameters = $this->getParameters();
    return sprintf( ".phrase { font-size: %dpx }", $parameters[0] );
  }
}

class EmphasisModifier extends Modifier {
  protected $ereg = "/^i$/";
  protected $opening_tag = "<em>";
  protected $closing_tag = "</em>";
}

class OlTimeyModifier extends Modifier {
  protected $ereg = "/^oltimey$/";
  protected $css_additions = "body { background-color: #000; color: #fff; border: 3px double #fff; height: 100%; } .phrase { margin-bottom: 20%}";
}

class CodifyModifier extends Modifier {
  protected $ereg = "/^ascii$/";
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
  public function getModifiedText($subdomain) {
    $result = "";
    for ($i = 0; $i < strlen($subdomain); $i++) {
      $result .= sprintf("%d ", decbin(ord($subdomain[$i])));
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

$registered_modifiers = array('EmboldeningModifier',
                              'EmbiggeningModifier',
                              'EmphasisModifier',
                              'UppercaseModifier',
                              'SizeModifier',
                              'CodifyModifier',
                              'BinaryModifier',
                              'OlTimeyModifier');


