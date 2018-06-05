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

if (trim($_POST["new_season_name"]) !== "") {
    create_season(trim($_POST["new_season_name"]));
}

if (trim($_POST["new_matchday_name"]) !== "") {
    create_matchday($seasonmenu, trim($_POST["new_matchday_name"]));
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
    echo "<a href='http://$host_domain/create_pdf_spielplan.php?season=$seasonmenu&matchday=$matchdaymenu' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>Drucken</a>";
    if($is_admin) {
        echo '&nbsp;&nbsp;&nbsp;';
        echo "<button type='submit' value='Update' name='update' class='btn btn-primary btn-lg active'>Update</button>";
    }
    echo "</form>";
    if (isset($_POST['update'])) {
        foreach (get_match_ids($matchdaymenu) as $id) {
            update_match($id);
        }
    }

}
elseif(count($md_matches) == 0 && $md_matches !== null) {
    echo "<p class='lead'><em>Keine Spiele gefunden.</em></p>";
}?>

<?php
if ($is_admin) {
    if ($seasonmenu === null) {
        ?>

        <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <label for="inputurl">Neue Saison</label>
                <input type="text" class="form-control" name="new_season_name" placeholder="Saison Name"
                       value="<?php echo $url; ?>">
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <?php
    }

    if ($seasonmenu !== null AND $matchdaymenu === null) {
        ?>

        <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <label for="inputurl">Neuer Spieltag</label>
                <input type="text" class="form-control" name="new_matchday_name" placeholder="Spieltag Name"
                       value="<?php echo $url; ?>">
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <?php
    }

    if ($md_matches !== null) {
        ?>

        <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <label for="inputurl">Neues Spiel</label>
                <input type="text" class="form-control" name="inputurl" placeholder="Spiel URL"
                       value="<?php echo $url; ?>">
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <?php
    }
}
?>

</body>
</html>