<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.06.18
 * Time: 00:08
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

if (trim($_POST["new_betgroup_name"]) !== "") {
    create_betgroup(trim($_POST["new_betgroup_name"]));
}

$betgroups = get_betgroups(get_betgroup_ids());

foreach (get_betgroup_ids() as $id) {
    if (isset($_POST['delete' . $id])) {
        delete_betgroup($id);
    }
    foreach (all_users() as $users) {
        if (isset($_POST['check' . $id . $users['id']])) {
            create_betgroup_user($user_id, $id);
        }
    }
}

?>
<html>
<body>
<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

?>

<table class="table">
    <thead class="thead-inverse">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Seasons</th>
            <th>Users</th>
            <?php
            if ($is_admin) {
                echo "<th>Aktion</th>";
            }
        echo "</tr>";

        foreach($betgroups AS $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name']. "</td>";
            echo "<td>";
            echo "<li class='dropdown'>";
                echo "<a data-toggle='dropdown' class='dropdown-toggle'>Seasons<b class='caret'></b></a>";
                    echo "<ul class='dropdown-menu'>";
                        echo "<li>";
                            echo "<div class='checkbox'>";
                                echo "<label>";
                                    echo "<input type='checkbox'>Two";
                                echo "</label>";
                            echo "</div>";
                        echo "</li>";
                        echo "<li>";
                            echo "<div class='checkbox'>";
                                echo "<label>";
                                    echo "<input type='checkbox'>Two";
                                echo "</label>";
                            echo "</div>";
                        echo "</li>";
                    echo "</ul>";
                echo "</li>";
            echo "</td>";

            echo "<td>";
                echo "<li class='dropdown'>";
                    echo "<a data-toggle='dropdown' class='dropdown-toggle'>Users<b class='caret'></b></a>";
                        echo "<ul class='dropdown-menu'>";
                            echo "<li>";
                                echo "<div class='checkbox'>";
                                    echo "<label>";
                                        echo "<input type='checkbox'>Two";
                                    echo "</label>";
                                echo "</div>";
                            echo "</li>";
                            echo "<li>";
                                echo "<div class='checkbox'>";
                                    echo "<label>";
                                        echo "<input type='checkbox'>Two";
                                    echo "</label>";
                                echo "</div>";
                            echo "</li>";
                        echo "</ul>";
                echo "</li>";
            echo "</td>";

            if ($is_admin) {
                $betgroupid = $row['id'];
                echo "<form action='$actual_link' method='post'>";
                echo "<td><button type='submit' class='btn btn-primary' name='delete$betgroupid' value='1'>Löschen</button></td></form>";
                }
            echo "</tr>";
        } ?>
        </tr>
    </thead>
</table>

<div class="container">
    <form action="<?php echo $actual_link; ?>" method="post">
        <label for="new_betgroup_name">Neues Tipprunde</label>
        <input type="text" class="form-control" name="new_betgroup_name" placeholder="Tipprunde" value="<?php echo $name; ?>">
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

</body>
</html>