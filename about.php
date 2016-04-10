<!DOCTYPE html>
<html>
  <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="initial-scale=1">
    <script src="https://www.youtube.com/iframe_api"></script>
    <title>Random Grump - Watch</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/randomgrump.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,300,700' rel='stylesheet' type='text/css'>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style>
        .subheading {
            display: inline;
            color: #444;
        }
    </style>
  </head>
<body>
<?php include("header.php"); ?>
    <main id="about" class="outer-container container">
        <h1>RandomGrump and You</h1>
        <section>
            <h2>What is RandomGrump?</h2>
            <p>Want to watch some Game Grumps, but can't decide on a video or series? RandomGrump is for you! Select your filters and RandomGrump will return a queue of GameGrumps videos and/or playlists for you to watch.</p>
            <section>
                <h2>How do I use the filters?</h2>
                <p class="subheading">Maybe you only want to watch Steam Train videos, or maybe you only want to watch series featuring Jon. RandomGrump can make that happen.</p>

                <h4>Type </h4><p>Type decides whether you want to watch videos, playlists, or both.</p>
                <ul>
                    <li><b>Video</b> - The queue returned will contain videos, completely out of any sort of order. The videos may be one-offs or from the middle of a series.</li>
                    <li><b>Playlist</b> - The queue returned will contain playlists. A playlist will play in order from front to back before continuing to the next playlist.</li>
                    <li><b>Both</b> - The queue will contain both playlists and videos.</li>
                </ul>
                
                <h4>Era</h4>
                <p>Determines if the videos/playlists returned should be from before or after the Jon/Dan changeover.</p>
                <ul>
                    <li><b>Jon Era</b> - Classic Grumps! All videos and playlists from before Dan joined the show.</li>
                    <li><b>Dan Era</b> - New Grumps! All videos and playlists from after Dan joined the show.</li>
                    <li><b>Both</b> - Total Grumps! All videos and playlists on the channel from any point in time.</li>
                </ul>
                
                <h4>One-offs</h4>
                <p>Determines whether you'd like videos from the "One-OffS" playlist to show up in your results. Note: This option is disabled if the "Type" is set to "playlist".</p>
                <ul>
                    <li><b>Include one-offs</b> - Your queue will include videos from the one-offs playlist.</li>
                    <li><b>Exclude one-offs</b> - Your queue will not include videos from the one-offs playlist.</li>
                    <li><b>Only one-offs</b> - Your queue will only include videos from the one-offs playlist.</li>
                </ul>
                
                <h4>Show</h4>
                <p>Determines if the videos/playlists returned should only be from a specific show or combination of shows, such as only Steam Train or only Grumpcade <em>and</em> Game Grumps. Mix and match!</p>           
            </section>
            <section>
                <h2>What do the "Next" and "Prev" buttons do?</h2>
                <p>The large Prev/Next buttons below the player are for navigating the queue. If you want to go to the next video in a playlist, use the buttons in the YouTube player itself.</p>
            </section>
            <section>
                <h2>Why is there a donate button? Who/what am I donating to?</h2>
                <p>Donations go to pay for hosting fees for RandomGrump and sandwiches for the developer.</p>
            </section>
            
            <section>
                <h2>Why is this a thing?</h2>
                <p>I really like Game Grumps.</p>
            </section>
        </section>
    </main>
<?php include("footer.php") ?>
</body>
