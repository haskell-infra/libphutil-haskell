<?php

/**
 * Extends Differential with a 'Trac Issues' field for GHC.
 */
final class DifferentialGhcTracField
  extends DifferentialStoredCustomField {

  private $error;

  const FIELDKEY = 'differential:ghc-trac';

  /* -- Core custom field descriptions -------------------------------------- */
  public function getFieldKey() {
    return 'differential:ghc-trac';
  }

  public function getFieldName() {
    // Rendered in 'Config > Differential > differential.fields'
    return pht('GHC Trac Issues');
  }

  public function getFieldDescription() {
    // Rendered in 'Config > Differential > differential.fields'
    return pht('Lists associated GHC Trac issues.');
  }

  /* -- Field properties ---------------------------------------------------- */
  public function canDisableField() {
    // Rendered in 'Config > Differential > differential.fields'
    return true;
  }

  public function shouldAppearInListView() {
    return true;
  }

  public function shouldAppearInPropertyView() {
    return true; // NOTE: Only appears for 'rGHC', see below.
  }

  public function shouldAppearInEditView() {
    return true;
  }


  public function shouldAppearInConduitDictionary() {
    return true;
  }

  public function shouldOverwriteWhenCommitMessageIsEdited() {
    return true;
  }

  public function shouldAppearInConduitTransactions() {
    return true;
  }

  public function newConduitEditParameterType() {
    return new ConduitStringListParameterType();
  }

  // Rendered when you run 'arc diff'
  public function renderCommitMessageLabel() {
    return 'GHC Trac Issues';
  }

  // Rendered in the UI when viewing a revision
  public function renderPropertyViewLabel() {
    return pht('Trac Issues');
  }

  public function readFieldValueFromConduit(array $value) {
    return $value;
  }

  /* -- Transactions -------------------------------------------------------- */
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
    PhabricatorApplicationTransaction $xaction) {

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

      // Validate issue references
      foreach ($new as $id) {
        if (!preg_match('/#(\d+)/', $id)) {
          $this->error = pht('Invalid');
          $errors[] = new PhabricatorApplicationTransactionValidationError(
            $type,
            pht('Invalid issue reference'),
            pht('References to GHC Trac tickets may only take the form '.
                '`#XYZ` where `XYZ` refers to an issue number, but you '.
                'specified "%s".', $id),
            $xaction);
        }
      }
    }

    return $errors;
  }

  /* -- Storage ------------------------------------------------------------- */
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


  public function readValueFromCommitMessage($value) {
    $this->setValue($value);
    return $this;
  }

  /* -- Rendering ----------------------------------------------------------- */
  public function renderEditControl(array $handles) {
    return id(new AphrontFormTextControl())
      ->setLabel(pht('GHC Trac Issues'))
      ->setCaption(
        pht('Example: %s', phutil_tag('tt', array(), '#7602, #2345')))
      ->setName($this->getFieldKey())
      ->setValue(implode(', ', nonempty($this->getValue(), array())))
      ->setError($this->error);
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

  /* -- Private APIs -------------------------------------------------------- */
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
