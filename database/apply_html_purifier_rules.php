<?php

// Load in the configuration to start
$base_config = parse_ini_file("../config.ini", true);

require_once('../lib/htmlpurifier/library/HTMLPurifier.auto.php');
require_once('../lib/adodb5/adodb.inc.php');

// Create our DB object
$db = ADONewConnection($base_config['database']['type']);
$db->Connect($base_config['database']['hostname'], $base_config['database']['user'], $base_config['database']['pass'], $base_config['database']['database']);

$purifier_config = HTMLPurifier_Config::createDefault();
$purifier_config->set('Cache.SerializerPath', 'cache');
// Allow flash embeds in newer versions of purifier
$purifier_config->set('HTML.SafeObject', true);
$purifier_config->set('Output.FlashCompat', true);
$purifier_config->set('HTML.FlashAllowFullScreen', true);
// Disallow element and attributes not allowed in HTML5
$purifier_config->set('HTML.ForbiddenElements',
                      'basefont, big, center, font, strike, tt, acronym, dir');
$purifier_config->set('HTML.ForbiddenAttributes',
                      'rev, longdesc, name, abbr, scope, summary, align,
                      bgcolor, cellpadding, cellspacing, charoff, clear,
                      compact, frame, hspace, vspace, noshade,
                      nowrap, rules, size, type, valign, border,
                      hr@width, table@width, td@width, th@width, col@width,
                      colgroup@width, pre@width');
$purifier = new HTMLPurifier($purifier_config);

$count_items = $db->GetOne("SELECT count(*) FROM lylina_items");

echo "Total number of items:  $count_items \n";

$start = 0;
$interval = 1000;

$update_item = $db->Prepare('UPDATE lylina_items SET body=? WHERE id=?');

for($i = $start; $i < $count_items; $i += $interval) {
    $end = $i + $interval - 1;
    echo "Processing items $i - $end \n";

    $query = "SELECT id,body FROM lylina_items ORDER BY id ASC LIMIT $i,$interval";
    $items = $db->GetAll($query);

    foreach($items as $item) {
        $db->Execute($update_item, array($purifier->purify($item['body']), $item['id']));
    }
}

?>
