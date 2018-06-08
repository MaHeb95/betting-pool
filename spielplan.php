<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 12:21
 */

//Check Login
require ("view.nologin.php");

//Abfrage der Nutzer ID vom Login
$userid = $_SESSION['userid'];

//Ausgabe des internen Startfensters
require ("view.header.php");
require ("view.navbar.php");

require ("config.php");
require ("match.php");
require ("bet.php"); //for get_user only
require ("season_bet.php");

$is_admin = (bool) (get_user($userid)['admin']);

$seasonmenu = null;
$matchdaymenu = null;
if (isset($_GET["season"]) && is_numeric($_GET["season"])) {
    $seasonmenu = $_GET["season"];
}
if (isset($_GET['matchday']) && is_numeric($_GET['matchday'])) {
    $matchdaymenu = $_GET['matchday'];
}

if (trim($_POST["inputurl"]) !== "") {
    create_match($matchdaymenu, trim($_POST["inputurl"]));
}

if (trim($_POST["new_season_name"]) !== "" && trim($_POST["season_bet_type"]) !== "") {
    create_season(trim($_POST["new_season_name"]), trim($_POST["season_bet_type"]));
}

if (trim($_POST["new_matchday_name"]) !== "") {
    create_matchday($seasonmenu, trim($_POST["new_matchday_name"]));
}

if (trim($_POST["new_season_question_text"]) !== "") {
    $start_time = (trim($_POST["new_season_question_start"]) !== "" ? strtotime($_POST['new_season_question_start']) : NULL);
    create_season_question($seasonmenu, trim($_POST["new_season_question_text"]), $start_time,
        (int) trim($_POST["new_season_question_points"]));
}

$md_matches = null;
if ($matchdaymenu !== null) {
    $md_matches = get_matches(get_match_ids($matchdaymenu));
    foreach (get_match_ids($matchdaymenu) as $id) {
        if (isset($_POST['delete'.$id])) {
            delete_match($id);
        } else {
            $match = $md_matches[$id];
            if (((int)$match['start'] < 0) && (!isset($match['home_goals']) || !isset($match['guest_goals']))) {
                update_match($id);
            }
        }
    }
    $md_matches = get_matches(get_match_ids($matchdaymenu));
}

$md_season_questions = null;
if ($seasonmenu !== null) {
    $md_season_questions = get_season_questions(get_season_question_ids($seasonmenu));
    foreach (get_season_question_ids($seasonmenu) as $id) {
        if (isset($_POST['delete_sq_'.$id])) {
            delete_season_question($id);
        }

        if (isset($_POST['save_sq_'.$id])) {
            update_season_question($id, trim($_POST['sq_result_'.$id]));
        }
    }
    $md_season_questions = get_season_questions(get_season_question_ids($seasonmenu));
}


?>
<html>
<head>
    <script type="text/javascript">
        /**
         * You can have a look at https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/with * for more information on with() function.
         */
        function autoSubmit_season()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Seasonegory(season) and * accordingly we change the URL in the browser.
                 */
                if (season.selectedIndex === 0) {
                    window.location.href = 'spielplan.php';
                } else {
                    window.location.href = 'spielplan.php?season=' + season.options[season.selectedIndex].value;
                }
            }
        }

        function autoSubmit_matchday()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Seasonegory(season) and * accordingly we change the URL in the browser.
                 */
                if (matchday.selectedIndex === 0) {
                    window.location.href = 'spielplan.php?season=' + season.options[season.selectedIndex].value;
                } else {
                    window.location.href = 'spielplan.php?season=' + season.options[season.selectedIndex].value + '&matchday=' + matchday.options[matchday.selectedIndex].value;
                }
            }
        }
    </script>
