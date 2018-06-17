<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17.06.18
 * Time: 11:49
 */

function generate_random_token() {
    $length = 256;

    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    }
    if (function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
    return NULL;
}

function store_token_for_user($user_id, $token) {
    require(__DIR__ . '/../config.php');

    $statement = $pdo->prepare("INSERT INTO auth_token (user_id, token) VALUES (:user_id, :token)");
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindValue(':token', $token, PDO::PARAM_STR);
    $statement->execute();

    return (int) $pdo->lastInsertId();
}

function fetch_token($user_id, $auth_id) {
    require(__DIR__ . '/../config.php');

    $statement = $pdo->prepare("SELECT token FROM auth_token WHERE id = :auth_id AND user_id = :user_id");
    $statement->bindValue(':auth_id', $auth_id, PDO::PARAM_INT);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    $token = $statement->fetch(PDO::FETCH_ASSOC)['token'];
    return $token;
}

function destroy_token($user_id, $auth_id) {
    require(__DIR__ . '/../config.php');

    $statement = $pdo->prepare("DELETE FROM auth_token WHERE id = :auth_id AND user_id = :user_id");
    $statement->bindValue(':auth_id', $auth_id, PDO::PARAM_INT);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    return $statement->execute();
}

function destroy_current_token() {
    $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';
    if ($cookie) {
        list ($user_id, $auth_id, $token, $mac) = explode(':', $cookie);

        destroy_token($user_id, $auth_id);

        delete_remember_cookie();
    }
}

function delete_remember_cookie() {
    setcookie('rememberme', '', time() - 3600);
}

function persistent_login($user_id) {
    require(__DIR__ . '/../config.php');

    $token = generate_random_token(); // generate a token, should be 128 - 256 bit
    $auth_id = store_token_for_user($user_id, $token);
    $cookie = $user_id . ':' . $auth_id . ':' . $token;
    $mac = hash_hmac('sha256', $cookie, SECRET_KEY);
    $cookie .= ':' . $mac;
    setcookie('rememberme', $cookie, time()+60*60*24*365);
}

function remember_me() {
    require(__DIR__ . '/../config.php');

    $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';
    if ($cookie) {
        list ($user_id, $auth_id, $token, $mac) = explode(':', $cookie);
        if (!hash_equals(hash_hmac('sha256', $user_id . ':' . $auth_id . ':' . $token, SECRET_KEY), $mac)) {
            return false;
        }
        $usertoken = fetch_token($user_id, $auth_id);
        if (hash_equals($usertoken, $token)) {
            log_user_in($user_id);
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function log_user_in($user_id) {
    $_SESSION['userid'] = $user_id;
}