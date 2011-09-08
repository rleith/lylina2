<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011 Robert Leith

// AJAX JSON data for autocompleting feeds
class Feed_autocomplete {
	private $db;
	function __construct() {
		global $db;
		$this->db = $db;
	}

	function render() {
        if(!isset($_REQUEST['term'])) {
            return;
        }

        $term = isset($_REQUEST['term']) ? $_REQUEST['term'] : NULL;

        $feeds = $this->db->GetAll('SELECT lylina_feeds.name, lylina_feeds.url FROM lylina_feeds WHERE lylina_feeds.name LIKE ? OR lylina_feeds.url LIKE ?', array("%$term%", "%$term%"));

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
