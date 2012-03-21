<?php

// lylina feed aggregator
// Copyright (C) 2011-2012 Robert Leith

class ForgotPassword {
    private $db;
    private $auth;

    function __construct() {
        global $db;
        global $auth;
        $this->db = $db;
        $this->auth = $auth;
    }

    private function generateResetKey($id) {
        $keysource = "abcdefghijklmnopqrstuvwxyz0123456789";
        $key = "";
        for($i = 0; $i < 40; $i++) {
            $key .= $keysource[mt_rand(0, 35)];
        }

        return $key;
    }

    private function generateResetEmail($login, $key) {
        $url = "http://{$_SERVER['SERVER_NAME']}";
        $url .= substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/'));
        $url .= "/resetPassword?key=$key";
        return <<<EOT
$login, a password reset was requested. Please visit the following link in order to reset your password.

$url

This link will be valid for one hour.
EOT;
    }

    function render() {
        global $base_config;

        require_once('lib/recaptcha/recaptchalib.php');

        $render = new Render();

        if(isset($_POST['user']) && strlen($_POST['user']) > 0) {
            $user = $this->db->getRow('SELECT id,login,email FROM lylina_users
                                       WHERE login = ? OR email = ?', 
                                      array($_POST['user'], $_POST['user']));
            $recaptcha = recaptcha_check_answer($base_config['recaptcha']['private'],
                                                $_SERVER["REMOTE_ADDR"],
                                                $_POST["recaptcha_challenge_field"],
                                                $_POST["recaptcha_response_field"]);

            // Check inputs and send email if valid, otherwise show error
            if(!$recaptcha->is_valid) {
                $render->assign('error', 'Incorrect CAPTCHA');
            } else if($user === false || count($user) == 0) {
                $render->assign('error', 'Unknown username or e-mail');
            } else {
                $key = $this->generateResetKey($user['id']);
                $this->db->Execute("INSERT INTO lylina_passwordreset
                                    (`user_id`, `key`,`valid_until`)
                                    VALUES(?, ?, ADDTIME(NOW(), '1:0:0'))",
                                    array($user['id'], $key));
                error_log("Sending reset email to: " . $user['email']);
                $email_text = $this->generateResetEmail($user['login'], $key);
                error_log("Text: $email_text");
                $sent = mail($user['email'], "Lylina Password Reset", $email_text, "From: admin@lylina.com");
                $render->assign('success', true);
            }
        }

        $render->assign('recaptcha', recaptcha_get_html($base_config['recaptcha']['public']));

        $render->assign('title', 'Forgot Password');
        $render->display('forgot_password.tpl');
    }
}
