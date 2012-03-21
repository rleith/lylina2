<?php

// lylina feed aggregator
// Copyright (C) 2011-2012 Robert Leith

class ResetPassword {
    private $db;
    private $auth;

    function __construct() {
        global $db;
        global $auth;
        $this->db = $db;
        $this->auth = $auth;
    }

    private function isKeyValid($key) {
        $row = $this->db->getRow('SELECT user_id FROM lylina_passwordreset
                                  WHERE `key` = ? AND valid_until > NOW()
                                        AND deleted != TRUE',
                                  array($key));
        if($row !== false && count($row) > 0) {
            return $row['user_id'];
        } else {
            return false;
        }
    }

    function render() {
        $render = new Render();

        @$this->auth->startSession();

        if(isset($_REQUEST['password']) && isset($_REQUEST['password2']) &&
           isset($_SESSION['reset_key']) && $this->isKeyValid($_SESSION['reset_key'])) {
            // Check password length
            if(strlen($_REQUEST['password']) < 8) {
                $render->assign('error', 'Password must be at least 8 characters');
            } else if($_REQUEST['password'] != $_REQUEST['password2']) {
                $render->assign('error', 'Passwords do not match');
            } else {
                // Set password
                $this->db->Execute('UPDATE lylina_users SET pass=?
                                    WHERE id = ?',
                                   array($this->auth->hash($_REQUEST['password']),
                                         $this->isKeyValid($_SESSION['reset_key'])));
                // Mark key used
                $this->db->Execute('UPDATE lylina_passwordreset SET deleted=TRUE
                                    WHERE key = ?',
                                   array($_SESSION['reset_key']));
                $_SESSION['reset_key'] = NULL;
                // Reset succeeded - show success message
                $render->assign('success', true);
            }
        } else if(isset($_REQUEST['key']) && $this->isKeyValid($_REQUEST['key'])) {
            // We are going to allow this session to reset a password
            // First regenerate to prevent fixation
            $this->auth->regenerateSession();
            $_SESSION['reset_key'] = $_GET['key'];
        } else {
            header('Location: ./ForgotPassword');
            return;
        }

        $render->assign('title', 'Reset Password');
        $render->display('reset_password.tpl');
    }
}
