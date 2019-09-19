<?php
class WildcardHooks {

  public static function onSidebarBeforeOutput(Skin $skin, &$bar) {
    if (!$skin->getUser()->isLoggedIn()) {
      return false;
    }

    $page_url = '';
    if (!is_null($skin->getTitle()) && $skin->getTitle()->isKnown()) {
      $page_url = $skin->getTitle()->getFullText();
    }

    $wildcard_links = [];

    if(WildcardSession::isEnabled()) {
      $wildcard_links[] = [
        "text" => "Stop Picking",
        "href" => SpecialPage::getTitleFor('Wildcard')->getLocalUrl(
          ['cmd' => 'stop', 'returnto' => $page_url]
        )
      ];
    } else {
      $wildcard_links[] = [
        "text" => "Start Picking",
        "href" => SpecialPage::getTitleFor('Wildcard')->getLocalUrl(
          ['cmd' => 'start', 'returnto' => $page_url]
        )
      ];
    }

    $wildcard_links[] = [
      "text" => "Everyone's Picks",
      "href" => SpecialPage::getTitleFor('Wildcard')->getLocalUrl()
    ];

    $bar["Wildcard"] = $wildcard_links;
  }

  public static function siteNoticeAfter( &$siteNotice, $skin ) {
    if(!WildcardSession::isEnabled()) {
      return true;
    }

    $title = $skin->getTitle();

    if (!$title->exists()) {
      return true;
    }

    if (!preg_match("/\(\\d*\)$/", $title->getPrefixedText())) {
      return true;
    }

    if (!$skin->getUser()->isLoggedIn()) {
      return true;
    }

    $dbw = wfGetDB(DB_MASTER);
    $res = $dbw->select(
      'Wildcard',
      ['page_id'],
      'user_id = ' . $skin->getUser()->getId()
    );

    $page_id = $skin->getWikiPage()->getId();
    $selected_pages = [];
    foreach($res as $row) {
      if($row->page_id == $page_id) {
        $page_already_selected = true;
      }

      $selected_pages[$row->page_id] = WikiPage::newFromID($row->page_id);
    }

    $siteNotice .= self::renderWildcardBox($title, $selected_pages, $page_id);
    return true;
  }

  # Rendering via string concatenation is not ideal, but how to
  # delegate to the mediawiki templating system deserves more
  # discussion.
  public static function renderWildcardBox($title, $selected_pages, $page_id) {
    $html = "";
    $html .= "<div style='border:1px solid black;padding:10px;text-align:left;margin-top:10px;background-color:#F2F2F2'>";
    $html .= "<h2 style='margin-top:0px;border-bottom:0px'>";
    $html .= "<span style='text-decoration:underline'>Wildcard Choices</span>";
    $html .= "<span style='font-size:80%'> (<a href='";
    $html .= SpecialPage::getTitleFor('Wildcard')->getLocalUrl(
      ['cmd' => 'stop',  'returnto' => $title->getFullText()]
    );
    $html .= "'>Stop Picking</a>)</span>";
    $html .= "</h2>";

    $page_already_selected = false;

    $html .= "<ul>";
    if(count($selected_pages) > 0) {
      $html .= "<li>My Picks (" . count($selected_pages) . "/2)";
      $html .= "<ul>";
      if(count($selected_pages) == 2 && !array_key_exists($page_id, $selected_pages)) {
        $html .= "<li style='font-style:italic'>To pick the current page, remove one below";
      }
      foreach($selected_pages as $selected_page_id => $selected_page) {
        $html .= "<li>";
        if($page_id == $selected_page_id) {
          $html .= "<span style='font-style:italic'>(Current Page)</span> ";
        } else {
          $html .= "<a href='" . $selected_page->getTitle()->getLocalUrl() . "'>";
        }
        $html .= $selected_page->getTitle()->getPrefixedText();
        if($page_id != $selected_page_id) {
          $html .= "</a>";
        }
        $html .= " (<a href='";
        $html .= SpecialPage::getTitleFor('Wildcard')->getLocalUrl(
          ['cmd' => 'remove', 'page' => $selected_page_id, 'returnto' => $page_id]
        );
        $html .= "'>Unpick</a>)";
        $html .= "\n";
      }
      $html .= "</ul>";
    }
    if (!(array_key_exists($page_id, $selected_pages)) && !(count($selected_pages) > 1)) {
      $html .= "<li><a rel='nofollow' href='";
      $html .= SpecialPage::getTitleFor('Wildcard')->getLocalUrl(
        ['cmd' => 'pick', 'page' => $page_id]
      );
      $html .= "'>Pick this page</a>";
      $html .= " [" . $title->getPrefixedText() . "]";
    }
    $html .= "<li><a href='";
    $html .= SpecialPage::getTitleFor('Wildcard')->getLocalUrl();
    $html .= "'>View Everyone's Picks</a>";
    $html .= "</ul>";

    $html .= "</div>";

    return $html;
  }

  public static function onLoadExtensionSchemaUpdates( $updater ) {
    $updater->addExtensionTable("Wildcard", __DIR__ . "/../sql/wildcard.sql");
  }
}
?>