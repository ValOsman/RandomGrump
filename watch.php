<?php

try {
    $dbh = new PDO("sqlite:randomgrumps.db");
}
catch(PDOException $e) {
    echo $e->getMessage();
}

function space() {
  echo ("<br><br>");
}

// function getOneEntry($tableName, $dbh) {
//     $query = "SELECT count(*) FROM $tableName";
//     $stmt = $dbh->query($query);
//     $stmt->execute();
//     $numRows = $stmt->fetch()[0];

//     $randInt = mt_rand(1, $numRows);
//     $query = "SELECT " . $tableName . "_id FROM $tableName WHERE rowid = '$randInt'";
//     $stmt = $dbh->prepare($query);
//     $stmt->execute();
//     return $stmt->fetch()[0];
// }

?>


<!DOCTYPE html>
<html>
  <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://www.youtube.com/iframe_api"></script>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css"></link>
    <style>
      .hide {
        display: none;
      }

      .cursor {
        background-color: green;
      }

      .buttonContainer {
        display: inline-block;
      }

      button.list-group-item {
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: justify;
      }

/*      @keyframes playlistEntries {
        from{opacity: 0;}
        to {opacity: 100;}
      }   */  
  

      .list-group-item {
        padding: 5px 10px !important;
        animation-name: playlistEntries;
        animation-duration: 3s;
      }

      button.list-group-item:hover {
        background-color: #ccc;
      }

      .player-list-control span{
        text-align: center;
        width: 100%;
      }

      #playerContainer {
        display: inline-block;
      }

      #controls { 
        display: block;
      }


    </style>
  </head>
  <body class="container-fluid">
    <div id="playerContainer">
      <div id="player">        
      </div>
    </div>
    <div id="playerList" class="col-md-3 pull-right list-group">
    </div>
    <div id="controls">
      <div id="playerButtons" class="buttonContainer">
        <input class="btn btn-primary" id="videoBtn" type="submit" name="video" value="New video" />
        <input class="btn btn-primary" id="playlistBtn" type="submit" name="playlist" value="New playlist" />
      </div>
      <div id="videoButtons" class="hide">
        <input class="btn btn-primary" id="nextVideoBtn" type="submit" name="nextVideo" value="Next video" />
        <input class="btn btn-primary" id="prevVideoBtn" type="submit" name="prevVideo" value="Previous video" />
      </div>
      <div id="playlistButtons" class="hide">
        <input class="btn btn-primary" id="nextPlaylistBtn" type="submit" name="nextPlaylist" value="Next playlist" />
        <input class="btn btn-primary" id="prevPlaylistBtn" type="submit" name="prevPlaylist" value="Previous playlist" />
      </div>
      ||
      <input class="btn btn-primary" id="makeBig" type="submit" name="makeBig" value="MAKE BIG" />
      ||
      <input class="btn btn-primary" id="objectTest" type="submit" name="objectTest" value="Object Test" />
    </div>
    <div>
      <h3>Options</h3>
      <form id="playerForm" action="ajax.php" method="POST">
        <input type="radio" name="table" checked="checked" value="video">Video<br>
        <input type="radio" name="table" value="playlist">Playlist<br>
        <input type="radio" name="table" value="both">Both<br>
        <hr>
        <input type="radio" name="published" value="both" checked="checked">Both<br>
        <input type="radio" name="published" value="jon-era">Jon Era<br>
        <input type="radio" name="published" value="dan-era">Dan Era<br>
        <hr>
        <input type="checkbox" name="show" value="all">All<br>
        <input type="checkbox" name="show" value="gamegrumps">Game Grumps<br>
        <input type="checkbox" name="show" value="steamtrain">Steam Train<br>
        <input type="checkbox" checked="checked" name="show" value="grumpcade">GrumpCade<br>
        <input type="checkbox" name="show" value="gamegrumpsvs">Game Grumps VS<br>        
        <input type="checkbox" name="show" value="steamrolled">Steam Rolled<br>
        <input type="checkbox" name="show" value="tableflip">Table Flip<br>
        <input type="checkbox" name="show" value="animated">Animated<br>        

        <input class="btn btn-primary" id="submit" type="submit" name="submit" value="Submit">
      </form>
    </div>


