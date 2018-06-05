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
$userid = (int) $_SESSION['userid'];

//Ausgabe des internen Startfensters
require ("view.header.php");
require ("view.navbar.php");

require ("config.php");
require ("match.php");
require ("bet.php");

$is_admin = (bool) (get_user($userid)['admin']);

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
foreach ($md_matches AS $row) {
    $val = strval($_POST[$row['id']]);
    if (trim($val) !== "") {
        create_bet($userid, $row['id'],$val);
        submitted_bet($userid, $row['id']);
    }
}

foreach (all_users() AS $user) {
    foreach ($md_matches AS $match) {
            check_points($user['id'],$match['id']);
    }
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
                    window.location.href = 'tipps.php';
                } else {
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value;
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
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value;
                } else {
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value + '&matchday=' + matchday.options[matchday.selectedIndex].value;
                }
            }
        }
        function to_tippsadmin()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Seasonegory(season) and * accordingly we change the URL in the browser.
                 */
                if (matchday.selectedIndex === 0) {
                    window.location.href = 'tippsadmin.php?season=' + season.options[season.selectedIndex].value;
                } else {
                    window.location.href = 'tippsadmin.php?season=' + season.options[season.selectedIndex].value + '&matchday=' + matchday.options[matchday.selectedIndex].value;
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

if (check_matchday_submitted($userid,$matchdaymenu) !== TRUE) {?>
<form action="<?php echo $actual_link; ?>" method="post">
    <div class="table-responsive">
        <table class="table tippabgabe">
            <thead class="thead-inverse">
                <tr>
                    <th class="hidden-xs-down">Anstoss</th>
                    <th>Ansetzung</th>
                    <?php
                    $statement = $pdo->prepare("SELECT username FROM " . $db_name . ".user WHERE id =" . $userid);
                    $statement->execute();
                    $user = $statement->fetch(PDO::FETCH_ASSOC)['username'];
                    echo "<th>" . $user . "</th>";
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($md_matches AS $row) {
                echo "<tr>";
                //echo "<td>" . $row['id'] . "</td>";
                echo "<td class='anstoss hidden-xs-down'>" . date('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
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
                echo "<td>" ?>

                <input type="number" class="form-control tippfeld" name="<?php echo $row['id']; ?>" list="possibleBets" placeholder="" step="1" min="0" max="2" value=""
                    <?php if ($row['start'] < 0) {echo "disabled";}?>>
                    <?php //!!! bet INPUT
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
        echo "</table>";
    echo "</div>";
    echo "<div class='col-md-3 col-md-offset-9'>";
        echo "<button onclick='confirmFunction()' type='submit' class='btn btn-primary' name='submit_bets' value='1'>Tipps abgeben!</button>";
    echo "</div>";
echo "</form>";

?>

<script>
    function confirmFunction() {
        alert("Wollen Sie die Tipps endgültig abgeben?");
    }
</script>  <?php

}
else {
?>
<div class="table-responsive">
<table class="table">
    <thead class="thead-inverse">
    <tr>
        <th>Anstoss</th>
        <th>Ansetzung</th>
        <th>Ergebnis</th>
        <?php
        $statement = ("SELECT * FROM " . $db_name . ".user ");
        foreach (all_users() as $row) {
            echo "<th>" . $row['username'] . "</th>";
        }
        ?>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($md_matches AS $row) {
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
        if ($match['home_goals'] !== null) {
            echo "<td>" . $row['home_goals'] . " - " . $row['guest_goals'] . "  |  <strong>" . $row['winner'] . "</strong></td>";
        } else {
            echo "<td></td>";
        }

        foreach (all_users() as $user) {
            $bet = get_bet($user['id'],$row['id']);
            if ($bet === NULL){
                echo "<td>-</td>";
            } else {
                if ($row['winner'] === NULL) {
                    echo "<td>" . $bet . "</td>";
                } elseif ($bet == $row['winner']) {
                    echo "<td><strong>" . $bet . " ✓</strong></td>";
                } else {
                    echo "<td>" . $bet . "</td>";
                }
            }

        }
        echo "</tr>";
    }
    echo "<tr class='active' >";
    echo "<td class='summary' colspan='3'>Punkte Spieltag:</td>";
        foreach (all_users() as $user) {
            echo "<td><strong>" . sum_points_matchday($user['id'],$matchdaymenu) . "</strong></td>";
        }
    echo "</tr>";

    echo "<tr class='active' >";
    echo "<td class='summary' colspan='3'>Punkte Gesamt:</td>";

    $user_ids = [];
    $total_points = [];
    foreach (all_users() as $user) {
        $user_ids[] = $user['id'];
        $total_points[] = sum_points_all_at_matchday($user['id'],$matchdaymenu);
        echo "<td><strong>" . sum_points_all_at_matchday($user['id'],$matchdaymenu) . "</strong></td>";
    }
    echo "</tr>";

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

    // output the ranking
    echo "<tr class='active' >";
    echo "<td class='summary' colspan='3'>Platz:</td>";

    foreach (all_users() as $user) {
        echo "<td><strong>" . $ranks[$user['id']] . "</strong></td>";
    }
    echo "</tr>";

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    if ($is_admin) {
        echo '&nbsp;&nbsp;&nbsp;';
        echo "<a href='http://$host_domain/tippsadmin.php?season=$seasonmenu&matchday=$matchdaymenu' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>Tipps nachtragen</a>";
    }

    echo '&nbsp;&nbsp;&nbsp;';
    echo "<a href='http://$host_domain/create_pdf.php?season=$seasonmenu&matchday=$matchdaymenu' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>Drucken</a>";
}
}
elseif(count($md_matches) == 0 && $md_matches !== null) {
    echo "<p class='lead'><em>Keine Spiele gefunden.</em></p>";
}



?>

</body>
</html>