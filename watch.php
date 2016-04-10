<!DOCTYPE html>
<html>
  <?php include("head.php"); ?>
<body>
<?php include("header.php"); ?>
    <main class="outer-container container">
        <div id="playerContainer">
                <div id="player">
                </div>
                <div id="controls">
                    <div id="playerButtons" class="buttonContainer">
                        <button class="btn btn-primary" id="videoBtn" type="submit" name="video" value="New video">New video</button>
                        <button class="btn btn-primary" id="playlistBtn" type="submit" name="playlist" value="New playlist">New playlist</button>
                    </div>
                    <div id="videoButtons" class="hide">
                        <button class="btn btn-primary" id="prevVideoBtn" type="submit" name="prevVideo" value="Prev"><span class="glyphicon glyphicon-chevron-left"></span> Prev</button>
                        <button class="btn btn-primary" id="nextVideoBtn" type="submit" name="nextVideo" value="Next">Next <span class="glyphicon glyphicon-chevron-right"></span></button>
                    </div>
                    <div id="playlistButtons" class="hide">
                        <button class="btn btn-warning" id="prevPlaylistBtn" type="submit" name="prevPlaylist" value="Prev"><span class="glyphicon glyphicon-chevron-left"></span> Prev</button>
                        <button class="btn btn-warning" id="nextPlaylistBtn" type="submit" name="nextPlaylist" value="Next">Next <span class="glyphicon glyphicon-chevron-right"></span></button>
                    </div>                   
                </div>
            </div>
        <div class="inner-container container">
            
            <div id="menu">
                <div id="playerListContainer" class="list-group hide">
                    <h3>Queue</h3>
                    <div id="queue-column">
                        <div class="btn-group" id="sort">
                            <button class="btn btn-primary btn-sort" id="shuffle" type="submit" name="shuffle" value="shuffle">Shuffle</button>
                            <button class="btn btn-primary btn-sort" id="sort-asc" type="submit" name="sort-asc" value="sort-asc">Sort Asc</button>      <button class="btn btn-primary btn-sort" id="sort-desc" type="submit" name="sort-desc" value="sort-desc">Sort Desc</button>
                        </div>
                        <ul id="playerList">
                        </ul>
                    </div>           
                </div>
                <div id="control-panel">
                    <div id="options">
                        <h3>Filters</h3>
                        <form id="playerForm" action="assets/php/ajax.php" method="POST">
                            <div id="form-body">
                                <fieldset>
                                    <h4>Type</h4>
                                    <div class="radio">
                                        <label for="video">
                                        <input type="radio" name="table" checked="checked" value="video"> Video
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label for="playlist">
                                        <input type="radio" name="table" value="playlist"> Playlist
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" name="table" value="both"> Both
                                        </label>
                                    </div>
                                </fieldset>

                                <fieldset id="era">
                                    <h4>Era</h4>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" name="published" value="jon-era"> Jon Era
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" name="published" value="dan-era"> Dan Era
                                         </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" name="published" value="both" checked="checked"> Both
                                         </label>
                                    </div>
                                </fieldset>
                                <fieldset name="one-offs">
                                <h4>One-offs</h4>
                                <div class="radio">
                                <label>
                                    <input type="radio" checked="checked"  name="one-off" value="include">Include one-offs
                                </label>
                                </div>
                                <div class="radio">
                                <label>
                                    <input type="radio" name="one-off" value="exclude">Exclude one-offs
                                </label>
                                </div>
                                <div class="radio">
                                <label>
                                    <input type="radio" name="one-off" value="only">Only one-offs
                                </label>
                                </div>
                                </fieldset>
                                <fieldset>
                                <h4>Show</h4>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" checked="checked"  name="show" value="all">All
                                </label>
                                </div>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show" value="gamegrumps">Game Grumps
                                </label>
                                </div>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show" value="steamtrain">Steam Train
                                </label>
                                </div>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show" value="grumpcade">GrumpCade
                                </label>
                                </div>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show" value="gamegrumpsvs">Game Grumps VS
                                </label>
                                </div>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show" value="steamrolled">Steam Rolled
                                </label>
                                </div>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show" value="tableflip">Table Flip
                                </label>
                                </div>
                                <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show" value="animated">Animated
                                </label>
                                </div>
                                </fieldset>
                            </div>

                            <input class="btn btn-primary" id="submit" type="submit" name="submit" value="Grump!">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include("footer.php") ?>

    <script src="assets/js/mediacontroller.js"></script>
    <script src="assets/js/randomgrump.js"></script>
</body>

</html>
