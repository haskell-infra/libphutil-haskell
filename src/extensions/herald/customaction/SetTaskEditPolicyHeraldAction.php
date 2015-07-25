<?php

/**
 * Extends Herald with a custom 'Set "Editable By" policy' action for Maniphest
 * tasks.
 *
 * Primarily useful for email/support related addresses to be assigned to
 * private projects.
 */
//final class SetTaskEditPolicyHeraldAction extends HeraldAction {
//
//  public function appliesToAdapter(HeraldAdapter $adapter) {
//    return $adapter instanceof HeraldManiphestTaskAdapter;
//  }
//
//  public function appliesToRuleType($type) {
//    return $type == HeraldRuleTypeConfig::RULE_TYPE_GLOBAL;
//  }
//
//  public function getActionKey() {
//    return 'custom:edit-policy';
//  }
//
//  public function getActionName() {
//    return 'Set edit policy to project';
//  }
//
//  public function getActionType() {
//    return HeraldAdapter::VALUE_PROJECT;
//  }
//
//  public function applyEffect(
//    HeraldAdapter $adapter,
//    $object,
//    HeraldEffect $effect) {
//
//    // First off, ensure there's only one set project
//    if (count($effect->getTarget()) != 1) {
//      throw new HeraldInvalidConditionException(
//        'Expected only one project to be set for editability policy');
//    }
//
//    $project = $effect->getTarget();
//    $project_phid = $project[0];
//
//    // Set new value by queueing a transaction, and returning the transcript.
//    $adapter->queueTransaction(
//      id(new ManiphestTransaction())
//      ->setTransactionType(PhabricatorTransactions::TYPE_EDIT_POLICY)
//      ->setNewValue($project_phid));
//
//    return new HeraldApplyTranscript(
//      $effect,
//      true,
//      pht('Set edit policy of task'));
//  }
//
//}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
