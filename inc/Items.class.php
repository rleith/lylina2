<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011 Robert Leith

// Item operations?
// TODO: Define relationship with other classes
class Items {
    private $db;
    private $auth;
    function __construct() {
        global $db;
        global $auth;
        $this->db = $db;
        $this->auth = $auth;
    }

    function get_items($newest = 0, $pivot = 0, $search = array()) {
        if(!$this->auth->check()) {
            return array();
        }

        $args = array();

        // Build select
        $select_clause = "SELECT lylina_items.id, 
                                 lylina_items.url, 
                                 lylina_items.title, 
                                 lylina_items.body, 
                                 UNIX_TIMESTAMP(lylina_items.dt) AS timestamp, 
                                 lylina_feeds.url AS feed_url,
                                 (SELECT feed_name FROM lylina_userfeeds WHERE lylina_userfeeds.user_id = ? and lylina_userfeeds.feed_id = lylina_items.feed_id) AS feed_name,
                                 COALESCE(lylina_vieweditems.viewed,0) AS viewed";
        $args[] = $this->auth->getUserId();

        // Build from and join
        $from_clause = "FROM lylina_items";
        // TODO: Try removing straight_join hint once mysql is upgraded. Without it on older mysql the optimizer messes up the join order preventing indexes from being used but seems to work fine on 5.5
        $from_clause .= " STRAIGHT_JOIN lylina_feeds ON (lylina_items.feed_id = lylina_feeds.id)";
        $from_clause .= " LEFT JOIN lylina_vieweditems ON (lylina_vieweditems.user_id = ? AND lylina_items.id = lylina_vieweditems.item_id)";
        $args[] = $this->auth->getUserId();

        // Build where
        // initial condition limiting to items in subscribed feeds
        $where_clause = "WHERE lylina_items.feed_id IN (select feed_id from lylina_userfeeds where user_id = ?) ";
        $args[] = $this->auth->getUserId();

        if($pivot > 0) {
            $where_clause .= " AND lylina_items.dt < (select dt from lylina_items where id = ?)
                              AND lylina_items.id > ?";
            $args[] = $pivot;
            $args[] = $newest;
        } else if(count($search) == 0) { // Only limit by time if not searching
            $where_clause .= " AND lylina_items.dt > DATE_SUB(NOW(), INTERVAL 8 HOUR)
                              AND lylina_items.id > ?";
            $args[] = $newest;
        }

        // Add search conditions
        foreach($search as $term) {
            $where_clause .= " AND (lylina_items.title LIKE ? OR lylina_items.body LIKE ?) ";
            $term_like_str = '%' . $term . '%';
            // Add to args twice
            $args[] = $term_like_str;
            $args[] = $term_like_str;
        }

        // Build suffix
        $suffix = "ORDER BY lylina_items.dt DESC";
        if($pivot > 0 || count($search) > 0) {
            $suffix .= " LIMIT 100";
        }

        // Assemble final query
        $query = "$select_clause $from_clause $where_clause $suffix";
        //error_log($query);
        //error_log(implode(", ", $args));
        $items = $this->db->GetAll($query, $args);

        // Only calculate newest if pivot is not set
        if(!$pivot) {
            $newest_read = 0;

            // Find out what the newest one we've read is
            foreach($items as &$item) {
                if($item['viewed'] && $item['id'] > $newest_read) {
                    $newest_read = $item['id'];
                }
            }

            // If its newer than the newest item on this page, use that as the "newest" instead, likely the user browsed from a different location
            if($newest_read > $newest) {
                $newest = $newest_read;
            }
        }

        foreach($items as &$item) {
            // If we have a newer item, mark it as new
            if($newest && $item['id'] > $newest) {
                $item['new'] = 1;
            } else {
                $item['new'] = 0;
            }
            // Format the date for headers
            $item['date'] = date('l F j, Y', $item['timestamp']);
        }

        return $items;
    }
}   
