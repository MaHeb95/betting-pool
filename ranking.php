<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09.06.18
 * Time: 17:20
 */

require ("view.nologin.php");

//Abfrage der Nutzer ID vom Login
$userid = $_SESSION['userid'];

//Ausgabe des internen Startfensters
require ("view.header.php");
require ("view.navbar.php");
require ("config.php");
require ("match.php");
require ("bet.php");



$seasonmenu = null;
$betgroupmenu = null;
if (isset($_GET["season"]) && is_numeric($_GET["season"])) {
    $seasonmenu = $_GET["season"];
}
if (isset($_GET['betgroup']) && is_numeric($_GET['betgroup'])) {
    $betgroupmenu = $_GET['betgroup'];
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
                window.location.href = 'ranking.php';
            } else {
                window.location.href = 'ranking.php?season=' + season.options[season.selectedIndex].value;
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
                window.location.href = 'ranking.php?season=' + season.options[season.selectedIndex].value;
            } else {
                window.location.href = 'ranking.php?season=' + season.options[season.selectedIndex].value + '&betgroup=' + betgroup.options[betgroup.selectedIndex].value;
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
                if ($seasonmenu != '' && is_numeric($seasonmenu)) { ?>
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

## Get points and create ranking

if ($seasonmenu !== NULL AND $betgroupmenu !== NULL) {
    $user_ids = [];
    $user_names = array();
    $total_points = [];
    foreach (get_user_from_betgroup($betgroupmenu) as $user) {
        $user_ids[] = (int) $user['id'];
        $user_names[(int) $user['id']] = $user['displayname'];
        $total_points[] = sum_points_all($user['id'], $seasonmenu);
    }

    function ranks($user_ids, $points) {
        // sort user ID's and total points by points descending
        array_multisort($points, SORT_DESC, $user_ids);

        // calculate the ranking
        $rank = NULL;
        $ranks = [];
        $last_score = null;
        $rows = 0;
        foreach ($user_ids as $index => $id) {
            $rows++;
            if ($last_score !== $points[$index]) {
                $last_score = $points[$index];
                $rank = $rows;
            }
            $ranks[$id] = $rank;
        }

        return $ranks;

    }

    $ranks = ranks($user_ids, $total_points);

    array_multisort($total_points, SORT_DESC, $user_ids);

//array_multisort($total_points,SORT_DESC, $user_ids);

    ## Prepare bar graph

    $data_points_1 = array();

    foreach ($user_ids as $index => $user_id) {
        $data_points_1[] = array("label"=> $user_names[$user_id], "value"=> $total_points[$index]);
    }

    $chart_settings_1 = array("caption" => "Aktuelle Gesamtpunktzahl",
        "xAxisName" => "Spieler",
        "yAxisName" => "Punkte",
        "theme" => "fint",
        "labelDisplay" => "AUTO");

    $chart_1 = array("chart" => $chart_settings_1, "data" => $data_points_1);




    ## Points Line Graph

    $matchdays = get_matchdays(get_matchday_ids($seasonmenu));

    $matchday_names = array();

    foreach ($matchdays as $matchday) {
        $matchday_names[] = array("label" => $matchday['name']);
    }

    $categories_2 = array("category" => $matchday_names);

    $dataset_2 = array();

    foreach ($user_ids as $user_id) {
        $data = array();

        foreach ($matchdays as $matchday) {
            $data[] = array("value" => sum_points_all_at_matchday($user_id, $matchday['id'], $seasonmenu));
        }

        if ($user_id == $userid) {
            $dataset_2[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 3);
        } else {
            $dataset_2[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 1);
        }
    }

    $chart_settings_2 = array("caption" => "Verlauf Punktzahl",
        "xAxisName" => "",
        "yAxisName" => "Punkte",
        "theme" => "fint",
        "showValues" => "0",
        "useEllipsesWhenOverflow" => "1",
        "labelDisplay" => "ROTATE");

    if (count($matchdays) > 8) {
        $chart_settings_2['labelStep'] = (int) count($matchdays) / 8;
    }

    $chart_2 = array("chart" => $chart_settings_2, "dataset" => $dataset_2, "categories" => $categories_2);



    ## Points-at-Matchday Line Graph

    $matchdays = get_matchdays(get_matchday_ids($seasonmenu));

    $matchday_names = array();

    foreach ($matchdays as $matchday) {
        $matchday_names[] = array("label" => $matchday['name']);
    }

    $categories_4 = array("category" => $matchday_names);

    $dataset_4 = array();

    foreach ($user_ids as $user_id) {
        $data = array();

        foreach ($matchdays as $matchday) {
            $data[] = array("value" => sum_points_matchday($user_id, $matchday['id']));
        }

        if ($user_id == $userid) {
            $dataset_4[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 3);
        } else {
            $dataset_4[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 1);
        }
    }

    $chart_settings_4 = array("caption" => "Punktzahlen pro Spieltag",
        "xAxisName" => "",
        "yAxisName" => "Punkte",
        "theme" => "fint",
        "showValues" => "0",
        "useEllipsesWhenOverflow" => "1",
        "labelDisplay" => "ROTATE");

    if (count($matchdays) > 8) {
        $chart_settings_4['labelStep'] = (int) count($matchdays) / 8;
    }

    $chart_4 = array("chart" => $chart_settings_4, "dataset" => $dataset_4, "categories" => $categories_4);




    ## Places Line Graph

    $matchdays = get_matchdays(get_matchday_ids($seasonmenu));

    $matchday_names = array();

    foreach ($matchdays as $matchday) {
        $matchday_names[] = array("label" => $matchday['name']);
    }

    $categories_3 = array("category" => $matchday_names);

    $dataset_3 = array();

    $places = array();
    $points = array();

    foreach ($matchdays as $matchday) {
        $points_md = array();
        $points_id = array();

        foreach ($user_ids as $user_id) {
            $points_id[$user_id] = sum_points_all_at_matchday($user_id, $matchday['id'], $seasonmenu);
            $points_md[] = $points_id[$user_id];
        }

        $points[$matchday['id']] = $points_id;
        $places[$matchday['id']] = ranks($user_ids, $points_md);
    }

    foreach ($user_ids as $user_id) {
        $data = array();

        foreach ($matchdays as $matchday) {
            $data[] = array("value" => $places[$matchday['id']][$user_id]);
        }

        if ($user_id == $userid) {
            $dataset_3[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 3);
        } else {
            $dataset_3[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 1);
        }
    }

    $chart_settings_3 = array("caption" => "Verlauf Platzierung",
        "xAxisName" => "",
        "yAxisName" => "Platzierung",
        "theme" => "fint",
        "showValues" => "0",
        "useEllipsesWhenOverflow" => "1",
        "labelDisplay" => "ROTATE",
        "yAxisMinValue" => "1",
        "yAxisMaxValue" => count($user_ids),
        "yAxisValuesStep" => "1");

    if (count($matchdays) > 8) {
        $chart_settings_3['labelStep'] = (int) count($matchdays) / 8;
    }

    $chart_3 = array("chart" => $chart_settings_3, "dataset" => $dataset_3, "categories" => $categories_3);




    ## Point-Difference-from-Average Line Graph

    $matchdays = get_matchdays(get_matchday_ids($seasonmenu));

    $matchday_names = array();

    foreach ($matchdays as $matchday) {
        $matchday_names[] = array("label" => $matchday['name']);
    }

    $categories_5 = array("category" => $matchday_names);

    $dataset_5 = array();

    foreach ($user_ids as $user_id) {
        $data = array();

        foreach ($matchdays as $matchday) {
            $average = array_sum($points[$matchday['id']])/count($points[$matchday['id']]);
            $data[] = array("value" => $points[$matchday['id']][$user_id] - $average);
        }

        if ($user_id == $userid) {
            $dataset_5[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 3);
        } else {
            $dataset_5[] = array("seriesname" => $user_names[$user_id], "data" => $data, "lineThickness" => 1);
        }
    }

    $chart_settings_5 = array("caption" => "Verlauf Punktzahldifferenz",
        "xAxisName" => "",
        "yAxisName" => "Differenz vom Durchschnitt",
        "theme" => "fint",
        "showValues" => "0",
        "useEllipsesWhenOverflow" => "1",
        "labelDisplay" => "ROTATE");

    if (count($matchdays) > 8) {
        $chart_settings_5['labelStep'] = (int) count($matchdays) / 8;
    }

    $chart_5 = array("chart" => $chart_settings_5, "dataset" => $dataset_5, "categories" => $categories_5);
?>

    <div class="jumbotron">
        <div class="container">
            <div class="align-content-center text-center" style="margin:auto">
                <h1>Rangliste</h1>

                <ul class="list-unstyled">
                    <?php
                    foreach ($user_ids as $index => $user_id) {
                        echo "<li><strong>" . $ranks[$user_id] . ". " . $user_names[$user_id] . "</strong> (". $total_points[$index] ." Punkte)</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="jumbotron">
        <div class="container">
            <div class="align-content-center text-center d-none d-lg-block" style="margin:auto">
                <div id="chart_current_points">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-none d-sm-block d-lg-none" style="margin:auto">
                <div id="chart_current_points_md">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-block d-sm-none" style="margin:auto">
                <div id="chart_current_points_xs">FusionCharts XT will load here!</div>
            </div>
        </div>
    </div>
    <div class="jumbotron">
        <div class="container">
            <div class="align-content-center text-center d-none d-lg-block" style="margin:auto">
                <div id="chart_points_trend">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-none d-sm-block d-lg-none" style="margin:auto">
                <div id="chart_points_trend_md">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-block d-sm-none" style="margin:auto">
                <div id="chart_points_trend_xs">FusionCharts XT will load here!</div>
            </div>
        </div>
    </div>
    <div class="jumbotron">
        <div class="container">
            <div class="align-content-center text-center d-none d-lg-block" style="margin:auto">
                <div id="chart_points_average_trend">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-none d-sm-block d-lg-none" style="margin:auto">
                <div id="chart_points_average_trend_md">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-block d-sm-none" style="margin:auto">
                <div id="chart_points_average_trend_xs">FusionCharts XT will load here!</div>
            </div>
        </div>
    </div>
    <div class="jumbotron">
        <div class="container">
            <div class="align-content-center text-center d-none d-lg-block" style="margin:auto">
                <div id="chart_points_matchday_trend">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-none d-sm-block d-lg-none" style="margin:auto">
                <div id="chart_points_matchday_trend_md">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-block d-sm-none" style="margin:auto">
                <div id="chart_points_matchday_trend_xs">FusionCharts XT will load here!</div>
            </div>
        </div>
    </div>
    <div class="jumbotron">
        <div class="container">
            <div class="align-content-center text-center d-none d-lg-block" style="margin:auto">
                <div id="chart_place_trend">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-none d-sm-block d-lg-none" style="margin:auto">
                <div id="chart_place_trend_md">FusionCharts XT will load here!</div>
            </div>
            <div class="align-content-center text-center d-block d-sm-none" style="margin:auto">
                <div id="chart_place_trend_xs">FusionCharts XT will load here!</div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="fusioncharts/fusioncharts.js"></script>
    <script type="text/javascript" src="fusioncharts/themes/fusioncharts.theme.fint.js"></script>

    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "column2d",
                "renderAt": "chart_current_points",
                "width": "700",
                "height": "500",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_1, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "column2d",
                "renderAt": "chart_current_points_md",
                "width": "500",
                "height": "400",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_1, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "column2d",
                "renderAt": "chart_current_points_xs",
                "width": "300",
                "height": "200",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_1, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>

    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_trend",
                "width": "700",
                "height": "500",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_2, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_trend_md",
                "width": "500",
                "height": "400",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_2, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_trend_xs",
                "width": "300",
                "height": "200",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_2, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>

    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_average_trend",
                "width": "700",
                "height": "500",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_5, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_average_trend_md",
                "width": "500",
                "height": "400",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_5, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_average_trend_xs",
                "width": "300",
                "height": "200",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_5, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>

    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_matchday_trend",
                "width": "700",
                "height": "500",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_4, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_matchday_trend_md",
                "width": "500",
                "height": "400",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_4, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "msline",
                "renderAt": "chart_points_matchday_trend_xs",
                "width": "300",
                "height": "200",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_4, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>

    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "inversemsline",
                "renderAt": "chart_place_trend",
                "width": "700",
                "height": "500",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_3, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "inversemsline",
                "renderAt": "chart_place_trend_md",
                "width": "500",
                "height": "400",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_3, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>
    <script type="text/javascript">
        FusionCharts.ready(function() {
            var revenueChart = new FusionCharts({
                "type": "inversemsline",
                "renderAt": "chart_place_trend_xs",
                "width": "300",
                "height": "200",
                "dataFormat": "json",
                "dataSource": <?php echo json_encode($chart_3, JSON_NUMERIC_CHECK); ?>

            });
            revenueChart.render();
        })
    </script>

<?php
}

require('view.footer.php');