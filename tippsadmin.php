<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 14.09.17
 * Time: 22:33
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

if (!$is_admin) {
    echo "Admin-Bereich!";
    die;
}

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
    foreach (get_user_from_betgroup($betgroupmenu) AS $user) {
        foreach ($md_matches AS $match) {
            if (trim($_POST[$user['id'].$match['id']]) !== "") {
                $val = array('winner' => $_POST[$user['id'].$match['id']]);
                admin_bet($user['id'], $match['id'],$val);
                submitted_bet($user['id'], $match['id']);
            }
        }
    }
} else {
    foreach (get_user_from_betgroup($betgroupmenu) AS $user) {
        foreach ($md_matches AS $match) {
            if (trim($_POST[$user['id'].$match['id'].'_home']) !== "" AND trim($_POST[$user['id'].$match['id'].'_guest']) !== "") {
                $val = array('home' => (int) $_POST[$user['id'].$match['id'].'_home'], 'guest' => (int) $_POST[$user['id'].$match['id'].'_guest']);
                admin_bet($user['id'], $match['id'],$val);
                submitted_bet($user['id'], $match['id']);
            }
        }
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
                    window.location.href = 'tippsadmin.php';
                } else {
                    window.location.href = 'tippsadmin.php?season=' + season.options[season.selectedIndex].value;
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
                    window.location.href = 'tippsadmin.php?season=' + season.options[season.selectedIndex].value;
                } else {
                    window.location.href = 'tippsadmin.php?season=' + season.options[season.selectedIndex].value + '&matchday=' + matchday.options[matchday.selectedIndex].value;
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

    </script>
</head>
<body>
<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<form action="<?php echo $actual_link; ?>" method="post">
<table class="table">
    <thead class="thead-inverse">
    <tr>
        <th style="text-align: center" colspan="1">Anstoss</th>
        <th style="text-align: center" colspan="3">Ansetzung</th>
        <th style="text-align: center" colspan="1">Ergebnis</th>
        <?php
        $statement = ("SELECT * FROM " . $db_name . ".user ");
        foreach (get_user_from_betgroup($betgroupmenu) as $row) {
            echo "<th style='text-align: center' colspan='1'>" . $row['displayname'] . "</th>";
        }
        ?>
    </tr>
    </thead>
    <tbody>

    <?php
    foreach ($md_matches AS $match) {
        echo "<tr>";
        //echo "<td>" . $row['id'] . "</td>";
        echo "<td style='text-align: center' colspan='1'>" . date('d.m.Y - H:i', strtotime($match['start_time'])) . "</td>";
        echo "<td style='text-align: center' colspan='3'>" . $match['home_team'] . " - " . $match['guest_team'] . "</td>";
        if ($match['home_goals'] !== null) {
            echo "<td style='text-align: center' colspan='1'>" . $match['home_goals'] . " - " . $match['guest_goals'] . "  |  <strong>" . $match['winner'] . "</strong></td>";
        } else {
            echo "<td style='text-align: center' colspan='1'></td>";
        }
        foreach (get_user_from_betgroup($betgroupmenu) as $user) {
            $bet = get_bet($user['id'], $match['id']);
            /*if ($bet === NULL) {
                echo "<td class='tippanzeige'>-</td>";
            } else {*/
                if ($bettype == 'winner') {
                    $betstring = '' . $bet['winner'];
                    ?>
                    <td style='text-align: center' colspan='1'>
                        <label for="<?php echo $user['id'].$match['id']; ?>"></label>
                            <input type="number" size="1" class="form-control" name="<?php echo $user['id'].$match['id']; ?>" list="possibleBets" placeholder="<?php echo $betstring; ?>" step="1" min="0" max="2" value="">
                    </td> <?php
                } else {
                    $betstring_home = '' . $bet['home'];
                    $betstring_guest = '' . $bet['guest'];
                    ?>
                    <td style='text-align: center' colspan='1'>
                    <div class="input-group" style="max-width:11.2em; margin:auto">
                            <input type='number' class='form-control tippfeld_home' name='<?php echo $user['id'].$match['id']; ?>_home' placeholder="<?php echo $betstring_home; ?>"  value=''>
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="margin-right: -1px; margin-left: -1px; padding-left:0.5em; padding-right: 0.5em">:</span>
                            </div>
                            <input type='number' class='form-control tippfeld_guest' name='<?php echo $user['id'].$match['id']; ?>_guest' placeholder="<?php echo $betstring_guest; ?>"  value=''>
                    </div>
                    </td> <?php
                }
            //}   
        }
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "<div class='col-md-3 col-md-offset-9'>";
    echo "<button onclick='return confirmTippabgabe()' type='submit' class='btn btn-primary btn-lg active' name='submit_bets' value='1'>Tipps ändern!</button>";
    echo "</div>";
    echo "</form>";

    echo "<p></p>";

    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
    echo "<a href='tipps.php?season=$seasonmenu&matchday=$matchdaymenu' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>Zurück zur Tippübersicht!</a>";
    ?>

    <script>
        function confirmTippabgabe() {
            return confirm("Wollen Sie die Tipps endgültig abgeben?");
        }
    </script>
</body>
