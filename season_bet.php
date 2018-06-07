<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 07.06.18
 * Time: 08:34
 */

function create_season_question($season_id, $text="", $start_time=NULL, $points=NULL) {
    require("config.php");

    // check if matchday_id exists
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