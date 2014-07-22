<?php

/**
 * Extends Differential with a 'Trac Issues' field for GHC.
 */
final class DifferentialGhcTracField
  extends DifferentialStoredCustomField {

  private $error;

  public function getFieldKey() {
    return 'differential:ghc-trac';
  }

  public function getFieldName() {
    return pht('GHC Trac');
  }

  public function getFieldDescription() {
    return pht('Lists associated GHC Trac issues.');
  }

  public function canDisableField() {
    return true;
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function shouldAppearInEditView() {
    return true;
  }

  public function shouldAppearInCommitMessage() {
    return true;
  }

  public function shouldAllowEditInCommitMessage() {
    return true;
  }

  public function shouldAppearInConduitDictionary() {
    return true;
  }

  public function shouldOverwriteWhenCommitMessageIsEdited() {
    return true;
  }

  public function getCommitMessageLabels() {
    return array(
      'Trac',
      'Trac Issue',
      'Trac Issues',
    );
  }

  // Application transaction notifications
  public function shouldAppearInApplicationTransactions() {
    return true;
  }

  public function getOldValueForApplicationTransactions() {
    return array_unique(nonempty($this->getValue(), array()));
  }

  public function getNewValueForApplicationTransactions() {
    return array_unique(nonempty($this->getValue(), array()));
  }

  public function getApplicationTransactionTitle(
    PhabricatorApplicationTransaction $xaction) {
    $author_phid = $xaction->getAuthorPHID();
    $old = $xaction->getOldValue();
    $new = $xaction->getNewValue();

    return pht(
      '%s updated the Trac tickets for this revision.',
      $xaction->renderHandleLink($author_phid));
  }

  public function getApplicationTransactionTitleForFeed(
    PhabricatorApplicationTransaction $xaction,
    PhabricatorFeedStory $story) {

    $object_phid = $xaction->getObjectPHID();
    $author_phid = $xaction->getAuthorPHID();
    $old = $xaction->getOldValue();
    $new = $xaction->getNewValue();

    return pht(
      '%s updated the Trac tickets for %s.',
      $xaction->renderHandleLink($author_phid),
      $xaction->renderHandleLink($object_phid));
  }

  public function validateApplicationTransactions(
    PhabricatorApplicationTransactionEditor $editor,
    $type,
    array $xactions) {

    $this->error = null;
    $errors = parent::validateApplicationTransactions(
      $editor,
      $type,
      $xactions);

    foreach ($xactions as $xaction) {
      $old = $xaction->getOldValue();
      $new = $xaction->getNewValue();

      $add = array_diff($new, $old);
      if (!$add) {
        continue;
      }

      foreach ($new as $id) {
        if (!preg_match('/#(\d+)/', $id)) {
          $this->error = pht('Invalid');
          $errors[] = new PhabricatorApplicationTransactionValidationError(
            $type,
            pht('Invalid issue'),
            pht('References to trac tickets may only take the form `#XXXX` '.
                'where XXXX are decimal values'),
            $xaction);
        }
      }
    }

    return $errors;
  }

  // Storage handling
  public function readValueFromRequest(AphrontRequest $request) {
    $this->setValue($request->getStrList($this->getFieldKey()));
    return $this;
  }

  public function getValueForStorage() {
    return json_encode($this->getValue());
  }

  public function setValueFromStorage($value) {
    try {
      $this->setValue(phutil_json_decode($value));
    } catch (PhutilJSONParserException $ex) {
      $this->setValue(array());
    }
    return $this;
  }

  // Parse handling
  public function parseValueFromCommitMessage($value) {
    return preg_split('/[\s,]+/', $value, $limit = -1, PREG_SPLIT_NO_EMPTY);
  }

  public function readValueFromCommitMessage($value) {
    $this->setValue($value);
    return $this;
  }

  public function validateCommitMessageValue($value) {
    foreach ($value as $id) {
      if (!preg_match('/#(\d+)/', $id)) {
        throw new DifferentialFieldValidationException(
          pht("The ID '$id' is not in the form #<digits>."));
      }
    }
  }

  // Rendering, etc
  public function renderEditControl(array $handles) {
    return id(new AphrontFormTextControl())
      ->setLabel(pht('Trac Issues'))
      ->setCaption(
        pht('Example: %s', phutil_tag('tt', array(), '#7602, #2345')))
      ->setName($this->getFieldKey())
      ->setValue(implode(', ', nonempty($this->getValue(), array())))
      ->setError($this->error);
  }

  public function renderPropertyViewLabel() {
    return pht('Trac Issues');
  }

  public function renderCommitMessageValue(array $handles) {
    $value = $this->getValue();
    if (!$value) {
      return null;
    }
    return implode(', ', $value);
  }

  public function renderPropertyViewValue(array $handles) {
    $links = array();
    $match = null;

    // Don't show for non-GHC repositories.
    if (!$this->isGhcRepository()) {
      return null;
    }

    foreach ($this->getValue() as $ref) {
      if (!preg_match('/#(\d+)/', $ref, $match)) {
        $links[] = pht($ref);
      }
      else {
        $num = $match[1];
        $links[] = phutil_tag('a', array(
          'href' => 'https://ghc.haskell.org/trac/ghc/ticket/'.$num,
        ), $ref);
      }
    }

    // Return null if there aren't links, so we don't stick empty HTML into the
    // field causing it to always render.
    if (empty($links)) {
      return null;
    }
    else {
      return phutil_implode_html(phutil_tag('br'), $links);
    }
  }

  private function isGhcRepository() {
    $repo = $this->getObject()->getRepository();
    if ($repo === null) {
      return false;
    }
    return ($repo->getMonogram() === 'rGHC');
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
