<?php
    ob_start();
    set_time_limit(0);
    $dbStartTime = microtime(true);
    echo("Started loading database... <br>");
    require('yt-key.php');
    require('load-videos.php');
    require('load-playlists.php');
    require('videos-in-playlists.php');

    $dbEndTime = microtime(true);
    echo("Finished loading database. Total runtime: " . date("i:s",$dbEndTime-$dbStartTime) . "<br>");
    ob_end_flush();
?>