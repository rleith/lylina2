<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011-2012 Robert Leith

// AJAX JSON data for autocompleting feeds
class Feed_autocomplete {
    private $db;
    private $auth;
    function __construct() {
        global $db;
        global $auth;
        $this->db = $db;
        $this->auth = $auth;
    }

    function render() {
        if(!isset($_REQUEST['term']) || !$this->auth->check()) {
            return;
        }

        $term = isset($_REQUEST['term']) ? $_REQUEST['term'] : NULL;

        $feeds = $this->db->GetAll('
                    SELECT lylina_feeds.name,
                           lylina_feeds.url,
                           (select count(*) from lylina_userfeeds where lylina_userfeeds.feed_id = lylina_feeds.id) AS subscribers
                    FROM lylina_feeds
                    WHERE
                        lylina_feeds.id NOT IN (select feed_id from lylina_userfeeds where user_id = ?)
                        AND
                        (select count(*) from lylina_userfeeds where lylina_userfeeds.feed_id = lylina_feeds.id) > 2
                        AND
                        (lylina_feeds.name LIKE ? OR lylina_feeds.url LIKE ?)
                    ORDER BY subscribers
                    LIMIT 10', array($this->auth->getUserId(), "%$term%", "%$term%"));

        $feed_array = array();

        foreach($feeds as &$feed) {
            $feed_obj = new StdClass;
            $feed_obj->label = $feed['name'];
            $feed_obj->value = $feed['url'];
            $feed_array[] = $feed_obj;
        }

		$render = new Render();
		$render->assign('json_data', json_encode($feed_array));
		$render->display('json_data.tpl');
    }
}
