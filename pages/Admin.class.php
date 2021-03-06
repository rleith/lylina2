<?php

// lylina feed aggregator
// Copyright (C) 2004-2005 Panayotis Vryonis
// Copyright (C) 2005 Andreas Gohr
// Copyright (C) 2006-2010 Eric Harmon
// Copyright (C) 2011-2012 Robert Leith
// Copyright (C) 2011-2012 Nathan Watson

// Handle the admin interface
class Admin {
    // Our handle on the DB
    private $db;
    // Our handle on Auth
    private $auth;
    private $analyticsID;
    
    function __construct() {
        global $db;
        $this->db = $db;
        global $auth;
        $this->auth = $auth;
        global $analyticsID;
        $this->analyticsID = $analyticsID;
    }

    // General TODO: check sanity of all inputs

    function render() {
        $render = new Render($this->db);
        $render->assign('analyticsID', $this->analyticsID);

        // Check our authorization
        $auth = new Auth($this->db);
        // Otherwise we need to check to see if the user has already logged in or not
        if(!$this->auth->check()) {
            header('HTTP/1.1 403 Forbidden');
            $render->assign('title', 'There was an error');
            $render->assign('reason', 'You need to login to perform this operation.');
            $render->display('auth_fail.tpl');
            return;
        }
        // Check that the user has elevated privileges. If not they need to provide their password again
        if(!$this->auth->check_elevated()) {
            $op = 'elevate';
        } elseif(empty($_REQUEST['op'])) {
            $op = 'main';
        } else {
            $op = $_REQUEST['op'];
        }
        if(method_exists($this, $op)) {
            $this->$op($render);
        } else {
            header('HTTP/1.1 404 Not Found');
            $render->assign('title', 'There was an error');
            $render->assign('reason', 'The page you are looking for does not seem to exist.');
            $render->display('auth_fail.tpl');
            return;
        }
    }

    function login($render) {
        if(isset($_REQUEST['redirect'])) {
            header('Location: ' . $_REQUEST['redirect']);
        } else if(!empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: index.php');
        }
    }

    function elevate($render) {
        $render->assign('title', 'Password required');
        // Make sure we do not redirect back to the same page
        if(strpos($_SERVER['REQUEST_URI'], 'op=elevate') !== FALSE) {
            $render->assign('redirect', 'admin');
        } else {
            $render->assign('redirect', $_SERVER['REQUEST_URI']);
        }
        $render->assign('user', $this->auth->getUserName());
        $render->assign('message', 'Your password is required to perform this operation.');
        $render->display('login.tpl');
    }

    function new_user($render) {
        $render->assign('new_user', true);
        $this->main($render);
    }

    function main($render) {
        $feeds = $this->db->GetAll(
            'SELECT id, url, favicon_url,
             (SELECT COALESCE(lylina_userfeeds.feed_name, lylina_feeds.name)
                FROM lylina_userfeeds WHERE lylina_userfeeds.user_id = ?
                AND lylina_userfeeds.feed_id = lylina_feeds.id) AS name,
             (SELECT COUNT(*) FROM lylina_items WHERE lylina_items.feed_id = lylina_feeds.id) AS itemcount
             FROM lylina_feeds
             INNER JOIN (lylina_userfeeds)
                ON (lylina_feeds.id = lylina_userfeeds.feed_id)
             WHERE lylina_userfeeds.user_id = ?
             ORDER BY lylina_feeds.name',
             array($this->auth->getUserId(), $this->auth->getUserId()));
        $render->assign('feeds', $feeds);
        $email = $this->auth->getUserEmail();
        $render->assign('email', $email);
        $render->assign('title', 'Preferences');
        $render->display('preferences.tpl');
    }

