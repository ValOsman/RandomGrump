<?php
$playlistStartTime = microtime(true);

echo("Started loading playlists... <br>");

//Get first page of playlists and then iterate over remaining pages.
$request = 'https://www.googleapis.com/youtube/v3/playlists?part=contentDetails,snippet&channelId=' . $channelId . '&maxResults=' . $MAX_RESULTS . '&key='. MY_KEY;
$playlistsResults = json_decode(file_get_contents($request), true);
$nextPageToken = $playlistsResults["nextPageToken"];
$playlists = $playlistsResults["items"];

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

$playerIdArray = array();

//Format relevant playlist info into arrays and store those arrays in $playlistsFormatted.
for ($i = 0; $i < sizeof($playlists); $i++) {
    $items = $playlists[$i];
    $snippet = $playlists[$i]["snippet"];
    $title = explode(" - ", $snippet["title"])[0];
    $length = $items["contentDetails"]["itemCount"];
    $publishedAt = $snippet["publishedAt"];
    $playlistsFormatted[] = array($items["id"], $title, $publishedAt, $length);
}

$playlistsFormatted[] = array($uploadsId, 'Uploads', NULL, NULL);

//Connect to database, drop table if testing, create table if necessary
try {
    $dbh = new PDO("sqlite:../db/randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}  

/*$query = "DROP TABLE IF EXISTS playlist";
$dbh->exec($query);*/

$query = "CREATE TABLE IF NOT EXISTS `playlist` (
	`playlist_id`	TEXT UNIQUE,
	`title`	TEXT,
	`published_date`	DATE,
	`episodes`	INT
    )";
$dbh->exec($query);

//Store data
$dbh->beginTransaction();
foreach ($playlistsFormatted as $playlist=>$array) {
    $stmt = $dbh->prepare("INSERT OR IGNORE INTO playlist (playlist_id, title, published_date, episodes) VALUES (:playlist_id, :title, :published_date, :episodes)");
    $stmt->bindParam(":playlist_id", $array[0]);
    $stmt->bindParam(":title", $array[1]);
    $stmt->bindParam(":published_date", $array[2]);
    $stmt->bindParam(":episodes", $array[3]);
    $stmt->execute();
}

$dbh->commit();

$playlistEndTime = microtime(true);
echo("Finished loading playlists. Runtime: " . date("i:s",$playlistEndTime-$playlistStartTime) . "<br>");

?>