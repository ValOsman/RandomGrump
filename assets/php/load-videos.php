<?php
$videoStartTime = microtime(true);

echo("Start loading videos... <br>");

try {
    $dbh = new PDO("sqlite:../db/randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
} 

/*$query = "DROP TABLE IF EXISTS video";
$dbh->exec($query);*/

$query = "CREATE TABLE IF NOT EXISTS `video` (
	`video_id`	TEXT UNIQUE,
	`playlist_id`	TEXT,
	`title`	TEXT,
	`published_date`	DATE,
	`show`	TEXT,
	FOREIGN KEY(playlist_id) REFERENCES playlist (playlist_id)
    )";
$dbh->exec($query);

$request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails,snippet&playlistId=' . $uploadsId . '&maxResults=' . $MAX_RESULTS . '&key=' . MY_KEY;
//echo($request);
$uploadsResults = json_decode(file_get_contents($request), true);
$nextPageToken = $uploadsResults["nextPageToken"];
$uploads = $uploadsResults["items"];
while ($nextPageToken != "") {
    $request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails,snippet&playlistId=' . $uploadsId . '&maxResults=' . $MAX_RESULTS . '&pageToken='. $nextPageToken . '&key='. MY_KEY;
    $uploadsResults = json_decode(file_get_contents($request), true);
    if (array_key_exists("nextPageToken",$uploadsResults)) {
      $nextPageToken = $uploadsResults["nextPageToken"];      
    } else {
      $nextPageToken = "";
    }
    $uploads = array_merge($uploads, $uploadsResults["items"]);
}

//echo(sizeof($uploads));

function getShow($title, $publishedAt) {
    $publishedMonth = substr($publishedAt, 2, 4);
    $publishedDay = substr($publishedAt, 2, 7);
    $titleArray = explode("- ", $title);
    $titleStart = $titleArray[0];
    $titleEnd = $titleArray[sizeof($titleArray) - 1];
    switch (true) {
        case count($titleArray) == 1:
            return "Other";
            break;
        /*case ($publishedMonth == "04" && $publishedDay == "01"):
            return "Other";
            break;*/
        case stristr($titleStart,"Game Grumps VS") !== false || stristr($titleEnd,"Game Grumps VS") !== false:
            return "Game Grumps VS";
            break;
        case stristr($titleEnd, "Game Grumps") !== false || stristr($titleEnd, "GameGrumps") !== false:
            return "Game Grumps";
            break;
        case stristr($titleEnd,"Steam Train") !== false:
            return "Steam Train";
            break;
        case stristr($titleEnd,"Steam Rolled") !== false:
            return "Steam Rolled";
            break;
        case stristr($titleEnd, "Grumpcade") !== false:
            return "Grumpcade";
            break;
        case stristr($titleStart,"Animated") !== false || stristr($titleStart,"Animation") !== false:
            return "Animated";
            break;
        case stristr($titleStart,"Table Flip NEW EPISODE on Sling") !== false ||
            stristr($titleStart, "NEW Episode of TABLE FLIP") !== false:
            return "Other";
            break;
        case stristr($titleEnd, "Table Flip") !== false:
            return "Table Flip";
            break;
        default:
            return "Other";
            break;
    }
}


for ($i = 0; $i < sizeof($uploads); $i++) {
    $id = $uploads[$i]["contentDetails"]["videoId"];
    $snippet = $uploads[$i]["snippet"];
    $title = $snippet["title"];
    $publishedAt = $snippet["publishedAt"];
    $show = getShow($title, $publishedAt);
    $videosFormatted[] = array($id, $title, $snippet["publishedAt"], $show);
    //echo('<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=' . $playlists[$i]["id"] . '" frameborder="0" allowfullscreen></iframe>');
}


$dbh->beginTransaction();
foreach ($videosFormatted as $video=>$array) {
    $stmt = $dbh->prepare("INSERT INTO video (video_id, title, published_date, show) VALUES (:video_id, :title, :published_date, :show)");
    $stmt->bindParam(':video_id', $array[0]);
    $stmt->bindParam(':title', $array[1]);
    $stmt->bindParam(':published_date', $array[2]);
    $stmt->bindParam(':show', $array[3]);
    $stmt->execute();
}

$dbh->commit();

$videoEndTime = microtime(true);
echo("Finished loading videos. Runtime: " . date("i:s",$videoEndTime-$videoStartTime) . "<br>");
?>



