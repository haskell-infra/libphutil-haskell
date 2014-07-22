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

    return $this->replaceHTML(
      '@#([1-9]\d*)@s',
      array($this, 'applyCallback'),
      $text);
  }

  protected function applyCallback($matches) {
    return hsprintf(
      '<strong>#%s</strong>',
      $matches[1]);
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