    function add($render) {
        if(isset($_REQUEST['url']) && strlen($_REQUEST['url']) > 0) {
            $url = $_REQUEST['url'];

            require_once('lib/simplepie/simplepie.inc');
            $pie = new SimplePie();
            $pie->force_remote(true);
            $pie->enable_cache(false);
            $pie->set_autodiscovery_level(SIMPLEPIE_LOCATOR_ALL);
            $pie->set_feed_url($url);
            $pie->init();
            $feed_url = $pie->feed_url;
            $feed_title = $pie->get_title();

            // Save feed to insert into session variables for later insertion into db
            // only do this if we found items at the given url. This way we won't
            // insert broken urls in doadd(). Also prevents inserting a new feed
            // that never gets subscribed to in the following page.
            if(count($pie->get_items()) > 0) {
                $_SESSION['new_feed_url'] = $feed_url;
                $_SESSION['new_feed_name'] = $feed_title;
            } else {
                $_SESSION['new_feed_url'] = NULL;
                $_SESSION['new_feed_name'] = NULL;
            }

            $render->assign('url', $_REQUEST['url']);
            $render->assign('feed_url', $feed_url);
            $render->assign('items', array_slice($pie->get_items(), 0, 5));
            $render->assign('feed', $pie);
        } else {
            if(isset($_REQUEST['url'])) {
                $render->assign('url', $_REQUEST['url']);
            } else {
                $render->assign('url', "");
            }
            $render->assign('items', NULL);
        }

        $render->assign('title', 'Adding Feed');
        $render->display('feed_search.tpl');
    }

    function doadd($render) {
        $feed = $_REQUEST['feedurl'];
        $title = $_REQUEST['feedtitle'];

        if(isset($feed, $title, $_SESSION['new_feed_url'], $_SESSION['new_feed_name'])) {
            // Insert into lylina_feeds values obtained directly from simplepie
            $this->db->Execute('INSERT IGNORE INTO lylina_feeds (url, name) VALUES(?, ?)',
                                array($_SESSION['new_feed_url'], $_SESSION['new_feed_name']));
            // subscribe the user
            $this->db->Execute('INSERT IGNORE INTO lylina_userfeeds (feed_id, user_id, feed_name)
                                VALUES ((select id from lylina_feeds where url = ?), ?, ?)',
                                array($feed, $this->auth->getUserId(), NULL));
        }

        // Remove stored feed values
        $_SESSION['new_feed_url'] = NULL;
        $_SESSION['new_feed_name'] = NULL;
        
        header('Location: admin');
    }

    function import($render) {
        $xml = new XMLReader();
        $xml->open($_FILES['opml']['tmp_name']);
        while($xml->read()) {
                if($xml->nodeType == XMLReader::ELEMENT && $xml->localName == 'outline' && $xml->getAttribute('type') == 'rss') {
                        $feed = $xml->getAttribute('xmlUrl');
                        $title = $xml->getAttribute('text');

                        $this->db->Execute('INSERT IGNORE INTO lylina_feeds (url, name) VALUES (?, ?)',
                                            array($feed, $title));
                        $this->db->Execute('INSERT IGNORE INTO lylina_userfeeds (feed_id, user_id, feed_name)
                                            VALUES ((select id from lylina_feeds where url = ?), ?, ?)',
                                            array($feed, $this->auth->getUserId(), $title));
                }
        }

        header('Location: admin');
    }

    function export($render) {
        $feeds = $this->db->GetAll(
                'SELECT url,
                 (SELECT COALESCE(lylina_userfeeds.feed_name, lylina_feeds.name)
                        FROM lylina_userfeeds WHERE lylina_userfeeds.user_id = ?
                        AND lylina_userfeeds.feed_id = lylina_feeds.id) AS name
                 FROM lylina_feeds
                 INNER JOIN (lylina_userfeeds)
                        ON (lylina_feeds.id = lylina_userfeeds.feed_id)
                 WHERE lylina_userfeeds.user_id = ?',
                 array($this->auth->getUserId(), $this->auth->getUserId()));

        $xml = new XMLWriter();
        $xml->openMemory();

        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('opml');
        $xml->writeAttribute('version', '1.0');
        $xml->startElement('head');
        $xml->writeElement('title', 'lylina feeds');
        $xml->writeElement('ownerName', $this->auth->getUserName());
        $xml->endElement();

        $xml->startElement('body');
        foreach($feeds as $feed) {
                $xml->startElement('outline');
                $xml->writeAttribute('text', $feed['name']);
                $xml->writeAttribute('xmlUrl', $feed['url']);
                $xml->writeAttribute('type', 'rss');
                $xml->endElement();
        }
        $xml->endElement();

        $xml->endElement();
        $xml->endDocument();
        
        header('Content-disposition: attachment; filename=lylina_feeds.opml');
        header('Content-type: text/x-opml');
        $render->assign('output', $xml->outputMemory());
        $render->display('export.tpl');
    }

