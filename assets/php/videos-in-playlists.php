<?php
set_time_limit(0);
$channelName = "gamegrumps";
require_once('yt-key.php');
$MAX_RESULTS = 50;
$vipStartTime = microtime(true);

echo("Started loading videos into playlists... <br>");

try {
    $dbh = new PDO("sqlite:../db/randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}

$selectQuery = "SELECT playlist_id, title FROM playlist";
$stmt = $dbh->prepare($selectQuery);
$stmt->execute();
$playlistIds = $stmt->fetchAll();
$playlists = array();

foreach($playlistIds as $playlistId) {
    $request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&playlistId=' . $playlistId[0] . '&maxResults=' . $MAX_RESULTS . '&key='. MY_KEY;
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
            $request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&playlistId=' . $playlistId[0] . '&pageToken=' . $nextPageToken .  '&maxResults=' . $MAX_RESULTS . '&key='. MY_KEY;
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
    //echo($playlistId["title"] . " | ");
    //print_r($playlists["$playlist_id"]);
}

$vipEndTime = microtime(true);
echo("HTTPS querying over: " . date("i:s",$vipEndTime-$vipStartTime) . "<br>");

//print_r($playlists);
$dbh->beginTransaction();
foreach($playlists as $playlistId=>$playlist) {
    foreach($playlist as $video) {
        $insert = "UPDATE OR IGNORE video SET playlist_id = (?) WHERE video_id = (?)";
        $stmt = $dbh->prepare($insert);
        $stmt->bindParam(1, $playlistId);
        $stmt->bindParam(2, $video);
        $stmt->execute();
    }
}
$dbh->commit();



$vipEndTime = microtime(true);
echo("Finished loading videos into playlists. Runtime: " . date("i:s",$vipEndTime-$vipStartTime) . "<br>");
?>