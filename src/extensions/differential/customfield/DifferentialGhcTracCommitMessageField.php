<?php

/**
 * Extends Commit Messages with a 'Trac Issues' field for GHC.
 */
final class DifferentialGhcTracCommitMessageField
  extends DifferentialCommitMessageCustomField {

  private $error;

  const FIELDKEY = 'differential:ghc-trac';

  /* -- Core custom field descriptions -------------------------------------- */
  public function getFieldName() {
    // Rendered in 'Config > Differential > differential.fields'
    return pht('GHC Trac Issues');
  }

  public function getCustomFieldKey() {
    return 'differential:ghc-trac';
  }


  public function isFieldEditable() {
    return true;
  }

  public function readFieldValueFromObject(DifferentialRevision $revision) {
    if ($revision == null) {
      return [];
    }
    $custom_key = $this->getCustomFieldKey();
    $value1 = $this->readCustomFieldValue($revision, $custom_key);
    if ($value1 == 'null') {
        return [];
    }
    $value = phutil_json_decode($value1);
    if ($value == null) {
      return [];
    }
    return $value;
  }


  // Possible alternative labels
  public function getFieldAliases() {
    return array(
      'Trac',
      'Trac Issue',
      'Trac Issues',
      'GHC Trac',
      'GHC Trac Issue'
    );
  }

  /* -- Parsing commits ----------------------------------------------------- */
  public function parseFieldValue($value) {
    // return early if the user didn't provide anything
    if (!strlen($value)) {
      return [];
    }
    return preg_split('/[\s,]+/', $value, $limit = -1, PREG_SPLIT_NO_EMPTY);
  }

  public function validateCommitMessageValue(array $value) {
    printf("%s", $value);
    foreach ($value as $id) {
      if (!preg_match('/#(\d+)/', $id)) {
        throw new DifferentialFieldValidationException(
          pht('References to GHC Trac issues may only take the form '.
              '`#XYZ` where `XYZ` refers to an issue number.'));
      }
    }
  }

  public function renderFieldValue($values) {
    return implode(', ', $values);
  }

  public function readFieldValueFromConduit($value) {
    return $value;
  }

  public function isTemplateField() {
    return true;
  }

}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
