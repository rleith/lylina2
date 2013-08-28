<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011 Robert Leith
// Copyright (C) 2013 Nathan Watson

// AJAX display feeds
class Get_Items {
    private $db;
    function __construct() {
        global $db;
        $this->db = $db;
    }

    function render() {
        if(isset($_REQUEST['newest'])) {
            $newest = $_REQUEST['newest'];
        } else {
            $newest = 0;
        }
        if(isset($_REQUEST['pivot'])) {
            $pivot = $_REQUEST['pivot'];
        } else {
            $pivot = false;
        }
        
        //TODO: handle this better so we don't have to keep checking if $search_terms is empty
        if(isset($_REQUEST['search']) && strlen($_REQUEST['search']) > 0) {
            $search_terms = $_REQUEST['search'];
        } else {
            $search_terms = "";
        }

        $items = new Items($this->db);

        $list = $items->get_items($newest, $pivot, $search_terms);

        $render = new Render();
        $render->assign('items', $list);
        $render->display('items.tpl');
    }
}
