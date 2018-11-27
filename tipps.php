<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 12:21
 */

ob_start();

//Check Login

require ("view.nologin.php");

//Abfrage der Nutzer ID vom Login
$userid = (int) $_SESSION['userid'];

require ("view.header.php");
require ("view.navbar.php");

require ("config.php");
require ("match.php");
require ("bet.php");
require ("season_bet.php");

$is_admin = (bool) (get_user($userid)['admin']);

$seasonmenu = null;
$matchdaymenu = null;
$betgroupmenu = null;
if (isset($_GET["season"]) && is_numeric($_GET["season"])) {
    $seasonmenu = $_GET["season"];
}
if (isset($_GET['matchday']) && is_numeric($_GET['matchday'])) {
    $matchdaymenu = $_GET['matchday'];
}
if (isset($_GET['betgroup']) && is_numeric($_GET['betgroup'])) {
    $betgroupmenu = $_GET['betgroup'];
}

function save_cookie($season_id=null, $matchday_id=null, $betgroup_id=null) {
    $cookie = $season_id . ':' . $matchday_id . ':' . $betgroup_id;
    setcookie('view_prefs', $cookie, time()+60*60*24*365);
}

function load_cookie() {
    $cookie = isset($_COOKIE['view_prefs']) ? $_COOKIE['view_prefs'] : '';
    if ($cookie) {
        list ($season_id, $matchday_id, $betgroup_id) = explode(':', $cookie);
        if ($season_id == '') {
            $season_id = null;
        }
        if ($matchday_id == '') {
            $matchday_id = null;
        }
        if ($betgroup_id == '') {
            $betgroup_id = null;
        }
        return array($season_id, $matchday_id, $betgroup_id);
    } else {
        return array(null, null, null);
    }
}

if ($seasonmenu === null) {
    list($seasonmenu, $matchdaymenu, $betgroupmenu) = load_cookie();
} else {
    save_cookie($seasonmenu, $matchdaymenu, $betgroupmenu);
}

ob_end_flush();

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

$bettype = get_season_bettype($seasonmenu);
if ($bettype == 'winner') {
    foreach ($md_matches AS $row) {
        if (trim($_POST[$row['id']]) !== "") {
            $val = array('winner' => $_POST[$row['id']]);
            create_bet($userid, $row['id'], $val);
            submitted_bet($userid, $row['id']);
        }
    }
} else {
    foreach ($md_matches AS $row) {
        if (trim($_POST[$row['id'].'_home']) !== "" AND trim($_POST[$row['id'].'_guest']) !== "") {
            $val = array('home' => (int) $_POST[$row['id'].'_home'], 'guest' => (int) $_POST[$row['id'].'_guest']);
            create_bet($userid, $row['id'], $val);
            submitted_bet($userid, $row['id']);
        }
    }
}

$md_season_questions = get_season_questions(get_season_question_ids($seasonmenu));
foreach($md_season_questions AS $row) {
    if (trim($_POST['season_question_bet' . $row['id']]) !== "") {
        $val = $_POST['season_question_bet' . $row['id']];
        create_season_bet($userid, $row['id'], $val);
        submitted_season_bet($userid, $row['id']);
    }
}

foreach (all_users() AS $user) {
    foreach ($md_matches AS $match) {
        check_points($user['id'],$match['id']);
    }
}

foreach (all_users() AS $user) {
    foreach ($md_season_questions AS $season_question) {
        check_season_bet_points($user['id'], $season_question['id']);
    }
}


