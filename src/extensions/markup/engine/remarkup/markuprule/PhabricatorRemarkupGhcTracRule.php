<?php

/**
 * Looks for references to GHC Trac issues and links to them as a Remarkup rule.
 */
final class PhabricatorRemarkupGhcTracRule
  extends PhabricatorRemarkupCustomInlineRule {

  public function getPriority() {
    return 200.0;
  }

  public function apply($text) {
    if ($this->getEngine()->isTextMode()) {
      return $text;
    }

    return preg_replace_callback(
      '/\B#(\d+)/',
      array($this, 'markupInlineTracLink'),
      $text);
  }

  public function markupInlineTracLink(array $matches) {

    $uri = 'https://ghc.haskell.org/trac/ghc/ticket/'.$matches[1];
    $ref = 'Trac #'.$matches[1];

    $link = phutil_tag('a', array(
      'href' => $uri,
      'style' => 'font-weight: bold;',
    ), $ref);

    $engine = $this->getEngine();
    return $engine->storeText($link);
  }

}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
