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
            $statement->bindValue(':bet', json_encode($bet), PDO::PARAM_STR);
            $result = $statement->execute();
        } else {
            $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET bet=:bet, time=NOW() WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
            $statement->bindValue(':bet', json_encode($bet), PDO::PARAM_STR);
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
        $statement->bindValue(':bet', json_encode($bet), PDO::PARAM_STR);
        $result = $statement->execute();
    } else {
        $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET bet=:bet, time=NOW() WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
        $statement->bindValue(':bet', json_encode($bet), PDO::PARAM_STR);
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
        $bet = json_decode($statement->fetch(PDO::FETCH_ASSOC)['bet'], true);

        return $bet;
    } else {
        return NULL;
    }
}

function check_bet($winner, $home_goals, $guest_goals, $bet) {
    if (array_key_exists('winner', $bet)) {
        if ($winner == $bet['winner']) {
            return 'tendency';
        } else {
            return 'wrong';
        }
    } elseif (array_key_exists('home', $bet) AND array_key_exists('guest', $bet)) {
        if ($home_goals == $bet['home'] AND $guest_goals == $bet['guest']) {
            return 'correct';
        } elseif (($home_goals - $guest_goals) == ($bet['home'] - $bet['guest'])) {
            return 'difference';
        } elseif (sign($home_goals - $guest_goals) == sign($bet['home'] - $bet['guest'])) {
            return 'tendency';
        } else {
            return 'wrong';
        }
    } else {
        return NULL;
    }
}

function sign($n) {
    return ($n > 0) - ($n < 0);
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
        $statement = $pdo->prepare("SELECT winner, home_goals, guest_goals FROM ".$db_name.".match  WHERE id ='".$match_id."'");
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC);
        $winner = (int) $val['winner'];
        $home_goals = (int) $val['home_goals'];
        $guest_goals = (int) $val['guest_goals'];

        $bet = get_bet($user_id, $match_id);

        if ($val == null) {
            return false;
        }

        $statement = $pdo->prepare("SELECT settings FROM ".$db_name.".season INNER JOIN matchday ON season.id = matchday.season_id INNER JOIN `match` ON matchday.id = `match`.matchday_id WHERE `match`.id='".$match_id."'");
        $statement->execute();
        $settings = json_decode($statement->fetch(PDO::FETCH_ASSOC)['settings'], true);

        switch (check_bet($winner, $home_goals, $guest_goals, $bet)) {
            case 'correct':
                if (array_key_exists('correct', $settings)) {
                    $points = $settings['correct'];
                }
                break;
            case 'difference':
                if (array_key_exists('difference', $settings)) {
                    $points = $settings['difference'];
                }
                break;
            case 'tendency':
                if (array_key_exists('tendency', $settings)) {
                    $points = $settings['tendency'];
                }
                break;
            case 'wrong':
                $points = 0;
                break;
            default:
                return false;
        }

        $statement = $pdo->prepare("UPDATE ".$db_name.".bet SET points=:points WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
        $statement->bindValue(':points', $points, PDO::PARAM_INT);
        $result = $statement->execute();
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

function sum_points_all($user_id, $season_id=NULL) {
    require ("config.php");

    if ($season_id !== NULL) {
        $statement = $pdo->prepare("SELECT sum(bet.points) FROM bet INNER JOIN `match` ON bet.match_id = `match`.id INNER JOIN matchday ON `match`.matchday_id = matchday.id WHERE `matchday`.season_id = :season_id AND user_id =" . $user_id);
        $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC)['sum(bet.points)'];
    } else {
        $statement = $pdo->prepare("SELECT sum(points) FROM bet WHERE user_id =" . $user_id);
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC)['sum(points)'];
    }
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

function get_user_from_betgroup($betgroup_id) {
    require("config.php");

    $users = [];

    $statement = $pdo->prepare("SELECT user_id FROM ".$db_name.".betgroup_user WHERE betgroup_id=:betgroup_id");
    $statement->bindValue(':betgroup_id', $betgroup_id, PDO::PARAM_INT);
    $statement->execute();

    $user_ids = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $user) {
        $user_ids[] = $user['user_id'];
    }

    foreach ($user_ids as $id) {
        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".user WHERE id = :id");
        $statement->execute(array('id' => $id));
        $users[$id] = $statement->fetch(PDO::FETCH_ASSOC);

    }

    return $users;
}