?>

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

        function autoSubmit_betgroup()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Seasonegory(season) and * accordingly we change the URL in the browser.
                 */
                if (betgroup.selectedIndex === 0) {
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value + '&matchday=' + matchday.options[matchday.selectedIndex].value;
                } else {
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value + '&matchday=' + matchday.options[matchday.selectedIndex].value + '&betgroup=' + betgroup.options[betgroup.selectedIndex].value;
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
<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

    <form class="form selector" id="form" name="form" method="get" action="<?php echo $actual_link; ?>">
        <fieldset>
            <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-md-4">
                        <p class="bg">
                            <label for="season" class="sr-only">Wähle eine Saison</label> <!-- Season SELECTION -->
                            <!--onChange event fired and function autoSubmit() is invoked-->
                            <select class="form-control" id="season" name="season" onchange="autoSubmit_season();">
                                <option value="">-- Wähle eine Saison --</option>
                                <?php
                                $seasons = get_seasons(get_season_ids($userid));
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
                            <div class="col-md-4">
                                <p class="bg">
                                    <label for="matchday" class="sr-only">Wähle einen Spieltag</label>
                                    <select class="form-control" id="matchday" name="matchday"
                                            onchange="autoSubmit_matchday();">
                                        <option value="">-- Wähle einen Spieltag --</option>
                                        <?php
                                        //POPULATE DROP DOWN WITH Matchday FROM A GIVEN Season
                                        foreach ($matchdays as $row) {
                                            echo("<option value=\"{$row['id']}\" " . ($matchdaymenu == $row['id'] ? "selected" : "") . ">{$row['name']}</option>");
                                        }
                                        ?>
                                    </select>
                                </p>
                            </div>
                            <?php
                            $matches = get_match_ids($matchdaymenu);
                        }?>
                                <div class="col-md-4">
                                    <p class="bg">
                                        <label for="betgroup" class="sr-only">Wähle eine Tipprunde</label> <!-- betgroup SELECTION -->
                                        <!--onChange event fired and function autoSubmit() is invoked-->
                                        <select class="form-control" id="betgroup" name="betgroup" onchange="autoSubmit_betgroup();">
                                            <option value="">-- Wähle eine Tipprunde --</option>
                                            <?php
                                            $betgroups = get_betgroups_from_user($userid, $seasonmenu);

                                            if (count($betgroups) == 1) {
                                                $betgroupmenu = (int) array_values($betgroups)[0]['id'];
                                            }

                                            foreach ($betgroups as $row) {
                                                echo("<option value=\"{$row['id']}\" " . ($betgroupmenu == $row['id'] ? " selected" : "") . ">{$row['name']}</option>");
                                            }

                                            ?>
                                        </select>
                                    </p>
                                </div>
                                <?php
                    }
                    ?>
                </div>
            </div>
        </fieldset>
    </form>
