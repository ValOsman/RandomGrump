<?php
set_time_limit(0);
$channelName = "gamegrumps";
$MY_KEY = "AIzaSyCg1GMcq_tVjSsiykZH6xTU0ZDlTOWjkV8";
$MAX_RESULTS = 50;
$startTime = microtime(true);

$request = 'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=' . $channelName . '&key='. $MY_KEY;
$channel = json_decode(file_get_contents($request), true);
$channelId = $channel["items"][0]["id"];
$uploadsId = $channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"];

$request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails,snippet&playlistId=' . $uploadsId . '&maxResults=' . $MAX_RESULTS . '&key=' . $MY_KEY;
//echo($request);
$uploadsResults = json_decode(file_get_contents($request), true);
$nextPageToken = $uploadsResults["nextPageToken"];
$uploads = $uploadsResults["items"];
while ($nextPageToken != "") {
    $request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails,snippet&playlistId=' . $uploadsId . '&maxResults=' . $MAX_RESULTS . '&pageToken='. $nextPageToken . '&key='. $MY_KEY;
    $uploadsResults = json_decode(file_get_contents($request), true);
    if (array_key_exists("nextPageToken",$uploadsResults)) {
      $nextPageToken = $uploadsResults["nextPageToken"];      
    } else {
      $nextPageToken = "";
    }
    $uploads = array_merge($uploads, $uploadsResults["items"]);
}

//echo(sizeof($uploads));

function getShow($title) { 
    $titleArray = explode("- ", $title);
    $titleStart = $titleArray[0];
    $titleEnd = $titleArray[sizeof($titleArray) - 1];
    switch (true) {
        case strpos($titleStart,"Game Grumps VS") !== false || strpos($titleEnd,"Game Grumps VS") !== false:
            return "Game Grumps VS";
            break;
        case strpos($titleEnd, "Game Grumps") !== false || strpos($titleEnd, "GameGrumps") !== false:
            return "Game Grumps";
            break;
        case strpos($titleEnd,"Steam Train") !== false:
            return $titleEnd;
            break;
        case strpos($titleEnd,"Steam Rolled") !== false:
            return $titleEnd;
            break;
        case strpos($titleEnd, "Grumpcade") !== false:
            return "Grumpcade";
            break;
        case strpos($titleStart,"Animated") !== false || strpos($titleStart,"Animation") !== false:
            return "Animated";
            break;
        case strpos($titleEnd,"Table Flip") !== false:
            return $titleEnd;
            break;
        default:
            return "Other";
            break;
    }
}


for ($i = 0; $i < sizeof($uploads); $i++) {
    //$request = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=' . $uploads[$i]["id"] . '&maxResults=1&key=AIzaSyCg1GMcq_tVjSsiykZH6xTU0ZDlTOWjkV8';
    //$series = json_decode(file_get_contents($request), true)["items"][0]["snippet"]["title"];
    //$seriesArray= explode(" - ", $series);
    //echo($series);
    $id = $uploads[$i]["contentDetails"]["videoId"];
    $snippet = $uploads[$i]["snippet"];
    $title = $snippet["title"];
    $show = getShow($title);
    $videosFormatted[] = array($id, $title, $snippet["publishedAt"], $show);
    //echo('<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=' . $playlists[$i]["id"] . '" frameborder="0" allowfullscreen></iframe>');
}


try {
    $dbh = new PDO("sqlite:randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}  

$query = "DROP TABLE IF EXISTS video";
$dbh->exec($query);

$query = "CREATE TABLE IF NOT EXISTS video(video_id TEXT, title TEXT, published_date DATE, show TEXT)";
$dbh->exec($query);

foreach ($videosFormatted as $video=>$array) {
    $video_id = $dbh->quote($array[0]);
    $title = $dbh->quote($array[1]);
    $published_date = $dbh->quote($array[2]);
    $show = $dbh->quote($array[3]);
    $query = "INSERT INTO video (video_id, title, published_date, show) VALUES ($video_id, $title, $published_date, $show)";
    $dbh->exec($query);
}

$randInt = mt_rand(1, 50);
$query = "SELECT video_id FROM video WHERE rowid = '$randInt'";
$stmt = $dbh->prepare($query);
$stmt->execute();
$randId = $stmt->fetch()[0];


echo("Complete: " . $randId);

$endTime = microtime(true);
echo("<br><br> Runtime: " . date("i:s",$endTime-$startTime));
?>