<script>
    "use strict";

    function MediaController() {
      this.mediaArray;
      this.mediaIndex = 0;
      this.player;
      this.playlistIndex = 0;
      this.playerListController = {
        playerListHead: 0,
        playerListRear: 0,
        playerListCursor: 0,
        MAX_PLAYERLIST_COUNT: 15
      };
    }

    MediaController.prototype.mediaLoader = function() {
          console.log("Media controller mediaLoader()");
          this.updateMediaList();
          if (this.mediaArray[this.mediaIndex].object_type === "video") {
              console.log("Media controller video loaded");
              $("#playlistButtons").removeClass("buttonContainer").addClass("hide");
              $("#videoButtons").addClass("buttonContainer").removeClass("hide");
              this.player.loadVideoById(this.mediaArray[this.mediaIndex].object_id);
              //console.log(this);
              function onPlayerStateChange(mediaController) {
                  return function(event) {
                      if(event.data === 0) {
                          console.log("Woo!");
                          mediaController.incMediaIndex();
                          mediaController.mediaLoader();
                      }
                  }
              }
              this.player.addEventListener('onStateChange', onPlayerStateChange(this));
          } 
          else if (this.mediaArray[this.mediaIndex].object_type === "playlist") {
              console.log("Media controller playlist loaded");
              $("#videoButtons").removeClass("buttonContainer").addClass("hide");
              $("#playlistButtons").addClass("buttonContainer").removeClass("hide");
              this.player.loadPlaylist({
                list: this.mediaArray[this.mediaIndex].object_id,
                listType: "playlist"
              });
              function onPlayerStateChange(mediaController) {
                  return function(event) {
                    if(event.data === 1 || event.data === -1) {
                      console.log("Playlist video cued")
                      mediaController.playlistIndex = mediaController.player.getPlaylistIndex();
                    }
                    if(event.data === 0 && mediaController.playlistIndex === mediaController.player.getPlaylist().length - 1) {
                      console.log("Load the next media object");
                      mediaController.playlistIndex = 0;
                      mediaController.incMediaIndex();
                      mediaController.mediaLoader();
                    }
                  }
              }
              this.player.addEventListener('onStateChange', onPlayerStateChange(this));
          }
      }

      MediaController.prototype.printMediaObject = function(index, position) {
          console.log("MediaController printMediaObject()");
          console.log(mediaController);
          console.log(index);
          var $playerList = $("#playerList");
          var button = "<button type=\"button\" data-indexNum=\"" + index + "\" class=\"list-group-item player-list-btn\">" + (index) + ". " + mediaController.mediaArray[index].object_title + "</button>";
          switch (position) {
              case "before":
                  $playerList.find("button:last").before(button);
                  break;
              case "after":
                  $playerList.find("button:first").after(button);
          }      
          $(".player-list-btn[data-indexnum=" + index + "]").on('click', function(e){
            console.log($(this)[0].dataset.indexnum);
            mediaController.mediaIndex = parseInt($(this)[0].dataset.indexnum);
            mediaController.mediaLoader();
          });
      }

      MediaController.prototype.updateMediaList = function() {
          console.log("MediaController updateMediaList()");
          $(".player-list-btn").removeClass("active");
          $("button[data-indexnum=" + this.mediaIndex +"]").addClass("active");
      }

      MediaController.prototype.moveCursor = function(direction) {
          switch(direction) {
            case "up":
                if (mediaController.playerListController.playerListCursor === 0) {
                    mediaController.playerListController.playerListCursor = mediaController.mediaArray.length - 1;
                  }
                  else {
                    mediaController.playerListController.playerListCursor--;
                }
              break;
            case "down":
                if (mediaController.playerListController.playerListCursor + 1 === mediaController.mediaArray.length) {
                    mediaController.playerListController.playerListCursor = 0;
                }
                  else {
                    mediaController.playerListController.playerListCursor++;
                }
              break;
          }
          $(".player-list-btn").removeClass("cursor");
          $("button[data-indexnum=" + this.playerListController.playerListCursor +"]").addClass("cursor");
      }

      MediaController.prototype.printMediaList = function() {
          console.log("MediaController printMediaList()");
          if (this.mediaArray.length < this.playerListController.MAX_PLAYERLIST_COUNT) {
            this.playerListController.playerListRear = this.mediaArray.length - 1;
          } else {
            this.playerListController.playerListRear = this.playerListController.MAX_PLAYERLIST_COUNT - 1;
          }
          document.getElementById("playerList").innerHTML = "";
          document.getElementById("playerList").innerHTML += "<button type=\"button\" data-direction=\"up\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-up\"></span></button>";
          document.getElementById("playerList").innerHTML += "<button type=\"button\" data-direction=\"down\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-down\"></span></button>";
          var i = 0;
          console.log(this.playerListController);
          while (i >= this.playerListController.playerListHead && i <= this.playerListController.playerListRear) {
              this.printMediaObject(i, "before");
              i++;
          }
          $("button[data-indexnum=" + 0 +"]").addClass("active");
          // document.getElementById("playerList").innerHTML += "<button type=\"button\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-down\"></span></button>";
          $(".player-list-btn").on('click', function(e){
            console.log($(this)[0].dataset.indexnum);
            this.mediaIndex = parseInt($(this)[0].dataset.indexnum);
            mediaController.mediaLoader();
          });
          $(".player-list-control").on('click', function(e){
            console.log("Before::  Head: " + mediaController.playerListController.playerListHead +
            " Cursor: " + mediaController.playerListController.playerListCursor + 
            " Rear: " + mediaController.playerListController.playerListRear);
            if ($(this)[0].dataset.direction === "down") {
                if (mediaController.playerListController.playerListCursor === mediaController.playerListController.playerListRear) {   
                    $("button[data-indexnum=" + mediaController.playerListController.playerListHead +"]").remove();                 
                    if (mediaController.playerListController.playerListHead + 1 === mediaController.mediaArray.length) {
                        mediaController.playerListController.playerListHead = 0;
                    }
                      else {
                        mediaController.playerListController.playerListHead++;
                    }
                    if (mediaController.playerListController.playerListRear + 1 === mediaController.mediaArray.length) {
                        mediaController.playerListController.playerListRear = 0;
                    }
                      else {
                        mediaController.playerListController.playerListRear++;
                    }                            
                    mediaController.printMediaObject(mediaController.playerListController.playerListRear, "before");
                }
                mediaController.moveCursor("down");
                //mediaController.updateMediaList();
                //mediaController.incMediaIndex();
                //mediaController.mediaLoader();
            }
            else if ($(this)[0].dataset.direction === "up") {
                if (mediaController.playerListController.playerListCursor === mediaController.playerListController.playerListHead) {
                    $("button[data-indexnum=" + mediaController.playerListController.playerListRear +"]").remove();
                    if (mediaController.playerListController.playerListHead === 0) {
                        mediaController.playerListController.playerListHead = mediaController.mediaArray.length - 1;
                      }
                      else {
                        mediaController.playerListController.playerListHead--;
                    }
                    if (mediaController.playerListController.playerListRear === 0) {
                        mediaController.playerListController.playerListRear = mediaController.mediaArray.length - 1;
                      }
                      else {
                        mediaController.playerListController.playerListRear--;
                    }
                    mediaController.printMediaObject(mediaController.playerListController.playerListHead,"after");
                }
                mediaController.moveCursor("up");
                // mediaController.updateMediaList();
                // mediaController.decMediaIndex();
                // mediaController.mediaLoader();
            }
            console.log("After::  Head: " + mediaController.playerListController.playerListHead +
            " Cursor: " + mediaController.playerListController.playerListCursor + 
            " Rear: " + mediaController.playerListController.playerListRear);
          });
      }

      MediaController.prototype.incMediaIndex = function() {
        if (this.mediaIndex + 1 === this.mediaArray.length) {
          this.mediaIndex = 0;
        }
        else {
          this.mediaIndex++;
        }
      }

      MediaController.prototype.decMediaIndex = function() {
        if (this.mediaIndex === 0) {
          this.mediaIndex = this.mediaArray.length - 1;
        }
        else {
          this.mediaIndex--;
        }
      }

