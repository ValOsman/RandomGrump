<?php
    set_time_limit(0);
    $dbStartTime = microtime(true);
    $runtime = date("h:i:sa") . "\n";
    echo("Running at " . date("h:i:sa"));
    file_put_contents("database-log.txt", $runtime, FILE_APPEND);
    echo("Started loading database... <br>");
    $channelName = "gamegrumps";
    $MAX_RESULTS = 50;

    //Get channel ID and uploads playlist
    require('yt-key.php');  
    $request = 'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=' . $channelName . '&key='. MY_KEY;
    $channel = json_decode(file_get_contents($request), true);
    $channelId = $channel["items"][0]["id"];
    $uploadsId = $channel["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"];
      
    require('load-videos.php');
    require('load-playlists.php');
    require('videos-in-playlists.php');

    $dbEndTime = microtime(true);
    echo("Finished loading database. Total runtime: " . date("i:s",$dbEndTime-$dbStartTime) . "<br>");
?>