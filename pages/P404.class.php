<?php

// lylina feed aggregator
// Copyright (C) 2012 Robert Leith

class P404 {

    private $db;

    function __construct() {
        global $db;
        $this->db = $db;
    }

    function render() {
        $render = new Render($this->db);
        $render->display('404.tpl');
    }
}
