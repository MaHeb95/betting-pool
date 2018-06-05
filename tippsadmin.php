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

foreach (all_users() AS $user) {
    foreach ($md_matches AS $match) {
        $val = strval($_POST[$user['id'].$match['id']]);
        if (trim($val) !== "") {
            admin_bet($user['id'], $match['id'],$val);
            submitted_bet($user['id'], $match['id']);
        }
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
        foreach (all_users() as $row) {
            echo "<th style='text-align: center' colspan='1'>" . $row['username'] . "</th>";
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
        foreach (all_users() as $user) {
                $bet = get_bet($user['id'],$match['id']);
                ?>
                    <td style='text-align: center' colspan='1'>
                        <label for="<?php echo $user['id'].$match['id']; ?>"></label>
                            <input type="number" size="1" class="form-control" name="<?php echo $user['id'].$match['id']; ?>" list="possibleBets" placeholder="<?php echo $bet; ?>" step="1" min="0" max="2" value="">
                                <datalist id="possibleBets">
                                    <option value="0">
                                    <option value="1">
                                    <option value="2">
                                </datalist>
                    </td>
                <?php
        }
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "<div class='col-md-3 col-md-offset-9'>";
    echo "<button onclick='confirmFunction()' type='submit' class='btn btn-primary btn-lg active' name='submit_bets' value='1'>Tipps abgeben!</button>";
    echo "</div>";
    echo "</form>";

    echo "<p></p>";

    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
    echo "<a href='http://$host_domain/tipps.php?season=$seasonmenu&matchday=$matchdaymenu' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>Zurück zur Tippübersicht!</a>";
    ?>

    <script>
        function confirmFunction() {
            alert("Wollen Sie die Tipps endgültig ändern?");
        }
    </script>
</body>
