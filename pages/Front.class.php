<?php

// lylina feed aggregator
// Copyright (C) 2011-2012 Robert Leith

class Front {
    private $db;
    private $auth;

    function __construct() {
        global $db;
        global $auth;
        $this->db = $db;
        $this->auth = $auth;
    }

    function render() {
        $render = new Render();
        $render->assign('title', 'Lylina');
        $render->display('front.tpl');
    }
}
