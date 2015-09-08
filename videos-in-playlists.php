<?php
set_time_limit(0);
$channelName = "gamegrumps";
$MY_KEY = "AIzaSyCg1GMcq_tVjSsiykZH6xTU0ZDlTOWjkV8";
$MAX_RESULTS = 50;
$startTime = microtime(true);

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

try {
    $dbh = new PDO("sqlite:randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}

$dropQuery = "DROP TABLE IF EXISTS videos_in_playlists";
$dbh->exec($dropQuery);

/*
$createQuery = "CREATE TABLE IF NOT EXISTS `videos_in_playlists` (
    `video_id`  TEXT NOT NULL,
    `playlist_id`   TEXT NOT NULL,
    PRIMARY KEY(video_id,playlist_id),
    FOREIGN KEY(`video_id`) REFERENCES video ( video_id ),
    FOREIGN KEY(`playlist_id`) REFERENCES video ( playlist_id )
)";

$dbh->exec($createQuery);*/



$selectQuery = "SELECT playlist_id, title FROM playlist";
$stmt = $dbh->prepare($selectQuery);
$stmt->execute();
$playlistIds = $stmt->fetchAll();
$playlists = array();

// LOCAL DB VERSION
// foreach($playlistIds as $playlistId) {
//     $title = "%" . $playlistId["title"] . "%";
//     $playlist_id = $playlistId["playlist_id"];
//     echo($title . " | " . $playlist_id . "<br><br>");
//     $videoQuery = "SELECT video_id,show FROM video WHERE title LIKE :name";
//     echo($videoQuery . "<br><br>");
//     $stmt = $dbh->prepare($videoQuery);
//     $stmt->bindParam(':name', $title);
//     $stmt->execute();
//     $videoArray = $stmt->fetchAll();
//     $videoIds = array();
//     $videoShow = $videoArray[0]["show"];
//     foreach($videoArray as $videoId) {
//         $videoIds[] = $videoId["video_id"];
//     }
//     $playlists[$playlist_id] = $videoIds;
//     foreach($videoIds as $video_id) {      
//         $insert = "INSERT INTO videos_in_playlists(playlist_id, video_id) VALUES ('$playlist_id', '$video_id')";
//         echo($insert);
//         echo("<br><br>");
//         $dbh->exec($insert);
//     }
// }

foreach($playlistIds as $playlistId) {
    $request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&playlistId=' . $playlistId[0] . '&maxResults=' . $MAX_RESULTS . '&key='. $MY_KEY;
    $playlistsResults = json_decode(file_get_contents($request), true);
    $items = $playlistsResults["items"];
    $videoIds = array();
    foreach($items as $item) {
        $videoIds[] = $item["contentDetails"]["videoId"];
    }
    $playlist_id = $playlistId["playlist_id"];
    $playlists[$playlist_id] = $videoIds;
    echo("<br><br>");
    if (array_key_exists("nextPageToken",$playlistsResults)) {
        $nextPageToken = $playlistsResults["nextPageToken"];
        while($nextPageToken != "") {
            $request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&playlistId=' . $playlistId[0] . '&pageToken=' . $nextPageToken .  '&maxResults=' . $MAX_RESULTS . '&key='. $MY_KEY;
            $playlistsResults = json_decode(file_get_contents($request), true);
            $items = $playlistsResults["items"];
            $videoIds = array();
            foreach($items as $item) {
                $videoIds[] = $item["contentDetails"]["videoId"];
            }
            $playlists[$playlist_id] = array_merge($playlists[$playlist_id],$videoIds);
            if (array_key_exists("nextPageToken",$playlistsResults)) {
                $nextPageToken = $playlistsResults["nextPageToken"]; 
                //echo($playlistsResults["nextPageToken"]  . "<br><br>");
            } else {
                $nextPageToken = "";
            }
        }     
    }
    echo($playlistId["title"] . " | ");
    print_r($playlists["$playlist_id"]);
}

//print_r($playlists);

foreach($playlists as $playlistId=>$playlist) {
    foreach($playlist as $video) {
        $insert = "UPDATE video SET playlist_id = (?) WHERE video_id = (?)";
        $stmt = $dbh->prepare($insert);
        $stmt->bindParam(1, $playlistId);
        $stmt->bindParam(2, $video);
        $stmt->execute();
    }
}



$endTime = microtime(true);
echo("<br><br> Runtime: " . date("i:s",$endTime-$startTime));
?>