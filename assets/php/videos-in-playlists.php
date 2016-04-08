<?php
$vipStartTime = microtime(true);

echo("Started loading videos into playlists... <br>");

try {
    $dbh = new PDO("sqlite:../db/randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}

$selectQuery = "SELECT playlist_id, title FROM playlist ORDER BY playlist_id DESC";
$stmt = $dbh->prepare($selectQuery);
$stmt->execute();
$playlistIds = $stmt->fetchAll();
$playlists = array();
$dbh = null;
$stmt = null;

//Gets each video ID and associates it with a playlist ID.
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


try {
    $dbh = new PDO("sqlite:../db/randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}

//print_r($playlists);
$dbh->beginTransaction();
foreach($playlists as $playlistId=>$playlist) {
    //echo("playlist id: " . $playlistId . "<br>");
    foreach($playlist as $video) {
        //echo("<br> video id: " . $video);
        $insert = "UPDATE OR IGNORE video SET playlist_id = :playlistId WHERE video_id = :video";
        $stmt = $dbh->prepare($insert);
        $stmt->bindParam(':playlistId', $playlistId);
        $stmt->bindParam(':video', $video);
        $stmt->execute();
    }
}
$dbh->commit();

$vipEndTime = microtime(true);
echo("Finished loading videos into playlists. Runtime: " . date("i:s",$vipEndTime-$vipStartTime) . "<br>");
?>