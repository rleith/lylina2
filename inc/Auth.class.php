<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011 Robert Leith

// Provides support for authentication
class Auth {
    // Handle on the DB
    private $db;
    // Handle on the configuration
    private $config;

    function __construct() {
        global $db;
        global $base_config;
        $this->db = $db;
        $this->config = new Config($this->db);
        $this->salt = $base_config['security']['salt'];

        // Variable to store cookie lifetime. Default to 0 - browser close.
        $lifetime = 0;

        // Set up session location if configured
        if(isset($base_config['session']['path']) && strlen($base_config['session']['path']) > 0) {
            session_save_path($base_config['session']['path']);
        }

        // Set session length if configured
        if(isset($base_config['session']['length']) && strlen($base_config['session']['length']) > 0) {
            ini_set('session.gc_maxlifetime', $base_config['session']['length']);
            // Save value for use in setting cookie params later
            $lifetime = $base_config['session']['length'];
        }

        // Set up session cookie settings
        session_name("LYLINA_SESS");

        // Set http-only flag for session cookie as the JS does not need to use it.
        // Helps prevent XSS attacks on the user's session cookie.
        session_set_cookie_params($lifetime, '/', $_SERVER['SERVER_NAME'], false, true);
    }

    private function preventHijacking() {
        if(!isset($_SESSION['userAgent'])) {
            return false;
        }

        if($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
            return false;
        }

        return true;
    }

    private function regenerateSession() {
        // If the session is already set to expire should not regenerate a new id
        if(isset($_SESSION['expires'])) {
            return;
        }

        // Set session to expire in 5 minutes
        $_SESSION['expires'] = time() + 5*60;

        // Create new session id without destroying the old one
        session_regenerate_id(false);

        // Grab current session id and close both sessions to allow other scripts to use them
        $newSession = session_id();
        session_write_close();

        // Set the session id to the new one and start it again
        session_id($newSession);
        session_start();

        // Unset expires flag for new session
        unset($_SESSION['expires']);
    }

    private function validateSession() {
        if(isset($_SESSION['expires']) && $_SESSION['expires'] < time()) {
            return false;
        }

        return true;
    }

    private function startSession() {
        session_start();

        // Make sure the session is valid, and destroy it if not
        if($this->validateSession()) {
            // Check if session is new or is a hijacking attempt
            if(!$this->preventHijacking()) {
                // Reset session data and regenerate id
                $_SESSION = array();
                $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
                $this->regenerateSession();

            // 5% chance to regenerate sessionid
            } elseif(rand(1,100) <= 5) {
                $this->regenerateSession();
            }
        } else {
            $_SESSION = array();
            session_destroy();
            session_start();
        }
    }

    function getUserId() {
        if(isset($_SESSION['user'])) {
            $user = $this->db->getRow('SELECT id FROM lylina_users WHERE login = ?', array($_SESSION['user']));
            if(count($user) > 0) {
                return (int) $user['id'];
            }
        }

        return NULL;
    }

    function getUserName() {
        if(isset($_SESSION['user'])) {
            return $_SESSION['user'];
        } else {
            return NULL;
        }
    }

    function hash($pass) {
        return sha1($pass . $this->salt);
    }

    function validate($user, $pass) {
        $userRow = $this->db->GetRow('SELECT * FROM lylina_users WHERE login = ?', array($user));

        if(count($userRow) == 0) {
            return false;
        }

        if($userRow['pass'] == $this->hash($pass)) {
            // If its a good password, let's start the session
            @$this->startSession();
            // Make sure the session is regenerated
            $this->regenerateSession();
            // Store user name for later identification
            $_SESSION['user'] = $userRow['login'];
            // Give user elevated privileges for a time as they just auth'd
            $_SESSION['elevated_expires'] = time() + 15*60;
            return true;
        } else {
            return false;
        }
    }

    function check() {
        @$this->startSession();

        if(isset($_SESSION['user'])) {
            return true;
        } else {
            return false;
        }
    }

    function check_elevated() {
        @$this->startSession();

        if(!isset($_SESSION['user'], $_SESSION['elevated_expires'])) {
            return false;
        }

        if(time() > $_SESSION['elevated_expires']) {
            return false;
        }

        // Check is good, renew the user's elevated privileges
        $_SESSION['elevated_expires'] = time() + 15*60;

        return true;
    }

    function logout() {
        // To logout we just have to destroy the session, this will break the key and the users session will be invalid
        // Do not use startSession private method here as we do not want to regenerate the session leaving the old one valid for a time
        @session_start();
        $_SESSION = array();
        @session_destroy();
        // TODO: Delete the cookie? It works fine without doing so, and this is very ugly, thanks PHP!
    }
}
