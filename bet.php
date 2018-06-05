<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 23:54
 */

function create_bet($user_id, $match_id, $bet) {
    require("config.php");

    $statement = $pdo->prepare("SELECT start_time - NOW() FROM ".$db_name.".match WHERE id='".$match_id."'");
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['start_time - NOW()'];
    $start_time = (int) $val;

    if ($start_time<0) {
        return False;
    }else {

        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".bet WHERE match_id='".$match_id."' AND user_id=".$user_id);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if( ! $row)
        {
            $statement = $pdo->prepare("INSERT INTO ".$db_name.".bet (user_id, match_id, bet, time) VALUES (:user_id, :match_id, :bet, NOW())");
            $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $statement->bindValue(':match_id', $match_id, PDO::PARAM_INT);
            $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
            $result = $statement->execute();
        } else {
            $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET bet=:bet, time=NOW() WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
            $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
            $result = $statement->execute();
        }
    }
    return $result;
}

function admin_bet($user_id, $match_id, $bet) {
    require("config.php");

    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".bet WHERE match_id='".$match_id."' AND user_id=".$user_id);
    $statement->execute();
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if( ! $row)
    {
        $statement = $pdo->prepare("INSERT INTO ".$db_name.".bet (user_id, match_id, bet, time) VALUES (:user_id, :match_id, :bet, NOW())");
        $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $statement->bindValue(':match_id', $match_id, PDO::PARAM_INT);
        $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
        $result = $statement->execute();
    } else {
        $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET bet=:bet, time=NOW() WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
        $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
        $result = $statement->execute();
    }
    return $result;
}

function get_bet($user_id, $match_id) {
    require ("config.php");

    $statement = $pdo->prepare("SELECT submitted FROM ".$db_name.".bet WHERE match_id='".$match_id."' AND user_id=". $user_id);
    $statement->execute();
    $submitted = (bool) ($statement->fetch(PDO::FETCH_ASSOC)['submitted']);

    if ($submitted) {
        $statement = $pdo->prepare("SELECT bet FROM " . $db_name . ".bet WHERE match_id ='" . $match_id . "' AND user_id ='" . $user_id . "'");
        $statement->execute();
        $bet = (int) ($statement->fetch(PDO::FETCH_ASSOC)['bet']);

        return $bet;
    } else {
        return NULL;
    }
}

function check_points($user_id, $match_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT finished FROM ".$db_name.".match WHERE id='".$match_id."'");
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['finished'];
    $finished = (int) $val;
    if ($finished == 0) {
        return False;
    } else {

        $statement = $pdo->prepare("SELECT winner FROM ".$db_name.".match  WHERE id ='".$match_id."'");
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC)['winner'];
        $winner = (int) $val;

        $statement = $pdo->prepare("SELECT bet FROM ".$db_name.".bet  WHERE match_id ='".$match_id."' AND user_id ='".$user_id."'");
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC)['bet'];
        $bet = (int) $val;

        if ($val == null) {
            return false;
        }

        if ($winner == $bet) {
            $points = 1;
            $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET points=:points WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
            $statement->bindValue(':points', $points, PDO::PARAM_INT);
            $result = $statement->execute();
        } else {
            $points = 0;
            $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET points=:points WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
            $statement->bindValue(':points', $points, PDO::PARAM_INT);
            $result = $statement->execute();
        }
    }
    return $result;
}

/*function submitted_matchday($user_id, $matchday) {
    require ("config.php");
    require ("match.php");
    $val = get_match_ids($matchday);
    foreach ($val AS $match_id) {
        submitted($user_id, $match_id);
    }
    return $result;
}*/

function submitted_bet($user_id, $match_id) {
    require ("config.php");

    $submitted = 1;
    $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET submitted=:submitted WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
    $statement->bindValue(':submitted', $submitted, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function check_matchday_submitted($user_id, $matchday) {
    require ("config.php");

    $statement = $pdo->prepare("SELECT `bet`.submitted FROM bet, `match` WHERE `match`.id = bet.match_id AND `match`.matchday_id= '".$matchday."'  AND user_id ='".$user_id."' ORDER BY `bet`.submitted DESC LIMIT 1;");
    $statement->execute();
    return (bool) ($statement->fetch(PDO::FETCH_ASSOC)['submitted']);

}

function sum_points_matchday($user_id, $matchday) {
    require ("config.php");

    $statement = $pdo->prepare("SELECT sum(`bet`.points) FROM bet, `match` WHERE `match`.id = bet.match_id AND `match`.matchday_id= '".$matchday."'  AND user_id ='".$user_id."'");
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['sum(`bet`.points)'];
    $points = (int) $val;

    return $points;
}

function sum_points_all($user_id) {
    require ("config.php");

    $statement = $pdo->prepare("SELECT sum(points) FROM ".$db_name.".bet WHERE user_id=" . $user_id);
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['sum(points)'];
    $points = (int) $val;

    return $points;
}

function sum_points_all_at_matchday($user_id, $matchday) {
    require ("config.php");

    for ($i=$matchday; $i>0; $i--) {

        $statement = $pdo->prepare("SELECT sum(`bet`.points) FROM bet, `match` WHERE `match`.id = bet.match_id AND `match`.matchday_id= '".$i."'  AND user_id ='".$user_id."'");
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC)['sum(`bet`.points)'];
        $points = $points + (int) $val;
    }
    return $points;
}


function get_user($user_id) {
    require ("config.php");

    $statement = $pdo->prepare("SELECT * FROM " . $db_name . ".user WHERE id = ".$user_id);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
}

function all_users() {
    require ("config.php");

    $statement = $pdo->prepare("SELECT * FROM " . $db_name . ".user ");
    $statement->execute();
    $user = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $user;
}

//var_dump(create_bet(1,2,1));
//var_dump(check_points(1,1));
//var_dump(submitted(1,1));
//var_dump(get_bet(1,1));
//var_dump(check_matchday_submitted(1,1));
//var_dump(sum_points_all_at_matchday(1,3));
//var_dump(all_users());
?>
