<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 04.09.17
 * Time: 20:02
 */

include 'vendor/autoload.php';


function create_season($name, $bet_type, $settings, $start_time=NULL) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO ".$db_name.".season (name, bet_type, settings, start_time) VALUES (:name, :bet_type, :settings, FROM_UNIXTIME(:start_time))");
    $statement->bindValue(':name', $name, PDO::PARAM_STR);
    $statement->bindValue(':bet_type', $bet_type, PDO::PARAM_STR);
    $statement->bindValue(':settings', json_encode($settings), PDO::PARAM_STR);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function update_season_start_time($season_id) {
    require("config.php");

    $statement = $pdo->prepare(
        "SELECT start_time FROM ".$db_name.".matchday 
         WHERE (season_id = :season_id AND start_time IS NOT NULL)
         ORDER BY start_time ASC
         LIMIT 1");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->execute();
    $start_time = $statement->fetch(PDO::FETCH_ASSOC)['start_time'];

    if ($start_time !== NULL) {
        $start_time = strtotime($start_time);
    }

    $statement = $pdo->prepare("UPDATE ".$db_name.".season SET start_time=FROM_UNIXTIME(:start_time) WHERE id=:id");
    $statement->bindValue(':id', $season_id, PDO::PARAM_INT);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $statement->execute();
}

function get_season_ids($userid=NULL) {
    require("config.php");

    if ($userid !== NULL) {
        $statement = $pdo->prepare("SELECT id FROM ".$db_name.".season INNER JOIN betgroup_season ON season.id=betgroup_season.season_id INNER JOIN betgroup_user ON betgroup_season.betgroup_id=betgroup_user.betgroup_id WHERE user_id=:user_id");
        $statement->bindValue(':user_id', $userid, PDO::PARAM_INT);

    } else {
        $statement = $pdo->prepare("SELECT id FROM ".$db_name.".season ORDER BY start_time ASC, name ASC");
    }

    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $season) {
        $id_list[] = $season['id'];
    }

    return $id_list;
}

function get_seasons($ids) {
    require("config.php");

    $seasons = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".season WHERE id = :id");
        $statement->execute(array('id' => $id));
        $seasons[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $seasons;
}

function get_seasonname($id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT name FROM ".$db_name.".season WHERE id =".$id);
    $statement->execute();
    $seasonname = $statement->fetch(PDO::FETCH_ASSOC);

    return $seasonname;
}

function all_seasons() {
    require ("config.php");

    $statement = $pdo->prepare("SELECT * FROM " . $db_name . ".season ");
    $statement->execute();
    $seasons = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $seasons;
}

function get_season_bettype($id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT bet_type FROM ".$db_name.".season WHERE id='".$id."'");
    $statement->execute();
    $bettype = $statement->fetch(PDO::FETCH_ASSOC)['bet_type'];


    return $bettype;
}

function create_matchday($season_id, $name, $start_time=NULL) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO ".$db_name.".matchday (season_id, name, start_time) VALUES (:season_id, :name, FROM_UNIXTIME(:start_time))");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->bindValue(':name', $name, PDO::PARAM_STR);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function update_matchday_start_time($matchday_id) {
    require("config.php");

    $statement = $pdo->prepare(
        "SELECT start_time FROM ".$db_name.".match 
         WHERE (matchday_id = :matchday_id AND start_time IS NOT NULL)
         ORDER BY start_time ASC
         LIMIT 1");
    $statement->bindValue(':matchday_id', $matchday_id, PDO::PARAM_INT);
    $statement->execute();
    $start_time = $statement->fetch(PDO::FETCH_ASSOC)['start_time'];

    if ($start_time !== NULL) {
        $start_time = strtotime($start_time);
    }

    $statement = $pdo->prepare("UPDATE ".$db_name.".matchday SET start_time=FROM_UNIXTIME(:start_time) WHERE id=:id");
    $statement->bindValue(':id', $matchday_id, PDO::PARAM_INT);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $statement->execute();
}

