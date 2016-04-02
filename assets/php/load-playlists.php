<?php
ini_set('max_execution_time', 300);
$channelName = "gamegrumps";
require_once('yt-key.php');
$MAX_RESULTS = 50;
$playlistStartTime = microtime(true);

echo("Started loading playlists... <br>");

$request = 'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=' . $channelName . '&key='. MY_KEY;
$channel = json_decode(file_get_contents($request), true);
$channelId = $channel["items"][0]["id"];
$uploadsId = $channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"];
//print_r($uploadsId);
//echo($request);
//echo("<br><br>");
$request = 'https://www.googleapis.com/youtube/v3/playlists?part=contentDetails,snippet&channelId=' . $channelId . '&maxResults=' . $MAX_RESULTS . '&key='. MY_KEY;
$playlistsResults = json_decode(file_get_contents($request), true);
$nextPageToken = $playlistsResults["nextPageToken"];
$playlists = $playlistsResults["items"];
//print_r($playlists[0]);
//echo($request);
//echo("<br><br>");
while ($nextPageToken != "") {
    $request = 'https://www.googleapis.com/youtube/v3/playlists?part=contentDetails,snippet&channelId=' . $channelId . '&maxResults=' . $MAX_RESULTS . '&pageToken='. $nextPageToken . '&key='. MY_KEY;
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
    $dbh = new PDO("sqlite:../db/randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}  

$query = "DROP TABLE IF EXISTS playlist";
$dbh->exec($query);

$query = "CREATE TABLE IF NOT EXISTS `playlist` (
	`playlist_id`	TEXT UNIQUE,
	`title`	TEXT,
	`published_date`	DATE,
	`episodes`	INT
    )";
$dbh->exec($query);


$dbh->beginTransaction();
foreach ($playlistsFormatted as $playlist=>$array) {
    $playlist_id = $dbh->quote($array[0]);
    $title = $dbh->quote($array[1]);
    $published_date = $dbh->quote($array[2]);
    $episodes = $dbh->quote($array[3]);
    $query = "INSERT OR IGNORE INTO playlist (playlist_id, title, published_date, episodes) VALUES ($playlist_id, $title, $published_date, $episodes)";
    $dbh->query($query);
}

$dbh->commit();

$playlistEndTime = microtime(true);
echo("Finished loading playlists. Runtime: " . date("i:s",$playlistEndTime-$playlistStartTime) . "<br>");

?>