<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 11.12.17
 * Time: 18:13
 */

include("fpdf.php");

//Abfrage der Nutzer ID vom Login
$userid = (int) $_SESSION['userid'];

//Ausgabe des internen Startfensters

require ("config.php");
require ("match.php");
require ("bet.php");


$seasonmenu = null;
$matchdaymenu = null;
if (isset($_GET["season"]) && is_numeric($_GET["season"])) {
    $seasonmenu = $_GET["season"];
}
if (isset($_GET['matchday']) && is_numeric($_GET['matchday'])) {
    $matchdaymenu = $_GET['matchday'];
}

$md_matches = null;
if ($matchdaymenu !== null) {
    $md_matches = get_matches(get_match_ids($matchdaymenu));
    foreach (get_match_ids($matchdaymenu) as $id) {
        $match = $md_matches[$id];
        if (((int)$match['start'] < 0) && (!isset($match['home_goals']) || !isset($match['guest_goals']))) {
            update_match($id);
        }
    }
    $md_matches = get_matches(get_match_ids($matchdaymenu));
}

$pdf = new FPDF(L);

$pdf->AddPage();
$pdf->SetFont('Arial','B',20);
$pdf->SetAutoPageBreak(true,10);

$pdf->Cell(276,15,'',0,1);

//1.Zeile - Überschrift
$pdf->SetFillColor(227,101,4);
$pdf->Cell(110,15,$matchdaymenu . '. Spieltag',1,0,C,1);
$pdf->SetFillColor(229,229,229);
$pdf->Cell(166,15,'',1,1,C,1);

//2.Zeile - Tabellenkopf
$pdf->SetFont('Arial','B',12);
$pdf->Cell(25,10,'Anstoss',1,0,C,0);
$pdf->Cell(85,10,'Ansetzung',1,0,C,0);
$pdf->Cell(20,10,'Ergebnis',1,0,C,0);
$pdf->Cell(1,10,'',1,0,C,0);
//2.Zeile - User
$pdf->SetFont('Arial','B',11);
$statement = ("SELECT * FROM " . $db_name . ".user ");
foreach (all_users() as $row) {
    $pdf->Cell(18, 10, $row['username'], 1, 0, C, 0);
}
$pdf->Cell(1,10,'',1,1,C,0);

//Tippdaten
$pdf->SetFillColor(229,229,229);
foreach ($md_matches AS $match) {
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(25,10,date('d.m. - H:i', strtotime($match['start_time'])),1,0,C,0);
    $pdf->Cell(85,10,utf8_decode($match['home_team']) .  " - " . utf8_decode($match['guest_team']) ,1,0,C,0);
    if ($match['home_goals'] !== null) {
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(13,10,$match['home_goals'] . " - " . $match['guest_goals'],1,0,C,0);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(7,10,$match['winner'],1,0,C,0);
    } else {
        $pdf->Cell(13,10,'',1,0,C,0);
        $pdf->Cell(7,10,'',1,0,C,0);
    }
    $pdf->Cell(1,10,'',1,0,C,0);
    foreach (all_users() as $user) {
        $pdf->SetFont('Arial','',12);
        $bet = get_bet($user['id'], $match['id']);
        $pdf->Cell(18,10,'',1,0,C,0);
    }
    $pdf->Cell(1,10,'',1,1,C,0);
}

// Punkte am Spieltag
$pdf->SetFont('Arial','B',12);
$pdf->Cell(130,10,'Punkte Spieltag:',1,0,R,1);
$pdf->Cell(1,10,'',1,0,C,1);
foreach (all_users() as $user) {
    $pdf->Cell(18,10,'',1,0,C,1);
}
$pdf->Cell(1,10,'',1,1,C,0);

// Punkte Gesamt
$pdf->SetFont('Arial','B',12);
$pdf->Cell(130,10,'Punkte Gesamt:',1,0,R,1);
$pdf->Cell(1,10,'',1,0,C,1);
foreach (all_users() as $user) {
    $user_ids[] = $user['id'];
    $total_points[] = sum_points_all_at_matchday($user['id'],$matchdaymenu);
    $pdf->Cell(18,10,sum_points_all_at_matchday($user['id'],$matchdaymenu),1,0,C,1);
}
$pdf->Cell(1,10,'',1,1,C,0);

// sort user ID's and total points by points descending
array_multisort($total_points,SORT_DESC, $user_ids);
// calculate the ranking
$ranks = [];
$last_score = null;
$rows = 0;
foreach ($user_ids as $index => $id) {
    $rows++;
    if( $last_score !== $total_points[$index] ){
        $last_score = $total_points[$index];
        $rank = $rows;
    }
    $ranks[$id] = $rank;
}

// Platz
$pdf->SetFont('Arial','B',12);
$pdf->Cell(130,10,'Platz:',1,0,R,1);
$pdf->Cell(1,10,'',1,0,C,1);
foreach (all_users() as $user) {
    $pdf->Cell(18,10,$ranks[$user['id']],1,0,C,1);
}
$pdf->Cell(1,10,'',1,1,C,0);


$pdf->Output();

?>