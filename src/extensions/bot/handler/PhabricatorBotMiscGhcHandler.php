<?php

/**
 * Does a few random things for the #ghc channel on Freenode.
 */
final class PhabricatorBotMiscGhcHandler extends PhabricatorBotHandler {

  public function receiveMessage(PhabricatorBotMessage $message) {
    switch ($message->getCommand()) {
      case 'MESSAGE':
        $target_name = $message->getTarget()->getName();
        $text        = $message->getBody();
        if ($target_name !== '#ghc') {
          // Don't do this in non-GHC channels, as it's probably annoying.
          break;
        }

        /* -- Case #1: Check for 'reportabug' and link to Trac -- */
        $matches = null;
        if (preg_match('/reportabug/i', $text, $matches)) {
          $this->replyTo($message,
            pht('https://ghc.haskell.org/trac/ghc/newticket?type=bug'));
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
