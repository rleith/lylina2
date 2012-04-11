<?php

// lylina feed aggregator
// Copyright (C) 2011-2012 Robert Leith

class Front {
    private $db;
    private $auth;
    private $loginError;

    function __construct() {
        global $db;
        global $auth;
        $this->db = $db;
        $this->auth = $auth;
        $this->loginError = false;
    }

    function setLoginError($error) {
        $this->loginError = $error;
    }

    function render() {
        $render = new Render();
        $render->assign('title', 'Lylina');

        if($this->loginError) {
            $render->assign('error', 'Incorrect username or password.');
        }

        $render->display('front.tpl');
    }
}
