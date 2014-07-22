<?php

/**
 * Looks for links to GHC Trac and links to them.
 */
final class PhabricatorBotGhcTracHandler extends PhabricatorBotHandler {

  /**
   * Map of PHIDs to the last mention of them (as an epoch timestamp); prevents
   * us from spamming chat when a single object is discussed.
   */
  private $recentlyMentioned = array();

  public function receiveMessage(PhabricatorBotMessage $message) {
    switch ($message->getCommand()) {
      case 'MESSAGE':
        $target_name = $message->getTarget()->getName();
        if ($target_name !== '#ghc') {
          // Don't do this in non-GHC channels, as it's probably annoying.
          break;
        }

        $text = $message->getBody();
        $tickets = array();
        $matches = null;

        $pattern =
          '@'.
          '#(\d+)'.
          '@';

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
          foreach ($matches as $match) {
            $tickets[$match[1]] = $match[1];
          }
        }

        foreach ($tickets as $ticket) {

          // Don't mention the same object more than once every 10 minutes
          // in public channels, so we avoid spamming the chat over and over
          // again for discsussions of a specific revision, for example.
          if (empty($this->recentlyMentioned[$target_name])) {
            $this->recentlyMentioned[$target_name] = array();
          }

          $quiet_until = idx(
            $this->recentlyMentioned[$target_name],
            $ticket,
            0) + (60 * 10);

          if (time() < $quiet_until) {
            // Remain quiet on this channel.
            continue;
          }

          // Grab the ticket HTTPS content
          $title = '(Description unavailable)';
          $url   = 'https://ghc.haskell.org/trac/ghc/ticket/'.$ticket;
          $html  = HTTPSFuture::loadContent($url);

          if ($html) {

            // Match the trac title span of the ticket. This is horrible
            // and awful and bad.
            $pattern =
              '@'.
              '<h1 id="trac-ticket-title" class="searchable">\s+'.
              '<span class="summary">'.
              '(.*)'.
              '</span>\s+'.
              '</h1>'.
              '@';

            $matches = null;
            if (preg_match($pattern, $html, $matches)) {
              $title = $matches[1];
            }
          }

          $desc  = '#'.$ticket.': '.$title.' - '.$url;

          $this->recentlyMentioned[$target_name][$ticket] = time();
          $this->replyTo($message, pht($desc));
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