//////////////////////////////////////////////////

    var mediaController = new MediaController();
    var player;
    const MAX_PLAYERLIST_COUNT = 15;

    function onYouTubeIframeAPIReady() {
      var initialVideo;
      shuffleOne("video").done(function(result) {
          initialVideo = result;      
          player = new YT.Player('player', {
            height: '390',
            width: '640',
            videoId: initialVideo,
            events: {
              'onReady': onPlayerReady
            }
          });
      });
      mediaController.player = player;
    }

    function onPlayerReady(event) {   
      console.log("Player loaded");
      mediaController.player = player;
    }

    function shuffleOne(tableName) {
          return $.ajax({
          url: 'ajax.php',
          type: 'post',
          data: {'getOne': 'true', 'table': tableName}
        });                
    }

    function returnQuery(query) {
          return $.ajax({
          url: 'ajax.php',
          type: 'POST',
          data: {'getList': 'true', 'table': query["tableName"], 'published': query['published'], 'show': query["show"]}            
        });  
    }

//BUTTON BINDINGS
///////////////////////////////////////////////////
    $('#videoBtn').on('click', function(e){
        shuffleOne("video").done(function(result) {
          player.loadVideoById(result);
        });
    });

    $('#playlistBtn').on('click', function(e){
        shuffleOne("playlist").done(function(result) {
          player.loadPlaylist({
            list: result, 
            listType: "playlist"
          });
        });
    });

    $('#playerForm').on('submit', function(e){
        $("#playerButtons").removeClass("buttonContainer").addClass("hide");
        e.preventDefault();
        console.log($(this).serializeArray())
        var formArray = $(this).serializeArray();
        var query = {
          tableName: formArray[0]['value'],
          published: formArray[1]['value']
        };
        var showArray = []
        for (var i = 2; i < formArray.length; i++) {
            if (formArray[i]['name'] === 'show')
              showArray.push(formArray[i]['value']);
        }
        query["show"] = JSON.stringify(showArray);
        console.log(query);
        returnQuery(query).done(function(result) {
          //console.log(result);
          var resultString = result.split("xxx");
          console.log("POST: " + resultString[0]);
          console.log("$Shows: " + resultString[1]);
          console.log("Query:" + resultString[2]);
          mediaController.mediaIndex = 0;
          mediaController.mediaArray = JSON.parse(resultString[resultString.length - 1]);
          mediaController.printMediaList();
          mediaController.mediaLoader();
        });
    });

    $('#nextVideoBtn').on('click', function(e){
        mediaController.incMediaIndex();
        mediaController.mediaLoader();
    });

    $('#prevVideoBtn').on('click', function(e){
        mediaController.decMediaIndex();
        mediaController.mediaLoader();
    });

    $('#nextPlaylistBtn').on('click', function(e){
        mediaController.incMediaIndex();
        mediaController.mediaLoader();
    });

    $('#prevPlaylistBtn').on('click', function(e){
        mediaController.decMediaIndex();
        mediaController.mediaLoader();
    });

    $('#makeBig').on('click', function(e){
        player.setSize(1280, 720);
    });

    $('#objectTest').on('click', function(e){
        shuffleOne("video").done(function(result) {
          mediaController.player.loadVideoById(result);
        });
    });


    //Event listener debugger
    //(function () {
    //     var ael = Node.prototype.addEventListener,
    //         rel = Node.prototype.removeEventListener;
    //     Node.prototype.addEventListener = function (a, b, c) {
    //         console.log('Listener', 'added', this, a, b, c);
    //         ael.apply(this, arguments);
    //     };
    //     Node.prototype.removeEventListener = function (a, b, c) {
    //         console.log('Listener', 'removed', this, a, b, c);
    //         rel.apply(this, arguments);
    //     };
    // }(
    //));
</script>
  </body>
</html>