function create_betgroup($name) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO ".$db_name.".betgroup (name) VALUES (:name)");
    $statement->bindValue(':name', $name, PDO::PARAM_STR);
    $result = $statement->execute();

    return $result;
}

function delete_betgroup($betgroup_id) {
    require("config.php");

    $statement = $pdo->prepare("DELETE FROM ".$db_name.".betgroup WHERE id=:id");
    $statement->bindValue(':id', $betgroup_id, PDO::PARAM_INT);
    return $statement->execute();;
}

function get_betgroup_ids() {
    require("config.php");

    $statement = $pdo->prepare("SELECT id FROM ".$db_name.".betgroup ORDER BY id ASC, name ASC");
    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $betgroup) {
        $id_list[] = $betgroup['id'];
    }

    return $id_list;
}

function get_betgroups($ids) {
    require("config.php");

    $betgroups = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".betgroup WHERE id = :id");
        $statement->execute(array('id' => $id));
        $betgroups[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $betgroups;
}

function get_betgroups_from_user($user_id, $season_id=NULL) {
    require("config.php");

    $betgroups = [];

    if ($season_id !== NULL) {
        $statement = $pdo->prepare("SELECT betgroup_user.betgroup_id FROM ".$db_name.".betgroup_user INNER JOIN betgroup_season ON betgroup_user.betgroup_id=betgroup_season.betgroup_id WHERE user_id=:user_id AND season_id=:season_id");
        $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    } else {
        $statement = $pdo->prepare("SELECT betgroup_id FROM ".$db_name.".betgroup_user WHERE user_id=:user_id");
        $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    }
    $statement->execute();

    $betgroup_ids = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $betgroup) {
        $betgroup_ids[] = $betgroup['betgroup_id'];
    }

    foreach ($betgroup_ids as $id) {
        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".betgroup WHERE id = :id");
        $statement->execute(array('id' => $id));
        $betgroups[$id] = $statement->fetch(PDO::FETCH_ASSOC);

    }

    return $betgroups;
}

function create_betgroup_user($user_id, $betgroup_id) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO ".$db_name.".betgroup_user (user_id, betgroup_id) VALUES (:user_id, :betgroup_id)");
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindValue(':betgroup_id', $betgroup_id, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function delete_betgroup_user($user_id, $betgroup_id) {
    require("config.php");

    $statement = $pdo->prepare("DELETE FROM ".$db_name.".betgroup_user WHERE user_id=:user_id AND betgroup_id=:betgroup_id");
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindValue(':betgroup_id', $betgroup_id, PDO::PARAM_INT);
    return $statement->execute();;
}


function create_betgroup_season($season_id, $betgroup_id) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO ".$db_name.".betgroup_season (season_id, betgroup_id) VALUES (:season_id, :betgroup_id)");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->bindValue(':betgroup_id', $betgroup_id, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function delete_betgroup_season($season_id, $betgroup_id) {
    require("config.php");

    $statement = $pdo->prepare("DELETE FROM ".$db_name.".betgroup_season WHERE season_id=:season_id AND betgroup_id=:betgroup_id");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->bindValue(':betgroup_id', $betgroup_id, PDO::PARAM_INT);
    return $statement->execute();;
}

function check_betgroup_user($user_id, $betgroup_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".betgroup_user WHERE user_id=:user_id AND betgroup_id=:betgroup_id");
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindValue(':betgroup_id', $betgroup_id, PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$row) { $result=NULL; }
    else { $result= TRUE; }

    return $result;
}

function check_betgroup_season($season_id, $betgroup_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".betgroup_season WHERE season_id=:season_id AND betgroup_id=:betgroup_id");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->bindValue(':betgroup_id', $betgroup_id, PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$row) { $result=NULL; }
    else { $result= TRUE; }

    return $result;
}

//var_dump(create_bet(1,2,1));
//var_dump(check_points(1,1));
//var_dump(submitted(1,1));
//var_dump(get_bet(1,1));
//var_dump(check_matchday_submitted(1,1));
//var_dump(sum_points_all_at_matchday(1,3));
//var_dump(all_users());

//$name = "family";
//var_dump(delete_betgroup(2));

//var_dump(get_user_from_betgroup(1));
?>
