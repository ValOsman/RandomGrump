<?php
// set_time_limit(0);
$channelName = "gamegrumps";
$MY_KEY = "AIzaSyCg1GMcq_tVjSsiykZH6xTU0ZDlTOWjkV8";
$MAX_RESULTS = 50;

$request = 'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=' . $channelName . '&key='. $MY_KEY;
$channel = json_decode(file_get_contents($request), true);
$channelId = $channel["items"][0]["id"];
$uploadsId = $channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"];
//print_r($uploadsId);
//echo($request);
echo("<br><br>");
$request = 'https://www.googleapis.com/youtube/v3/playlists?part=contentDetails,snippet&channelId=' . $channelId . '&maxResults=' . $MAX_RESULTS . '&key='. $MY_KEY;
$playlistsResults = json_decode(file_get_contents($request), true);
$nextPageToken = $playlistsResults["nextPageToken"];
$playlists = $playlistsResults["items"];
//print_r($playlists[0]);
//echo($request);
echo("<br><br>");
while ($nextPageToken != "") {
    $request = 'https://www.googleapis.com/youtube/v3/playlists?part=contentDetails,snippet&channelId=' . $channelId . '&maxResults=' . $MAX_RESULTS . '&pageToken='. $nextPageToken . '&key='. $MY_KEY;
    $playlistsResults = json_decode(file_get_contents($request), true);
    if (array_key_exists("nextPageToken",$playlistsResults)) {
      $nextPageToken = $playlistsResults["nextPageToken"];      
    } else {
      $nextPageToken = "";
    }
    $playlists = array_merge($playlists, $playlistsResults["items"]);
}
//print_r($playlists);
$playerIdArray = array();
for ($i = 0; $i < sizeof($playlists); $i++) {
    $items = $playlists[$i];
    $snippet = $playlists[$i]["snippet"];
    $title = explode(" - ", $snippet["title"])[0];
    $length = $items["contentDetails"]["itemCount"];
    $playlistsFormatted[] = array($items["id"], $title, $snippet["publishedAt"], $length);
}

try {
    $dbh = new PDO("sqlite:randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}  

$query = "DROP TABLE IF EXISTS playlist";
$dbh->exec($query);

$query = "CREATE TABLE IF NOT EXISTS playlist(playlist_id TEXT, title TEXT, published_date DATE, episodes INT, show TEXT)";
$dbh->exec($query);

foreach ($playlistsFormatted as $playlist=>$array) {
    $playlist_id = $dbh->quote($array[0]);
    $title = $dbh->quote($array[1]);
    $published_date = $dbh->quote($array[2]);
    $episodes = $dbh->quote($array[3]);
    $query = "INSERT INTO playlist (playlist_id, title, published_date, episodes) VALUES ($playlist_id, $title, $published_date, $episodes)";
    $dbh->exec($query);
}

$randInt = mt_rand(1, 276);
$query = "SELECT playlist_id FROM playlist WHERE rowid = '$randInt'";
$stmt = $dbh->prepare($query);
$stmt->execute();
$randId = $stmt->fetch()[0];
?>