function get_matchday_ids($season_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT id FROM ".$db_name.".matchday WHERE season_id = :season_id
        ORDER BY start_time ASC, name ASC");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $matchday) {
        $id_list[] = $matchday['id'];
    }

    return $id_list;
}

function get_matchdays($ids) {
    require("config.php");

    $matchdays = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".matchday WHERE id = :id");
        $statement->execute(array('id' => $id));
        $matchdays[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $matchdays;
}

function create_match($matchday_id, $url=NULL, $home_team=NULL, $home_logo=NULL, $guest_team=NULL, $guest_logo=NULL, $start_time=NULL) {
    require("config.php");

    if ($url !== NULL) {
        $match_info = parse_match_url($url);
        $home_team = $match_info['home_team'];
        $home_logo = $match_info['home_logo'];
        $guest_team = $match_info['guest_team'];
        $guest_logo = $match_info['guest_logo'];
        $start_time = $match_info['start_time'];
    }

    // check if matchday_id exists
    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".matchday WHERE id = :id");
    $statement->execute(array('id' => $matchday_id));
    $matchday = $statement->fetch(PDO::FETCH_ASSOC);

    if ($matchday == false) {
        return $matchday;
    }

    // write information to database
    $statement = $pdo->prepare("INSERT INTO ".$db_name.".match (matchday_id, home_team, home_logo, guest_team, guest_logo, start_time, url) VALUES (:matchday_id, :home_team, :home_logo, :guest_team, :guest_logo, FROM_UNIXTIME(:start_time), :url)");
    $result = $statement->execute(array('matchday_id' => $matchday_id, 'home_team' => $home_team, 'home_logo' => $home_logo, 'guest_team' => $guest_team, 'guest_logo' => $guest_logo, 'start_time' => $start_time, 'url' => $url));

    return $result;
}

function delete_match($match_id) {
    require("config.php");

    $statement = $pdo->prepare("DELETE FROM ".$db_name.".match WHERE id=:id");
    $statement->bindValue(':id', $match_id, PDO::PARAM_INT);
    return $statement->execute();
}

function update_match($match_id, $start_time=NULL, $home_goals=NULL, $guest_goals=NULL, $finished=NULL, $home_logo=NULL, $guest_logo=NULL) {
    require("config.php");
    require_once("bet.php");

    // get match information
    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".match WHERE id = :id");
    $statement->execute(array('id' => $match_id));
    $match = $statement->fetch(PDO::FETCH_ASSOC);

    if ($match == false) {
        return $match;
    }

    if ($match['url'] !== NULL) {
        $match_info = parse_match_url($match['url']);

        if ($home_goals === NULL) {
            $home_goals = $match_info['home_goals'];
        }
        if ($guest_goals === NULL) {
            $guest_goals = $match_info['guest_goals'];
        }
        if ($start_time === NULL) {
            $start_time = $match_info['start_time'];
        }
        if ($finished === NULL) {
            $finished = $match_info['finished'];
            if ($finished === NULL) {
                $finished = $match['finished'];
            }
        }
        if ($home_logo === NULL) {
            $home_logo = $match_info['home_logo'];
        }
        if ($guest_logo === NULL) {
            $guest_logo = $match_info['guest_logo'];
        }
    }

    if ($finished) {
        if ($home_goals > $guest_goals) {
            $winner = 1;
        } elseif ($home_goals < $guest_goals) {
            $winner = 2;
        } else {
            $winner = 0;
        }
    } else {
        $winner = NULL;
    }

    $statement = $pdo->prepare("UPDATE ".$db_name.".match SET home_goals=:home_goals, guest_goals=:guest_goals, 
        finished=:finished, winner=:winner, start_time=FROM_UNIXTIME(:start_time), home_logo=:home_logo, guest_logo=:guest_logo WHERE id=:id");
    $statement->bindValue(':id', $match_id, PDO::PARAM_INT);
    $statement->bindValue(':home_goals', $home_goals, PDO::PARAM_INT);
    $statement->bindValue(':guest_goals', $guest_goals, PDO::PARAM_INT);
    $statement->bindValue(':finished', $finished, PDO::PARAM_BOOL);
    $statement->bindValue(':winner', $winner, PDO::PARAM_INT);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $statement->bindValue(':home_logo', $home_logo, PDO::PARAM_STR);
    $statement->bindValue(':guest_logo', $guest_logo, PDO::PARAM_STR);
    $result = $statement->execute();
    //update points
    foreach(all_users() AS $user) {
        check_points($user['id'],$match_id);
    }


    return $result;

    // if finished, also call the function that gives points for this match
}

