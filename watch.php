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
    }

    MediaController.prototype.mediaLoader = function() {
          console.log("Media controller mediaLoader()");
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
    


    var mediaController = new MediaController();
    var player;

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

    // 5. The API calls this function when the player's state changes.
    // function onPlayerStateChange(event) {}

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
          data: {'getList': 'true', 'table': query["tableName"], 'show': query["show"]}            
        });  
    }

    function printObject(index, position) {
      var $playerList = $("#playerList");
      var button = "<button type=\"button\" data-indexNum=\"" + index + "\" class=\"list-group-item player-list-btn\">" + (index+1) + ". " + resultArray[index].object_title + "</button>";
      switch (position) {
          case "before":
              $playerList.find("button:last").before(button);
              break;
          case "after":
              $playerList.find("button:first").after(button);
      }      
      $(".player-list-btn[data-indexnum=" + index + "]").on('click', function(e){
        console.log($(this)[0].dataset.indexnum);
        objectIndex = parseInt($(this)[0].dataset.indexnum);
        objectLoader();
      });      
    }

    function printObjectList() {
      var startTime = Date.now();
      MAX_PLAYERLIST_COUNT = 15;
      playerListHead = 0;
      playerListRear = 0;
      if (resultArray.length < MAX_PLAYERLIST_COUNT) {
        playerListRear = resultArray.length - 1;
      } else {
        playerListRear = MAX_PLAYERLIST_COUNT-1;
      }
      //var playerListIndex = 0;
      document.getElementById("playerList").innerHTML = "";
      document.getElementById("playerList").innerHTML += "<button type=\"button\" data-direction=\"up\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-up\"></span></button>";
      document.getElementById("playerList").innerHTML += "<button type=\"button\" data-direction=\"down\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-down\"></span></button>";
      //$("#playerList").addClass("list-group");
      var i = 0;
      while (i >= playerListHead && i <= playerListRear) {
          printObject(i, "before");
          i++;
      }
      $("button[data-indexnum=" + 0 +"]").addClass("active");
      var EndTime = endTime = Date.now();
      console.log("Print time elapsed:" + (endTime - startTime));
      //console.log(resultArray);
      // document.getElementById("playerList").innerHTML += "<button type=\"button\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-down\"></span></button>";
      $(".player-list-btn").on('click', function(e){
        console.log($(this)[0].dataset.indexnum);
        objectIndex = parseInt($(this)[0].dataset.indexnum);
        objectLoader();
      //   //console.log("Player list button clicked");
      //   //console.log(objectId);
      });
      $(".player-list-control").on('click', function(e){
        if ($(this)[0].dataset.direction === "down") {
          $("button[data-indexnum=" + playerListHead +"]").remove();
          if (playerListHead + 1 === resultArray.length) {
              playerListHead = 0;
          }
            else {
              playerListHead++;
          }
          if (playerListRear + 1 === resultArray.length) {
              playerListRear = 0;
          }
            else {
              playerListRear++;
          }
          printObject(playerListRear, "before");
          updateObjectList()
          //incrementObjectIndex();
          //objectLoader();
        }
        else if ($(this)[0].dataset.direction === "up") {
          $("button[data-indexnum=" + playerListRear +"]").remove();
            if (playerListHead === 0) {
                playerListHead = resultArray.length - 1;
              }
              else {
                playerListHead--;
            }
            if (playerListRear === 0) {
                playerListRear = resultArray.length - 1;
              }
              else {
                playerListRear--;
            }
            console.log(playerListHead)
            printObject(playerListHead,"after");
            updateObjectList()
            //decrementObjectIndex();
            //objectLoader();
        }
      });
    }

    function updateObjectList() {
      $(".player-list-btn").removeClass("active");
      $("button[data-indexnum=" + objectIndex +"]").addClass("active");
    }


    //BUTTON BINDINGS
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
          tableName: formArray[0]['value']
        };
        var showArray = []
        for (var i = 1; i < formArray.length; i++) {
            if (formArray[i]['name'] === 'show')
              showArray.push(formArray[i]['value']);
        }
        query["show"] = JSON.stringify(showArray);
        console.log(query);
        returnQuery(query).done(function(result) {
          var resultString = result.split("xxx");
          var resultArray = JSON.parse(resultString[resultString.length - 1]);
          console.log("POST: " + resultString[0]);
          console.log("$Shows: " + resultString[1]);
          console.log("Query:" + resultString[2]);
          var objectIndex = 0;
          //printObjectList();
          //objectLoader();
          mediaController.mediaArray = resultArray;
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