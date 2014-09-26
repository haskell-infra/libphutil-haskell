<?php

/**
 * Takes messages of the form:
 *   <bot name>: log I did a thing
 * and logs it to the Phriction page:
 *   w/projects/haskell.org_infrastructure/server_admin_log/
 */
final class PhabricatorBotServerAdminLogHandler extends PhabricatorBotHandler {

  /**
   * TODO FIXME: Need to un-hardcode the bot name.
   */
  private $botName   = 'phaskell';
  private $wikiTitle = 'Server Admin Log';
  private $wikiSlug  = 'projects/haskell.org_infrastructure/server_admin_log';
  private $numLogs   = 3;

  private function getWikiPageContent() {
    $res = $this->getConduit()->callMethodSynchronous(
      'phriction.info',
      array(
        'slug' => $this->wikiSlug,
      ));
    return $res['content'];
  }

  private function editWikiPage($content) {
    $this->getConduit()->callMethodSynchronous(
      'phriction.edit',
      array(
        'slug'        => $this->wikiSlug,
        'title'       => $this->wikiTitle,
        'description' => 'Automatic addition via IRC (from phaskell)',
        'content'     => $content,
      ));
  }

  public function receiveMessage(PhabricatorBotMessage $message) {
    switch ($message->getCommand()) {
      case 'MESSAGE':
        $target_name = $message->getTarget()->getName();
        $text        = $message->getBody();

        if ($target_name !== '#haskell-infrastructure') {
          // Don't do this in non-infra channels, as it's probably annoying.
          break;
        }

        /* -- Case #1: Check for 'log "foobar"' and add it. -- */
        $matches = null;
        if (preg_match("/$this->botName: log \"(.+)\"/i", $text, $matches)) {
          $this->replyTo($message, pht('TODO FIXME'));
          break;
        }

        /* -- Case #2: Check for 'get log' and get the lastest entries. --  */
        $matches = null;
        if (preg_match("/$this->botName: get log(s?)/i", $text, $matches)) {
          $this->replyTo($message, pht('TODO FIXME'));
          break;
        }

        break;
    }
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
