<?php

final class DifferentialGhcTracField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:ghctrac';
  }

  public function getFieldName() {
    return pht('Trac');
  }

  public function getFieldDescription() {
    return pht('Reference to a GHC Trac issue.');
  }

  public function shouldAppearInCommitMessage() {
    return true;
  }

  public function shouldAllowEditInCommitMessage() {
    return true;
  }

  public function canDisableField() {
    return true;
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function renderPropertyViewValue(array $handles) {
    $host = $this->getObject()->getActiveDiff()->getSourceMachine();
    if (!$host) {
      return null;
    }

    return $host;
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
