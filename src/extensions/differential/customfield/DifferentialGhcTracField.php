<?php

final class DifferentialGhcTracField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:ghctrac';
  }

  public function getFieldName() {
    return pht('Trac');
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function getFieldDescription() {
    return pht('Reference to a GHC Trac issue.');
  }

  public function isFieldEnabled() {
    return true;
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

  public function getCommitMessageLabels() {
    return array(
      'Trac',
      'Trac Issue',
      'Trac Issues',
    );
  }

  public function renderPropertyViewValue(array $handles) {
    return null;
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
