<?php

try {
    $dbh = new PDO("sqlite:../db/randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}

function space() {
  echo ("<br><br>");
}

function getOneEntry($tableName, $dbh) {    
    $query = "SELECT count(*) FROM $tableName";
    if ($tableName == "video") {
        $query .= " WHERE show != 'Other'";
    }
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
    //print_r($_POST);
    //echo("xxx");
    $table = $_POST['table'];
    $shows = json_decode($_POST['show']);
    $published = $_POST['published'];
    $oneOffs = false;
    if (!empty($_POST['oneOffs'])) {
        $oneOffs = $_POST['oneOffs'];
    }
    //print_r($shows);
    //echo("xxx");
    define("JON_ERA", "'2013-06-25T17:45:02.000Z'");    
    $query = "SELECT ";
    $query2 = "";
    switch($table) {
        case 'video':
            $query .= "video.video_id as object_id, video.title as object_title, video.published_date as object_published_date, 'video' as object_type FROM video";
            break;
        case 'playlist':
            $query .= "DISTINCT playlist.playlist_id as object_id, playlist.title as object_title, playlist.published_date as object_published_date, 'playlist' as object_type FROM playlist INNER JOIN video ON video.playlist_id = playlist.playlist_id";
            break;
        case 'both':
            $query .= "video.video_id as object_id, video.title as object_title, video.published_date as object_published_date, 'video' as object_type FROM video";
            $query2 .= "UNION ALL SELECT DISTINCT playlist.playlist_id, playlist.title as object_title, playlist.published_date as object_published_date, 'playlist' as object_type FROM playlist INNER JOIN video ON video.playlist_id = playlist.playlist_id";
            break;
    }
    
    if ($oneOffs != false && $table != 'playlist') {
        $query .= " INNER JOIN playlist on video.playlist_id = playlist.playlist_id";
    }
    
    if($shows[0] == "all" || empty($shows)) {
        $query .= " WHERE video.show != 'Other'";
        $query2 .= " WHERE video.show != 'Other'";
    } else {
        $firstShow = true;
        foreach($shows as $show) {
            if($firstShow) {
                $query .= " WHERE ";
                $query2 .= " WHERE ";         
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
            
            if (($table == "video" || $table == "both") && $oneOffs != false) {
                if ($oneOffs == "exclude") {
                    $query .= " AND playlist.title IS NOT 'One-Offs'";
                } else if ($oneOffs == "only") {
                    $query .= " AND playlist.title = 'One-Offs'";
                }

            }
        }
    }
    
    //This ensures the entire One-Offs playlist is never returned.
    $query .= " AND object_title IS NOT 'One-Offs' AND object_title IS NOT 'Uploads'";
    $query2 .= " AND object_title IS NOT 'One-Offs' AND object_title IS NOT 'Uploads'";
    
    switch($published) {
        case 'jon-era':
            $query .= " AND object_published_date < " . JON_ERA;
            $query2 .= " AND object_published_date < " . JON_ERA;
            break;
        case 'dan-era':
            $query .= " AND object_published_date > " . JON_ERA;
            $query2 .= " AND object_published_date > " . JON_ERA;
            break;
        case 'both':
            break;
        default:
            break;
    }
    //echo($query);
    //echo("xxx");
    if ($table === "both") {
        $query = $query . $query2;
    }
    
    file_put_contents("querylog.txt", $query, FILE_APPEND);
    file_put_contents("querylog.txt", "\n", FILE_APPEND);
    
    $stmt = $dbh->prepare($query);
    if ($stmt != false) {
        $stmt->execute();
        $results = $stmt->fetchAll();
        shuffle($results);
        echo(json_encode($results));
    } else {
        echo('Database error');
    }

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


?>