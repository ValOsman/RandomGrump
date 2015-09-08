<?php

try {
    $dbh = new PDO("sqlite:randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}

function space() {
  echo ("<br><br>");
}

function getOneEntry($tableName, $dbh) {
    $query = "SELECT count(*) FROM $tableName";
    $stmt = $dbh->query($query);
    $stmt->execute();
    $numRows = $stmt->fetch()[0];

    $randInt = mt_rand(1, $numRows);
    $query = "SELECT " . $tableName . "_id FROM $tableName WHERE rowid = '$randInt'";
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    return $stmt->fetch()[0];
}

if (isset($_POST['getList'])) {
    print_r($_POST);
    echo("xxx");
    $table = $_POST['table'];
    $shows = json_decode($_POST['show']);
    print_r($shows);
    echo("xxx");
    $query = "SELECT ";
    $query2 = "";
    $where = "";
    switch($table) {
        case 'video':
            $query .= "video.video_id as object_id, video.title as object_title, 'video' as object_type FROM video";
            break;
        case 'playlist':
            $query .= "DISTINCT playlist.playlist_id as object_id, playlist.title as object_title, 'playlist' as object_type FROM playlist INNER JOIN video ON video.playlist_id = playlist.playlist_id";
            break;
        case 'both':
            $query .= "video.video_id as object_id, video.title as object_title, 'video' as object_type FROM video";
            $query2 .= "UNION ALL SELECT DISTINCT playlist.playlist_id, playlist.title as object_title, 'playlist' as object_type FROM playlist INNER JOIN video ON video.playlist_id = playlist.playlist_id";
            break;
    }
    if($shows[0] == "all" || empty($shows)) {
        $query .= " WHERE video.show != 'Other'";
        $query2 .= " WHERE video.show != 'Other'";
    } else {
        $firstShow = true;
        foreach($shows as $show) {
            if($firstShow) {
                $query .= " WHERE";
                $query2 .= " WHERE";         
            } else {
                $query .= " OR";
                $query2 .= " OR";
            }
            switch($show) {
                case 'gamegrumps':
                    $query .= " video.show = 'Game Grumps'";
                    $query2 .= " video.show = 'Game Grumps'";
                    break;
                case 'gamegrumpsvs':
                    $query .= " video.show = 'Game Grumps VS'";
                    $query2 .= " video.show = 'Game Grumps VS'";
                    break;            
                case 'steamtrain':
                    $query .= " video.show = 'Steam Train'";
                    $query2 .= " video.show = 'Steam Train'";
                    break;
                case 'steamrolled':
                    $query .= " video.show = 'Steam Rolled'";
                    $query2 .= " video.show = 'Steam Rolled'";
                    break;
                case 'grumpcade':
                    $query .= " video.show = 'Grumpcade'";
                    $query2 .= " video.show = 'Grumpcade'";
                    break;
                case 'tableflip':
                    $query .= " video.show = 'Table Flip'";
                    $query2 .= " video.show = 'Table Flip'";
                    break;
                case 'animated':
                    $query .= " video.show = 'Animated'";
                    $query2 .= " video.show = 'Animated'";
                    break;
                default:
                    $query .= " video.show != 'Other'";
                    $query2 .= " video.show != 'Other'";
                    break;
            }
            $firstShow = false;
        }
        $query .= " AND object_title IS NOT 'One-Offs'";
        $query2 .= " AND object_title IS NOT 'One-Offs'";

    }
    //echo($query);
    if ($table === "both") {
        $query = $query . $query2;
    }
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll();
    shuffle($results);
    echo($query . "xxx" . json_encode($results));
}

if (isset($_POST['getOne'])) {
    switch ($_POST['table']) {
        case 'video':
            echo(getOneEntry("video", $dbh));
            break;
        case 'playlist':
            echo(getOneEntry("playlist", $dbh));            
            break;
    }
}


// function select() {
//     echo "The select function is called.";
//     exit;
// }

// function insert() {
//     echo "<p>The insert function is called.<p>";
//     exit;
// }
?>