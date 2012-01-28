<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2012 Robert Leith

class Main {
    private $db;
    private $analyticsID;

    function __construct() {
        global $db;
        global $auth;
        global $analyticsID;
        $this->db = $db;
        $this->analyticsID = $analyticsID;
    }

    function render() {
        $render = new Render();
        $render->assign('update', true);
        $render->assign('analyticsID', $this->analyticsID);
        $render->display('head.tpl');

        $items = new Items($this->db);
        $list = $items->get_items();

        $render->assign('items', $list);
        $render->display('items.tpl');
        $render->display('foot.tpl');
    }
}