    function delete($render) {
        $confirm = isset($_REQUEST['confirm']) ? $_REQUEST['confirm'] : false;
        $id = $_REQUEST['id'];
        if($confirm) {
            $user_id = $this->auth->getUserId();
            // Delete the mapping to this feed for this user
            // Also deleted the viewed items record for this feed, user pair
            $this->db->Execute('DELETE lylina_userfeeds, lylina_vieweditems
                                FROM lylina_userfeeds
                                LEFT JOIN lylina_items ON lylina_userfeeds.feed_id = lylina_items.feed_id
                                LEFT JOIN lylina_vieweditems ON lylina_items.id = lylina_vieweditems.item_id
                                                             AND lylina_userfeeds.user_id = lylina_vieweditems.user_id
                                WHERE lylina_userfeeds.feed_id = ? AND lylina_userfeeds.user_id = ?',
                                array($id, $user_id));
            // Delete the feed and all its items if no one is subscribed to it anymore
            $this->db->Execute('DELETE lylina_feeds, lylina_items
                                FROM lylina_feeds
                                LEFT JOIN lylina_items ON lylina_feeds.id = lylina_items.feed_id
                                LEFT JOIN lylina_userfeeds ON lylina_feeds.id = lylina_userfeeds.feed_id
                                WHERE lylina_feeds.id = ? AND lylina_userfeeds.user_id IS NULL',
                                array($id));

            header('Location: admin');
        } else {
            $feed = $this->db->GetAll('SELECT id,
                                              lylina_userfeeds.feed_name AS name,
                                              (SELECT COUNT(*)
                                                FROM lylina_items
                                                WHERE lylina_items.feed_id = lylina_feeds.id)
                                              AS itemcount
                                       FROM lylina_feeds
                                        INNER JOIN (lylina_userfeeds)
                                            ON (lylina_feeds.id = lylina_userfeeds.feed_id
                                                AND lylina_userfeeds.user_id = ?)
                                       WHERE lylina_feeds.id = ?',
                                       array($this->auth->getUserId(), $id));
            $render->assign('feed', $feed[0]);
            $render->assign('title', 'Confirm Delete');
            $render->display('confirm_delete.tpl');
        }
    }

    function rename($render) {
        $confirm = isset($_REQUEST['confirm']) ? $_REQUEST['confirm'] : false;
        $reset = isset($_REQUEST['reset']) ? $_REQUEST['reset'] : false;
        $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
        $id = $_REQUEST['id'];
        if($confirm) {
            $this->db->Execute('UPDATE lylina_userfeeds SET feed_name=? WHERE feed_id=? AND user_id=?', array($name, $id, $this->auth->getUserId()));
            header('Location: admin');
        } elseif($reset) {
            $this->db->Execute('UPDATE lylina_userfeeds SET feed_name = NULL WHERE feed_id = ? AND user_id = ?', array($id, $this->auth->getUserId()));
            header('Location: admin');
        } else {
            $feed = $this->db->GetRow('SELECT feed_id AS id, feed_name AS name FROM lylina_userfeeds WHERE feed_id=? AND user_id=?', array($id, $this->auth->getUserId()));
            $globalfeed = $this->db->GetRow('SELECT name FROM lylina_feeds WHERE id=?', array($id));
            $render->assign('feed', $feed);
            $render->assign('globalfeed', $globalfeed);
            $render->assign('title', 'Rename Feed');
            $render->display('rename_feed.tpl');
        }
    }
    function passwd($render) {
        $old_pass = isset($_REQUEST['old_pass']) ? $_REQUEST['old_pass'] : '';
        $new_pass = isset($_REQUEST['new_pass']) ? $_REQUEST['new_pass'] : '';

        if($this->auth->validate($this->auth->getUserName(), $old_pass) && strlen($new_pass) > 0) {
            $this->db->Execute('UPDATE lylina_users SET pass=? WHERE id=?',
                                array($this->auth->hash($new_pass), $this->auth->getUserId()));
            $this->auth->logout();
            $render->assign('auth', false);
            $render->assign('title', 'Password changed');
            $render->assign('reason', "Your password has been changed. You'll need to log in again to continue.");
            $render->display('auth_fail.tpl');
        } else {
            $render->assign('title', 'There was an error changing your password');
            $render->assign('reason', 'Your current password was incorrect. <a href="admin">Try again</a>.');
            $render->display('auth_fail.tpl');
        }
    }

    function email($render) {
        $new_email = isset($_REQUEST['new_email']) ? $_REQUEST['new_email'] : '';
        $this->db->Execute('UPDATE lylina_users SET email=? WHERE id=?',
                            array($new_email, $this->auth->getUserId()));
        header('Location: admin');
    }
}