</head>
<body>
<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<form class="form selector" id="form" name="form" method="get" action="<?php echo $actual_link; ?>">
    <fieldset>
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col col-lg-3">
                    <p class="bg">
                        <!-- <label for="season">Wähle eine Saison</label> <!-- Season SELECTION -->
                        <!--onChange event fired and function autoSubmit() is invoked-->
                        <select class="form-control" id="season" name="season" onchange="autoSubmit_season();">
                        <option value="">-- Wähle eine Saison --</option>
                        <?php
                        $seasons = get_seasons(get_season_ids());
                        foreach ($seasons as $row) {
                            echo ("<option value=\"{$row['id']}\" " . ($seasonmenu == $row['id'] ? " selected" : "") . ">{$row['name']}</option>");
                        }
                        ?>
                        </select>
                    </p>
                </div>
                <?php
                //check whether Season was really selected and Season id is numeric
                if ($seasonmenu != '' && is_numeric($seasonmenu)) {
                    //select sub-categories categories for a given Season id
                    $matchdays = get_matchdays(get_matchday_ids($seasonmenu));
                    if (count($matchdays) > 0) {
                        ?>
                <div class="col col-lg-3">
                    <p class="bg">
                        <!-- <label for="matchday">Wähle einen Spieltag</label> -->
                        <select class="form-control" id="matchday" name="matchday" onchange="autoSubmit_matchday();">
                            <option value="">-- Wähle einen Spieltag --</option>
                            <?php
                            //POPULATE DROP DOWN WITH Matchday FROM A GIVEN Season
                            foreach ($matchdays as $row) {
                                echo ("<option value=\"{$row['id']}\" " . ($matchdaymenu == $row['id'] ? "selected" : "") . ">{$row['name']}</option>");
                            }
                            ?>
                        </select>
                    </p>
                </div>
                <?php
            }
        }
        ?>
            </div>
        </div>
    </fieldset>
</form>

<?php
if(count($md_matches) > 0){
    ?>
<form action="<?php echo $actual_link; ?>" method="post">
<table class="table">
    <thead class="thead-inverse">
    <tr>
        <th>Anstoss</th>
        <th>Ansetzung</th>
        <th>Ergebnis</th>
        <?php if ($is_admin) { ?>
        <th></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
<?php
    foreach($md_matches AS $row) {
        echo "<tr>";
        //echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . date('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
        echo "<style>
            #id".$row['id'].".ansetzung:before {
                background-image: url(". $row['home_logo'] .");
            }
      
            #id".$row['id'].".ansetzung:after {
                background-image: url(". $row['guest_logo'] .");
            }
            </style>";
        echo "<td id='id".$row['id']."' class='ansetzung'>
                <div class='ansetzung-text'>". $row['home_team'] . " - " . $row['guest_team'] . "</div>
              </td>";

        echo "<td align='center'>" . $row['home_goals'] . " - " . $row['guest_goals'] . "</td>";
        $match_id = $row['id'];
        if ($is_admin) {
            echo "<td><button type='submit' class='btn btn-primary' name='delete$match_id' value='1'>Löschen</button></td>";
        }
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</form>";

    echo "<form action='$actual_link' method='post'>";
    echo '&nbsp;&nbsp;&nbsp;';
    echo "<a href='http://$host_domain/create_pdf_spielplan.php?season=$seasonmenu&matchday=$matchdaymenu' class='btn btn-primary btn-lg' role='button' aria-pressed='true'>Drucken</a>";
    if($is_admin) {
        echo '&nbsp;&nbsp;&nbsp;';
        echo "<button type='submit' value='Update' name='update' class='btn btn-primary btn-lg'>Update</button>";
    }
    echo "</form><br/>";
    if (isset($_POST['update'])) {
        foreach (get_match_ids($matchdaymenu) as $id) {
            update_match($id);
        }
    }

}
elseif(count($md_matches) == 0 && $md_matches !== null) {
    echo "<p class='lead'><em>Keine Spiele gefunden.</em></p>";
}

