<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011-2012 Robert Leith

// Mark things as read
class Read {
    private $db;
    private $auth;
    function __construct() {
        global $db;
        global $auth;
        $this->db = $db;
        $this->auth = $auth;
    }

    function render() {
        $item_id = isset($_REQUEST['id'])? $_REQUEST['id'] : false;

        // Only mark items read if user is authenticated
        if($item_id && $this->auth->check()) {
            $this->db->Execute('INSERT INTO lylina_vieweditems
                                (user_id, item_id, viewed, viewed_timestamp)
                                VALUES(?, ?, 1, NOW())',
                               array($this->auth->getUserId(), $item_id));
        }
    }
}
