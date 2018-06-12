<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 07.06.18
 * Time: 08:34
 */

function create_season_question($season_id, $text="", $start_time=NULL, $points=NULL) {
    require("config.php");

    // check if season_id exists
    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".season WHERE id = :id");
    $statement->execute(array('id' => $season_id));
    $season = $statement->fetch(PDO::FETCH_ASSOC);

    if ($season == false) {
        return $season;
    }

    // write information to database
    $statement = $pdo->prepare("INSERT INTO ".$db_name.".season_question (season_id, text, start_time, points) VALUES (:season_id, :text, FROM_UNIXTIME(:start_time), :points)");
    $result = $statement->execute(array('season_id' => $season_id, 'text' => $text, 'start_time' => $start_time, 'points' => $points));

    return $result;
}

function get_season_question_ids($season_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT id FROM ".$db_name.".season_question WHERE season_id = :season_id");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $season_question) {
        $id_list[] = $season_question['id'];
    }

    return $id_list;
}

function get_season_questions($ids) {
    require("config.php");

    $season_questions = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT *, start_time - NOW() AS start FROM ".$db_name.".season_question WHERE id = :id");
        $statement->execute(array('id' => $id));
        $season_questions[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $season_questions;
}

function delete_season_question($season_question_id) {
    require("config.php");

    $statement = $pdo->prepare("DELETE FROM ".$db_name.".season_question WHERE id=:id");
    $statement->bindValue(':id', $season_question_id, PDO::PARAM_INT);
    return $statement->execute();
}

function update_season_question($season_question_id, $result=NULL) {
    require("config.php");

    // get match information
    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".season_question WHERE id = :id");
    $statement->execute(array('id' => $season_question_id));
    $season_question = $statement->fetch(PDO::FETCH_ASSOC);

    if ($season_question == false) {
        return $season_question;
    }

    $statement = $pdo->prepare("UPDATE ".$db_name.".season_question SET result=:result, finished=1 WHERE id=:id");
    $statement->bindValue(':id', $season_question_id, PDO::PARAM_INT);
    $statement->bindValue(':result', $result, PDO::PARAM_STR);
    $result = $statement->execute();


    //update points
    //foreach(all_users() AS $user) {
    //    check_points($user['id'],$season_question_id);
    //}


    return $result;

    // if finished, also call the function that gives points for this match
}

function create_season_bet($user_id, $season_question_id, $bet) {
    require("config.php");

    $statement = $pdo->prepare("SELECT start_time - NOW() FROM ".$db_name.".season_question WHERE id='".$season_question_id."'");
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['start_time - NOW()'];
    $start_time = (int) $val;
    if ($start_time<0) {
        return False;
    }else {

        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".season_bet WHERE season_question_id='".$season_question_id."' AND user_id=".$user_id);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if( ! $row)
        {
            $statement = $pdo->prepare("INSERT INTO ".$db_name.".season_bet (user_id, season_question_id, bet, time) VALUES (:user_id, :season_question_id, :bet, NOW())");
            $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $statement->bindValue(':season_question_id', $season_question_id, PDO::PARAM_INT);
            $statement->bindValue(':bet', json_encode($bet), PDO::PARAM_STR);
            $result = $statement->execute();
        } else {
            $statement = $pdo->prepare("UPDATE ".$db_name.".season_bet SET bet=:bet, time=NOW() WHERE season_question_id='".$season_question_id."' AND user_id=".$user_id);
            $statement->bindValue(':bet', json_encode($bet), PDO::PARAM_STR);
            $result = $statement->execute();
        }
    }
    return $result;
}


function submitted_season_bet($user_id, $season_question_id) {
    require ("config.php");

    $submitted = 1;
    $statement = $pdo->prepare("UPDATE ".$db_name.".season_bet SET submitted=:submitted WHERE  season_question_id='". $season_question_id."' AND user_id='".$user_id."'");
    $statement->bindValue(':submitted', $submitted, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function get_season_bet($user_id, $season_question_id) {
    require ("config.php");

    /*$statement = $pdo->prepare("SELECT submitted FROM ".$db_name.".season_bet WHERE season_question_id='".$season_question_id."' AND user_id=". $user_id);
    $statement->execute();
    $submitted = (bool) ($statement->fetch(PDO::FETCH_ASSOC)['submitted']);

    if ($submitted) {*/
        $statement = $pdo->prepare("SELECT bet FROM " . $db_name . ".season_bet WHERE season_question_id='".$season_question_id."' AND user_id =" . $user_id);
        $statement->execute();
        $bet = json_decode($statement->fetch(PDO::FETCH_ASSOC)['bet'], true);

        return $bet;
    /*} else {
        return NULL;
    }*/
}

function is_season_question_started($season_question_id) {
    require ("config.php");

    $statement = $pdo->prepare("SELECT start_time - NOW() FROM ".$db_name.".season_question WHERE id='".$season_question_id."'");
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['start_time - NOW()'];
    $start_time = (int) $val;
    if ($start_time<0) {
        return TRUE;
    }else {
        return FALSE;
    }
}

function is_season_question_submitted($user_id, $season_question_id) {
    require ("config.php");

    $statement = $pdo->prepare("SELECT submitted FROM ".$db_name.".season_bet WHERE season_question_id='".$season_question_id."' AND user_id=".$user_id);
    $statement->execute();
    $submitted = $statement->fetch(PDO::FETCH_ASSOC)['submitted'];
    if ($submitted == 1) {
        return TRUE;
    }else {
        return FALSE;
    }
}

function check_season_bet_points($user_id, $season_question_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT result FROM ".$db_name.".season_question WHERE id=".$season_question_id);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC)['result'];

    if (empty($result)) {
        return False;
    } else {

        $statement = $pdo->prepare("SELECT bet FROM ".$db_name.".season_bet  WHERE user_id ='".$user_id."' AND season_question_id=".$season_question_id);
        $statement->execute();
        $bet = $statement->fetch(PDO::FETCH_ASSOC)['bet'];

        if (empty($bet)) {
            return False;
        }

        if (strpos($bet,$result)!==false) {
            $statement = $pdo->prepare("SELECT points FROM ".$db_name.".season_question WHERE id=".$season_question_id);
            $statement->execute();
            $points = $statement->fetch(PDO::FETCH_ASSOC)['points'];

            $statement = $pdo->prepare("UPDATE ".$db_name.".season_bet SET points=:points WHERE user_id ='".$user_id."' AND season_question_id=".$season_question_id);
            $statement->bindValue(':points', $points, PDO::PARAM_INT);
            $res = $statement->execute();

            return $res;
        } else {
            return False;
        }
    }
}




