<?php

/**
 * Takes messages of the form:
 *   <bot name>: log I did a thing
 * and logs it to the Phriction page:
 *   w/projects/haskell.org_infrastructure/server_admin_log/
 *
 * Also takes messages of the form:
 *   <bot name: get log(s)
 * and retrieves the last 3 log entries from the page.
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

  private function affirmative() {
    $affirmatives = array(
      pht('Indubitably.'),
      pht('You betcha!'),
      pht('Indeed.'),
      pht('You got it!'),
      pht('Aye-aye!'),
      pht('beep-boop beep-boop. Affirmative.'),
      pht('Uh huh!'),
      pht('Consider it done.'),
      pht('Anything for you!'),
      pht('Look at you, being all productive!'),
      pht('You silly sysadmin!'),
      pht('I will never forget!'),
      pht('If you say so...'),
      pht('Roger - the NSA has been notified.'),
      pht('GATTACA!'), // Rafi ftw
      pht('k'),
      pht('I like the cut of that jib, pal!'),
      pht('LIES'),
    );
    return $affirmatives[array_rand($affirmatives)];
  }

  private function linkIrcUser($nickname) {
    $nick_map = array(
      'thoughtpolice' => 'austin',
    );

    if (array_key_exists($nickname, $nick_map)) {
      return '@'.$nick_map[$nickname];
    } else {
      return '@'.$nickname;
    }
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
          $today = date('Y-m-d');
          $timestamp = date('H:i T');
          $date = null;
          $raw_lines = explode("\n", $this->getWikiPageContent());

          // $linenum starts at 1. This because if we don't find today's date
          // below, then we insert it (at line 0) and log immediately below it.
          $linenum = 1;
          $found_date = false;
          $i = 0;
          foreach ($raw_lines as $line) {
            if (preg_match('/=== Date: (.+) ===/i', $line, $date) &&
                $date[1] == $today) {
              $linenum = $i + 1;
              $found_date = true;
              break;
            }
            $i++;
          }

          if (!$found_date) {
            $date_fmt = '=== Date: '.$today.' ===';
            array_splice($raw_lines, 0, 0, $date_fmt);
          }

          $sender_nick = $this->linkIrcUser($message->getSender()->getName());
          $msg = ' - **'.$timestamp.'** '.$sender_nick.': '.$matches[1];
          array_splice($raw_lines, $linenum, 0, $msg);

          // Pedantic formatting silliness - add a newline after the new
          // section. Get the line number of the date line, skip over the log
          // line, then insert a newline.
          if (!$found_date) {
            array_splice($raw_lines, $linenum + 1, 0, "\n");
          }

          $this->editWikiPage(implode("\n", $raw_lines));
          $this->replyTo($message, $this->affirmative());
          break;
        }

        /* -- Case #2: Check for 'get log' and get the lastest entries. --  */
        $matches = null;
        if (preg_match("/$this->botName: get log(s?)/i", $text, $matches)) {
          $events = array();
          $raw_lines = explode("\n", $this->getWikiPageContent());
          $date = null;
          $last_date = null;
          $logline = null;
          foreach ($raw_lines as $line) {
            if (preg_match('/=== Date: (.+) ===/i', $line, $date)) {
              $last_date = $date[1];
              $events[$last_date] = array();
            }
            if (preg_match('/ - (.+)/i', $line, $logline)) {
              $events[$last_date][] = $logline[1];
            }
          }

          $formatted_events = array();
          foreach ($events as $date => $actions) {
            $date = '['.chr(2).$date.chr(2).'] ';
            foreach ($actions as $action) {
              $formatted_events[] = $date.$action;
            }
          }

          $i = 0;
          foreach ($formatted_events as $e) {
            if ($i == $this->numLogs) {
              break;
            }

            $this->replyTo($message, $e);
            $i++;
          }

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
