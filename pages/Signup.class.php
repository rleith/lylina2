<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011 Robert Leith

// Handle the signup interface
class Signup {
    // Our handle on the DB
    private $db;
    // Our handle on Auth
    private $auth;

    function __construct() {
        global $db;
        $this->db = $db;
        global $auth;
        $this->auth = $auth;
    }

    // General TODO: check sanity of all inputs

    function render() {
        $render = new Render($this->db);

        // If already logged in go back to the index
        if($this->auth->check()) {
            header('Location: index.php');
            return;
        }

        $error = false;

        if(isset($_REQUEST['login'], $_REQUEST['email'], $_REQUEST['password'], $_REQUEST['password2'])) {
            $login = $_REQUEST['login'];
            $email = $_REQUEST['email'];
            $password = $_REQUEST['password'];
            $password2 = $_REQUEST['password2'];

            // Check login name
            if(strlen($login) == 0) {
                $render->assign('login_error', 'Login name is required.');
                $error = true;
            } else if($this->login_exists($login)) {
                $render->assign('login_error', 'Login name already exists. Please choose another.');
                $error = true;
            }
                
            if(strlen($email) == 0) {
                $render->assign('email_error', 'Email address is required.');
                $error = true;
            }

            // Check password
            if(strlen($password) < 8) {
                $render->assign('password_error', 'Password must be at least 8 characters.');
                $error = true;
            } else if(strcmp($password, $password2) != 0) {
                $render->assign('password_error', 'Passwords do not match, please try again.');
                $error = true;
            }

            if($error) {
                // assign render variables so user doesn't have to type in login again
                $render->assign('login', $login);
                $render->assign('email', $email);
            } else {
                $this->create_new_user($_REQUEST['login'], $_REQUEST['password'], $_REQUEST['email']);
                $this->auth->validate($_REQUEST['login'], $_REQUEST['password']);
                header('Location: admin?op=new_user');
                return;
            }
        }

        $render->assign('title', 'Signup');
        $render->display('signup.tpl');
    }

    function login_exists($login) {
        $result = $this->db->GetAll('SELECT login FROM lylina_users WHERE login = ?', array($login));
        return count($result) > 0;
    }

    function create_new_user($login, $password, $email) {
        $this->db->Execute('INSERT INTO lylina_users (login, pass, email) VALUES(?, ?, ?)',
                            array($login, $this->auth->hash($password), $email));
    }

}