function get_match_ids($matchday_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT id FROM ".$db_name.".match WHERE matchday_id = :matchday_id");
    $statement->bindValue(':matchday_id', $matchday_id, PDO::PARAM_INT);
    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $match) {
        $id_list[] = $match['id'];
    }

    return $id_list;
}

function get_matches($ids) {
    require("config.php");

    $matches = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT *, start_time - NOW() AS start FROM ".$db_name.".match WHERE id = :id");
        $statement->execute(array('id' => $id));
        $matches[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $matches;
}

function parse_match_url($url) {
    if (strpos($url, 'sport.de/') !== false) {
        return parse_sportde($url);
    }
    return NULL;
}

function parse_sportde($url) {

    $return = array();

    $url_parsed = parse_url($url);

    $html = file_get_contents($url);

    libxml_use_internal_errors(TRUE); //disable libxml errors

    $doc = new DOMDocument();
    $doc->loadHTML($html);

    libxml_clear_errors(); //remove errors for yucky html

    $xpath = new DOMXPath($doc);

    $return['home_team'] = $xpath->query("//div[contains(@class, 'team-name team-name-home')]/a")[0]->nodeValue;
    $return['guest_team'] = $xpath->query("//div[contains(@class, 'team-name team-name-away')]/a")[0]->nodeValue;
    $return['home_logo'] = getLogo($return['home_team']);
    $return['guest_logo'] = getLogo($return['guest_team']);
    $return['finished'] = false;
    //Hardcoded Ergebnis nach 90min
    $score = explode(':', $xpath->query("//div[contains(@class, 'match-result match-result-0')]")[0]->nodeValue);
    $notlive = empty($xpath->query("//div[contains(@class, 'hs-scoreboard live')]")[0]->nodeValue);
    if ($score[0] != '-' && $notlive == true) {
        $return['finished'] = true;
    }
    
    if($score[0] == '-') {
        $scorehome = NULL;
        $scoreguest = NULL;
    } else {
        $scorehome = $score[0];
        $scoreguest = $score[1];
    }

    $return['home_goals'] = $scorehome;
    $return['guest_goals'] = $scoreguest;

    $matchtime = $xpath->query("//div[contains(@class, 'match-datetime')]")[0]->nodeValue;
    $return['start_time'] = strtotime($matchtime);
  
    return $return;
}

function parse_bundesligade($url) {

    $return = array();

    $url_parsed = parse_url($url);

    $html = file_get_contents($url);

    libxml_use_internal_errors(TRUE); //disable libxml errors

    $doc = new DOMDocument();
    $doc->loadHTML($html);

    libxml_clear_errors(); //remove errors for yucky html

    $xpath = new DOMXPath($doc);

    $return['home_team'] = $xpath->query("//div[contains(@class, 'col-3 col-sm-4 col-md-5 clubHome d-flex justify-content-end')]/div[contains(@class, 'clubName')/h2[contains(@class, 'd-none d-lg-block')/a")[0]->nodeValue;
    $return['guest_team'] = $xpath->query("//div[contains(@class, 'col-3 col-sm-4 col-md-5 clubAway d-flex justify-content-end')]/div[contains(@class, 'clubName')/h2[contains(@class, 'd-none d-lg-block')/a")[0]->nodeValue;
    $return['home_logo'] = getLogo($return['home_team']);
    $return['guest_logo'] = getLogo($return['guest_team']);
    $scorehome = $xpath->query("//div[contains(@class, 'scoreCard scoreCardHome')]/div/div[contains(@class, 'scoreCardContentGoals ng-star-inserted')]")[0]->nodeValue;
    $scoreguest = $xpath->query("//div[contains(@class, 'scoreCard scoreCardAway')]/div/div[contains(@class, 'scoreCardContentGoals ng-star-inserted')]")[0]->nodeValue;
    $matchstate = $xpath->query("//span[contains(@class, 'matchState ng-star-inserted')]")[0]->nodeValue;
    if ($matchstate == 'FINAL') {
        $return['finished'] = true;
    } else {
        $return['finished'] = false;
    }
    $return['home_goals'] = $scorehome;
    $return['guest_goals'] = $scoreguest;

    $matchtime = $xpath->query("//div[contains(@class, 'match-datetime')]")[0]->nodeValue;
    $return['start_time'] = strtotime($matchtime);
  
    return $return;
}

function getLogo($team) {
    require("config.php");

    switch ($team) {
        case "Bayern München":      $logo = 'https://www.flashscore.de/res/image/data/tMir8iCr-88qkLtMj.png';
            break;
        case "FC Bayern München":      $logo = 'https://www.flashscore.de/res/image/data/tMir8iCr-88qkLtMj.png';
            break;
        case "Hertha BSC":          $logo = 'https://www.flashscore.de/res/image/data/dQALRIjl-C6khRCLH.png';
            break;
        case "Borussia Dortmund":   $logo = 'https://www.flashscore.de/res/image/data/Yiq1eU9r-dhhpTYj5.png';
            break;
        case "FC Augsburg":         $logo = 'https://www.flashscore.de/res/image/data/6ZUvwjgl-tGGTvWkH.png';
            break;
        case "Bayer Leverkusen":    $logo = 'https://www.flashscore.de/res/image/data/OWpaDOYA-YFjlSh6B.png';
            break;
        case "SC Paderborn 07":     $logo = 'https://www.flashscore.de/res/image/data/Yu5IOeyB-2PHPuCzB.png';
            break;
        case "VfL Wolfsburg":       $logo = 'https://www.flashscore.de/res/image/data/xK5jP2FG-rDv3XORD.png';
            break;
        case "1. FC Köln":          $logo = 'https://www.flashscore.de/res/image/data/8hxnRHEa-Umm0PjjU.png';
            break;
        case "Werder Bremen":       $logo = 'https://www.flashscore.de/res/image/data/lEp5rDFG-C6khRCLH.png';
            break;
        case "Fortuna Düsseldorf":  $logo = 'https://www.flashscore.de/res/image/data/ph4pnOh5-K6FXwj5N.png';
            break;
        case "SC Freiburg":         $logo = 'https://www.flashscore.de/res/image/data/ttZErLg5-jcldQWzO.png';
            break;
        case "1. FSV Mainz 05":     $logo = 'https://www.flashscore.de/res/image/data/nwmyWZVg-d2RaF1Ue.png';
            break;
        case "Bor. Mönchengladbach":$logo = 'https://www.flashscore.de/res/image/data/rDRx1VXg-xQEMKj97.png';
            break;
        case "FC Schalke 04":       $logo = 'https://www.flashscore.de/res/image/data/b1q14jZg-I1gtUEya.png';
            break;
        case "Eintracht Frankfurt": $logo = 'https://www.flashscore.de/res/image/data/f9dVVYCa-h85SGgwF.png'; 
            break;
        case "1899 Hoffenheim":     $logo = 'https://www.flashscore.de/res/image/data/I3cMF7f5-6cGmlIiN.png';
            break;
        case "1. FC Union Berlin":  $logo = 'https://www.flashscore.de/res/image/data/dI26KXzB-4O0wvXLr.png';
            break;
        case "RB Leipzig":          $logo = 'https://www.flashscore.de/res/image/data/02lwXYkl-hK7y9vIK.png';
            break;

        case "Hamburger SV":        $logo = 'https://www.flashscore.de/res/image/data/vy7iqCwS-Umm0PjjU.png';
            break;
        case "VfL Bochum":          $logo = 'https://www.flashscore.de/res/image/data/nyouWtil-Ak3zFX7R.png';
            break;
        case "SV Sandhausen":       $logo = 'https://www.flashscore.de/res/image/data/nm6mOyyS-QLcAuqKM.png';
            break;
        case "1. FC Nürnberg":      $logo = 'https://www.flashscore.de/res/image/data/rNxwlyGG-YafhBXYt.png';
            break;
        case "VfB Stuttgart":       $logo = 'https://www.flashscore.de/res/image/data/x0YB6veM-Gp7OHZN8.png';
            break;
        case "FC St. Pauli":        $logo = 'https://www.flashscore.de/res/image/data/QZX02wCr-4W0TfhRH.png';
            break;
        case "Arminia Bielefeld":   $logo = 'https://www.flashscore.de/res/image/data/jXtrQQFG-6XlDe17l.png';
            break;
        case "Erzgebirge Aue":      $logo = 'https://www.flashscore.de/res/image/data/xxzUuixS-8KLDrEjh.png';
            break;
        case "SV Wehen Wiesbaden":  $logo = 'https://www.flashscore.de/res/image/data/zNBt0YeM-4CdEv3ZS.png';
            break;
        case "Hannover 96":         $logo = 'https://www.flashscore.de/res/image/data/IXGhyAcM-pU0JrdFp.png';
            break;
        case "Holstein Kiel":       $logo = 'https://www.flashscore.de/res/image/data/0bzaORYA-zm5ZU81I.png';
            break;
        case "Karlsruher SC":       $logo = 'https://www.flashscore.de/res/image/data/69eisaGG-nBKHsY5b.png';
            break;
        case "Dynamo Dresden":      $logo = 'https://www.flashscore.de/res/image/data/hKpC3M9r-KYTatl79.png';
            break;
        case "1. FC Heidenheim 1846":   $logo = 'https://www.flashscore.de/res/image/data/pEIoHCil-C63jp75i.png';
            break;
        case "SpVgg Greuther Fürth":    $logo = 'https://www.flashscore.de/res/image/data/b17q7fAr-4dnK4mIO.png';
            break;
        case "Jahn Regensburg":     $logo = 'https://www.flashscore.de/res/image/data/bHYS4sCa-nVb6tP4G.png';
            break;
        case "VfL Osnabrück":       $logo = 'https://www.flashscore.de/res/image/data/pSveNmcM-2PHPuCzB.png';
            break;
        case "SV Darmstadt 98":     $logo = 'https://www.flashscore.de/res/image/data/lzTyrOAr-G2G8LNSj.png';
            break;

        case "Chemnitzer FC":       $logo = 'https://www.flashscore.de/res/image/data/KY9gMKxS-jc2fqRKc.png';
            break;
        case "1. FC Magdeburg":     $logo = 'https://www.flashscore.de/res/image/data/4E1l0ze5-fHLf2W7g.png';
            break;
        case "KFC Uerdingen 05":    $logo = 'https://www.flashscore.de/res/image/data/bsZe8mAr-dfaaLbap.png';
            break;
        case "FC Ingolstadt 04":    $logo = 'https://www.flashscore.de/res/image/data/4dq60IAr-K6FXwj5N.png';
            break;
        case "Würzburger Kickers":  $logo = 'https://www.flashscore.de/res/image/data/hl2Y1JHG-jiP0452i.png';
            break;
        case "Preußen Münster":     $logo = 'https://www.flashscore.de/res/image/data/88FnbHcM-8v02s5kA.png';
            break;
        case "MSV Duisburg":        $logo = 'https://www.flashscore.de/res/image/data/SA8VA2eM-8KLDrEjh.png';
            break;
        case "FSV Zwickau":         $logo = 'https://www.flashscore.de/res/image/data/SrpJ3zGG-vBSqmuO5.png';
            break;
        case "TSV 1860 München":    $logo = 'https://www.flashscore.de/res/image/data/bc5ve6e5-vNKxfWV1.png';
            break;
        case "SV Meppen":           $logo = 'https://www.flashscore.de/res/image/data/nTDB9O9r-EeLu1sTG.png';
            break;
        case "Viktoria Köln":       $logo = 'https://www.flashscore.de/res/image/data/WQJEg1f5-tY9G2wz6.png';
            break;
        case "SpVgg Unterhaching":  $logo = 'https://www.flashscore.de/res/image/data/zkutRtDr-zk8tJzWQ.png';
            break;
        case "Hansa Rostock":       $logo = 'https://www.flashscore.de/res/image/data/4EFuKCZA-S82PeYBB.png';
            break;
        case "SG Sonnenhof Großaspach": $logo = 'https://www.flashscore.de/res/image/data/riTD45Ca-2u2iUVB0.png';
            break;
        case "1. FC Kaiserslautern":    $logo = 'https://www.flashscore.de/res/image/data/pAs4omCr-nLRsbtQR.png';
            break;
        case "Eintracht Braunschweig":  $logo = 'https://www.flashscore.de/res/image/data/jTPygLBr-SKpuGhom.png';
            break;
        case "FC Carl Zeiss Jena":  $logo = 'https://www.flashscore.de/res/image/data/QFvxxFf5-Um1broz4.png';
            break;
        case "Waldhof Mannheim":    $logo = 'https://www.flashscore.de/res/image/data/z1xOIbhl-YLNkhM3S.png';
            break;
        case "Hallescher FC":       $logo = 'https://www.flashscore.de/res/image/data/vB3zP4Ar-MLY0nDP0.png';
            break;
        case "Bayern München II":   $logo = 'https://www.flashscore.de/res/image/data/EN7EzVGG-pCspZtcj.png';
            break;
        case "VfB Lübeck":          $logo = 'https://www.flashscore.de/res/image/data/2oel6qBr-zRyL8YUP.png';
            break;
        case "Türkgücü München":    $logo = 'https://www.flashscore.de/res/image/data/ATHRTQZA-2LD1OW9b.png';
            break;
        case "SC Verl":             $logo = 'https://www.flashscore.de/res/image/data/f3YP3BWg-Qq651txc.png';
            break;
        case "1. FC Saarbrücken":   $logo = 'https://www.flashscore.de/res/image/data/O6Ew3GBr-0O9JYj2g.png';
            break;

        case "Türkei":   $logo = 'https://www.flashscore.de/res/image/data/Q7zzy7HG-82rsxg61.png';
            break;
        case "Italien":   $logo = 'https://www.flashscore.de/res/image/data/zgZ3N3Yg-Gpo9Haxf.png';
            break;
        case "Wales":   $logo = 'https://www.flashscore.de/res/image/data/SdWlpUhl-vsqJS6k9.png';
            break;
        case "Schweiz":   $logo = 'https://www.flashscore.de/res/image/data/tWvN5N9r-r7XOAb5M.png';
            break;
        case "Dänemark":   $logo = 'https://www.flashscore.de/res/image/data/d8MRvRBr-vs3rrxd7.png';
            break;
        case "Finnland":   $logo = 'https://www.flashscore.de/res/image/data/Mmyh0ByS-02I1PFMe.png';
            break;
        case "Belgien":   $logo = 'https://www.flashscore.de/res/image/data/Qur1J5jl-ALa3qyER.png';
            break;
        case "Russland":   $logo = 'https://www.flashscore.de/res/image/data/00Z8ODyS-ALa3qyER.png';
            break;
        case "England":   $logo = 'https://www.flashscore.de/res/image/data/tCG9KQYA-84VqVvfA.png';
            break;
        case "Kroatien":   $logo = 'https://www.flashscore.de/res/image/data/KMZkYbwS-UBqwwZje.png';
            break;
        case "Österreich":   $logo = 'https://www.flashscore.de/res/image/data/p2STPgdM-02AFhOqC.png';
            break;
        case "Nordmazedonien":   $logo = 'https://www.flashscore.de/res/image/data/hhJjv8g5-CUoVveMr.png';
            break;
        case "Niederlande":   $logo = 'https://www.flashscore.de/res/image/data/bieizLjl-neUmUb9G.png';
            break;
        case "Ukraine":   $logo = 'https://www.flashscore.de/res/image/data/Q3KOlYVg-0j2ml3gJ.png';
            break;
        case "Schottland":   $logo = 'https://www.flashscore.de/res/image/data/lf3kdFwS-SdtYUu92.png';
            break;
        case "Tschechien":   $logo = 'https://www.flashscore.de/res/image/data/UH0CqZdM-lMFld0Yg.png';
            break;
        case "Polen":   $logo = 'https://www.flashscore.de/res/image/data/zB15YqDr-Eaw4x6f7.png';
            break;
        case "Slowakei":   $logo = 'https://www.flashscore.de/res/image/data/phJfHhHG-MXz8lgyU.png';
            break;
        case "Spanien":   $logo = 'https://www.flashscore.de/res/image/data/Od7JGXZA-A5iboq4k.png';
            break;
        case "Schweden":   $logo = 'https://www.flashscore.de/res/image/data/b7TlfQBr-MmTWbGDE.png';
            break;
        case "Ungarn":   $logo = 'https://www.flashscore.de/res/image/data/21LKuWg5-rcUSadb8.png';
            break;
        case "Portugal":   $logo = 'https://www.flashscore.de/res/image/data/IBvrXaZg-ny8ThRvl.png';
            break;
        case "Frankreich":   $logo = 'https://www.flashscore.de/res/image/data/I5PqTkcM-EoQ0Krck.png';
            break;
        case "Deutschland":   $logo = 'https://www.flashscore.de/res/image/data/zP226aXg-02AFhOqC.png';
            break;

        case "Arsenal FC":          $logo = 'https://www.flashscore.de/res/image/data/pfchdCg5-pU2IsJm8.png';
            break;
        case "West Ham United":     $logo = 'https://www.flashscore.de/res/image/data/Qo3RdMjl-hrtlQ906.png';
            break;
        case "Leicester City":      $logo = 'https://www.flashscore.de/res/image/data/Em1CYqYg-nDs49AhO.png';
            break;
        case "Tottenham Hotspur":   $logo = 'https://www.flashscore.de/res/image/data/ARC62UAr-EwpAw8YN.png';
            break;
        case "Chelsea FC":          $logo = 'https://www.flashscore.de/res/image/data/GMmvDEdM-2B0QucIK.png';
            break;
        case "Everton FC":          $logo = 'https://www.flashscore.de/res/image/data/EwJqZUZA-Onr593up.png';
            break;

        case "Real Madrid":         $logo = 'https://www.flashscore.de/res/image/data/A7kHoxZA-2irdgP53.png';
            break;
        case "Valencia CF":         $logo = 'https://www.flashscore.de/res/image/data/O2FpIYg5-KYu4i3zG.png';
            break;
        case "Inter Mailand":       $logo = 'https://www.flashscore.de/res/image/data/lxz64qDa-0GX6tBq2.png';
            break;
        case "AS Rom":              $logo = 'https://www.flashscore.de/res/image/data/QPgsexZA-Oh9bbJkf.png';
            break;
        case "Juventus Turin":      $logo = 'https://www.flashscore.de/res/image/data/CbDOFGyS-QPFkwCmU.png';
            break;
        case "Lazio Rom":           $logo = 'https://www.flashscore.de/res/image/data/zXqymff5-67BxS1ca.png';
            break;

    }
    return $logo;
}

?>