if($seasonmenu !== null AND $matchdaymenu === null AND count($md_season_questions) > 0){
?>
<form action="<?php echo $actual_link; ?>" method="post">
    <table class="table">
        <thead class="thead-inverse">
        <tr>
            <th>Startzeit</th>
            <th>Wette</th>
            <th>Punkte</th>
            <th>Ergebnis</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
<?php
foreach($md_season_questions AS $row) {
    echo "<tr>";
    //echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . date('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
    echo "<td id='id".$row['id']."' class='ansetzung'>
        <div class='ansetzung-text'>". $row['text'] . "</div>
      </td>";

    echo "<td align='center'>" . $row['points'] . "</td>";
    $season_question_id = $row['id'];
    $result = $row['result'];
    if ($is_admin) {
        echo "<td><input type='text' class='form-control' name='sq_result_$season_question_id' placeholder='$result'></td>";
        echo "<td><button type='submit' class='btn btn-primary' name='save_sq_$season_question_id' value='1'>Speichern</button> ";
        echo "<button type='submit' class='btn btn-primary' name='delete_sq_$season_question_id' value='1'>Löschen</button></td>";
    } else {
        echo "<td>$result</td>";
        echo "<td></td>";
    }
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
#echo "<button type='submit' class='btn btn-primary' name='update_season_question' value='1'>Aktualisieren</button>";
echo "</form>";

}?>

<?php
if ($is_admin) {
    if ($seasonmenu === null) {
        ?>
        <div class="jumbotron">
            <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <h1 class="display-5">Neue Saison</h1>
                <div class="form-label-group">
                    <input id="new_season_name" type="text" class="form-control" name="new_season_name" placeholder="Saison Name">
                    <label for="new_season_name">Saison Name</label>
                </div>
                <fieldset class="form-group">
                    <legend for="season_bet_type" class="col-form-label">Wettentyp</legend>
                    <div class="col-sm-12">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="season_bet_type" id="bet_type_1" value="winner" checked>
                            <label class="form-check-label" for="bet_type_1">Gewinner</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="season_bet_type" id="bet_type_2" value="result_fulltime">
                            <label class="form-check-label" for="bet_type_2">Ergebnis nach Regelspielzeit</label>
                       </div>
                       <div class="form-check">
                            <input class="form-check-input" type="radio" name="season_bet_type" id="bet_type_3" value="result">
                           <label class="form-check-label" for="bet_type_3">Endergebnis (nach Verlängerung)</label>
                       </div>
                   </div>
                </fieldset>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            </div>
        </div>
        <?php
    }

    if ($seasonmenu !== null AND $matchdaymenu === null) {
        ?>

        <div class="jumbotron">
        <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <h1 class="display-5">Neuer Spieltag</h1>
                <div class="form-label-group">
                    <input id="new_matchday_name" type="text" class="form-control" name="new_matchday_name" placeholder="Spieltag Name">
                    <label for="new_matchday_name">Spieltag Name</label>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
        </div>

        <div class="jumbotron">
        <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <h1 class="display-5">Neue Saisonwette</h1>
                <div class="form-label-group">
                    <input id="new_season_question_text" type="text" class="form-control"
                           name="new_season_question_text" placeholder="Saisonwette">
                    <label for="new_season_question_text">Bezeichnung Wette</label>
                </div>
                <div class="form-label-group">
                    <input id="new_season_question_start" type="datetime-local" class="form-control"
                           name="new_season_question_start" placeholder="Startzeit">
                    <label for="new_season_question_start">Startzeit (danach ist eine Tippabgabe nicht mehr möglich)</label>
                </div>
                <div class="form-label-group">
                    <input id="new_season_question_points" type="number" step="1" value="1" class="form-control"
                           name="new_season_question_points" placeholder="Punkte">
                    <label for="new_season_question_points">Punkte</label>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
        </div>
        <?php
    }

    if ($md_matches !== null) {
        ?>

        <div class="jumbotron">
        <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <h1 class="display-5">Neues Spiel</h1>
                <div class="form-label-group">
                    <input id="inputurl" type="url" class="form-control"
                           name="inputurl" placeholder="Spiel URL">
                    <label for="inputurl">Spiel URL</label>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
        </div>
        <?php
    }
}
?>

</body>
</html>