<?php
if ($seasonmenu !== null AND $matchdaymenu === null) {
    ?>

    <form action="<?php echo $actual_link; ?>" enctype="multipart/form-data" method="post">
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th class="d-none d-sm-table-cell">Abgabezeit</th>
                    <th>Wette</th>
                    <th>Punkte</th>
                    <th>Tipp</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach($md_season_questions AS $row) {
                echo "<tr>";
                    //echo "<td>" . $row['id'] . "</td>";
                    echo "<td class='anstoss d-none d-sm-table-cell'>" . date('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
                    echo "<td id='id".$row['id']."' class='saison-tipp'>
                    <div class='saison-tipp-text'>". $row['text'] . "</div>
                    </td>";
                    echo "<td align='center'>" . $row['points'] . "</td>";
                    $closed = is_season_question_started($row['id']);
                    $season_bet = get_season_bet($userid,$row['id']);
                    $submitted = is_season_question_submitted($userid,$row['id']);
                    if ($submitted == FALSE AND $closed == FALSE) { ?>
                        <td><input type="text" class="form-control" name="season_question_bet<?php echo $row['id']; ?>" placeholder="<?php echo $season_bet; ?>"
                        value="<?php echo $season_bet; ?>" <?php if ($closed == TRUE OR $submitted == TRUE) {echo "disabled";} ?>></td>
                    <?php } else {
                        echo "<td>";
                        foreach (get_user_from_betgroup($betgroupmenu) as $user) {
                            echo "<p><b>" . $user['displayname'] . ":</b> " . get_season_bet($user['id'],$row['id']);
                            if (get_season_bet($user['id'],$row['id']) == $row['result'] AND !empty(get_season_bet($user['id'],$row['id']))) { echo " ✓";}
                            echo "</p>";
                        }
                        echo "</td>";
                    }
    echo "</tr>";
} ?>
            </tbody>

        </table>
        <div class='col-md-3 col-md-offset-9'>
            <button onclick='confirmFunction()' type='submit' class='btn btn-primary' name='submit_bets' value='1'>Tipps abgeben!</button>
        </div>
    </form>
    <script>
        function confirmFunction() {
            confirm("Wollen Sie die Tipps endgültig abgeben?");
        }
    </script>
<?php }

if(count($md_matches) > 0){

if (check_matchday_submitted($userid,$matchdaymenu) !== TRUE) { ?>
    <form action="<?php echo $actual_link; ?>" enctype="multipart/form-data" method="post">
        <div class="table-responsive">
            <table class="table tippabgabe">
                <thead class="thead-dark">
                <tr>
                    <th class="d-none d-sm-table-cell">Anstoss</th>
                    <th>Ansetzung</th>
                    <?php
                    $statement = $pdo->prepare("SELECT displayname FROM " . $db_name . ".user WHERE id =" . $userid);
                    $statement->execute();
                    $user = $statement->fetch(PDO::FETCH_ASSOC)['displayname'];
                    echo "<th>" . $user . "</th>";
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($md_matches AS $row) {
                    echo "<tr>";
                    //echo "<td>" . $row['id'] . "</td>";
                    echo "<td class='anstoss d-none d-sm-table-cell'>" . date('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
                    echo "<style>
                            #id" . $row['id'] . ".ansetzung:before {
                                background-image: url(" . $row['home_logo'] . ");
                            }
                      
                            #id" . $row['id'] . ".ansetzung:after {
                                background-image: url(" . $row['guest_logo'] . ");
                            }
                            </style>";
                    echo "<td id='id" . $row['id'] . "' class='ansetzung'>
                                <div class='ansetzung-text'>" . $row['home_team'] . " - " . $row['guest_team'] . "</div>
                              </td>";
                    echo "<td>";
                    //echo "<div>";

                    if ($bettype == 'winner') { //!!! bet INPUT ?>
                        <input type='number' class='form-control tippfeld' name='<?php echo $row['id']; ?>'
                               list='possibleBets' placeholder='' step='1' min='0' max='2' value=''
                            <?php if ($row['start'] < 0) {
                                echo "disabled";
                            } else {
                                echo "required";
                            } ?>>
                    <?php } elseif ($bettype == 'result' OR $bettype == 'result_fulltime') { ?>
                        <div class="input-group" style="max-width:11.2em; margin:auto">
                            <input type='number' class='form-control tippfeld_home' name='<?php echo $row['id']; ?>_home' placeholder='' step='1' min='0' value='' <?php if ($row['start'] < 0) {echo "disabled";} else {echo "required";} ?>>
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="margin-right: -1px; margin-left: -1px; padding-left:0.5em; padding-right: 0.5em">:</span>
                            </div>
                            <input type='number' class='form-control tippfeld_guest' name='<?php echo $row['id']; ?>_guest' placeholder='' step='1' min='0' value='' <?php if ($row['start'] < 0) {echo "disabled";} else {echo "required";} ?>>
                        </div>
                    <?php }

                    //echo "</div>";
                    echo "</td>";
                    echo "</tr>";
                } ?>
                </tbody>
            </table>
        </div>
        <div class='col-md-3 col-md-offset-9'>
            <button onclick='confirmFunction()' type='submit' class='btn btn-primary' name='submit_bets' value='1'>Tipps abgeben!</button>
        </div>
    </form>
    <script>
        function confirmFunction() {
            confirm("Wollen Sie die Tipps endgültig abgeben?");
        }
    </script>

<?php }
else {
    ?>
    <div class="table-responsive">
    <table class="table">
    <thead class="thead-dark">
    <tr>
        <th class="d-none d-sm-table-cell">Anstoss</th>
        <th>Ansetzung</th>
        <th>Ergebnis</th>
        <?php
        foreach (get_user_from_betgroup($betgroupmenu) as $row) {
            echo "<th>" . $row['displayname'] . "</th>";
        }
        ?>
    </tr>
    </thead>
    <div>
    <?php
    foreach ($md_matches AS $row) {
        echo "<tr>";
        //echo "<td>" . $row['id'] . "</td>";
        echo "<td class='anstoss d-none d-sm-table-cell'>" . date('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
        echo "<style>
            #id" . $row['id'] . ".ansetzung:before {
                background-image: url(" . $row['home_logo'] . ");
            }
      
            #id" . $row['id'] . ".ansetzung:after {
                background-image: url(" . $row['guest_logo'] . ");
            }
            </style>";
        echo "<td id='id" . $row['id'] . "' class='ansetzung'>
                <div class='ansetzung-text'>" . $row['home_team'] . " - " . $row['guest_team'] . "</div>
              </td>";
        if ($row['home_goals'] !== null) {
            echo "<td>" . $row['home_goals'] . " : " . $row['guest_goals'];
            if ($bettype == 'winner') {
                echo "|  <strong>" . $row['winner'] . "</strong>";
            }
            echo "</td>";
        } else {
            echo "<td>- : -</td>";
        }

        foreach (get_user_from_betgroup($betgroupmenu) as $user) {
            $bet = get_bet($user['id'], $row['id']);
            if ($bet === NULL) {
                echo "<td class='tippanzeige'>-</td>";
            } else {
                if ($bettype == 'winner') {
                    $betstring = '' . $bet['winner'];
                } else {
                    $betstring = '' . $bet['home'] . ':' . $bet['guest'];
                }

                $bet_result = check_bet($row['winner'], $row['home_goals'], $row['guest_goals'], $bet);

                if ($row['winner'] === NULL) {
                    echo "<td class='tippanzeige'>" . $betstring . "</td>";
                } elseif ($bet_result == 'correct') {
                    echo "<td class='tippanzeige'><strong>" . $betstring . " ✓ (3)</strong></td>";
                } elseif ($bet_result == 'difference') {
                    echo "<td class='tippanzeige'><strong>" . $betstring . " ✓ (1)</strong></td>";
                } elseif ($bet_result == 'tendency') {
                    echo "<td class='tippanzeige'><strong>" . $betstring . " ✓ (1)</strong></td>";
                } else {
                    echo "<td class='tippanzeige'>" . $betstring . "</td>";
                }
            }

        }
        echo "</tr>";
    }
    echo "<tr class='active' >";
    echo "<td class='summary d-none d-sm-table-cell'></td>";
    echo "<td class='summary' colspan='2'>Punkte Spieltag:</td>";
    foreach (get_user_from_betgroup($betgroupmenu) as $user) {
        echo "<td><strong>" . sum_points_matchday($user['id'], $matchdaymenu) . "</strong></td>";
    }
    echo "</tr>";

    echo "<tr class='active' >";
    echo "<td class='summary d-none d-sm-table-cell'></td>";
    echo "<td class='summary' colspan='2'>Punkte Gesamt:</td>";

    $user_ids = [];
    $total_points = [];
    foreach (get_user_from_betgroup($betgroupmenu) as $user) {
        $user_ids[] = $user['id'];
        $total_points[] = sum_points_all_at_matchday($user['id'], $matchdaymenu, $seasonmenu);
        echo "<td><strong>" . sum_points_all_at_matchday($user['id'], $matchdaymenu, $seasonmenu) . "</strong></td>";
    }
    echo "</tr>";

    // sort user ID's and total points by points descending
    array_multisort($total_points, SORT_DESC, $user_ids);
    // calculate the ranking
    $ranks = [];
    $last_score = null;
    $rows = 0;
    foreach ($user_ids as $index => $id) {
        $rows++;
        if ($last_score !== $total_points[$index]) {
            $last_score = $total_points[$index];
            $rank = $rows;
        }
        $ranks[$id] = $rank;
    }

    // output the ranking
    echo "<tr class='active' >";
    echo "<td class='summary d-none d-sm-table-cell'></td>";
    echo "<td class='summary' colspan='2'>Platz:</td>";

    foreach (get_user_from_betgroup($betgroupmenu) as $user) {
        echo "<td><strong>" . $ranks[$user['id']] . "</strong></td>";
    }
    echo "</tr>";

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    echo"<div>";
    if ($is_admin) {
        echo '&nbsp;&nbsp;&nbsp;';
        echo "<a href='tippsadmin.php?season=$seasonmenu&matchday=$matchdaymenu&betgroup=$betgroupmenu' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>Tipps nachtragen</a>";
    }

    echo '&nbsp;&nbsp;&nbsp;';
    echo "<a href='http://$host_domain/create_pdf.php?season=$seasonmenu&matchday=$matchdaymenu&betgroup=$betgroupmenu&user=$userid' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>Drucken</a>";


}
}
elseif(count($md_matches) == 0 && $md_matches !== null) {
    echo "<p class='lead'><em>Keine Spiele gefunden.</em></p>";

}


require('view.footer.php');