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
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
    <style>
        .container {
            overflow: scroll-y;
            padding: 0;
            width: 1066px;
        }

        .hide {
        display: none;
        }

        .buttonContainer {
        display: inline-block;
        }

        #controls {
            clear: both;
        }

        li.list-group-item {
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: justify;
        }

        .list-group-item {
        padding: 5px 10px !important;
        animation-name: playlistEntries;
        animation-duration: 3s;
        }

        li.list-group-item:hover {
        background-color: #ccc;
        }

        .player-list-control span{
        text-align: center;
        width: 100%;
        }

        #playerContainer {
        display: inline-block;
        float: left;
        margin-bottom: 20px;
        }

        #controls {
        display: block;
        }

        #playerList {
            max-width: 100%;
            max-height: 400px;
            overflow-y: scroll;
            font-size: 0.8em;
            padding-left: 0;
        }

        #playerList li:hover{
            cursor: pointer;
            background-color: aqua;
        }

        .list-group {
            margin-bottom: 0;
        }

        #player-list-btn {
            /*Stuff*/
        }

        #playerListContainer {
            float: right;
            width: 39%;
        }

        #control-panel {
            width: 50%;
            float: left;
        }

    </style>
  </head>
  <body class="container">
    <div id="playerContainer">
      <div id="player">
      </div>
    </div>
    <div id="playerListContainer" class="list-group">
        <ul id="playerList">
        </ul>
    </div>
    <div id="control-panel">
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
          ||
          <button class="btn btn-primary" id="makeBig" type="submit" name="makeBig" value="MAKE BIG">Make big</button>
           <button class="btn btn-primary" id="makeSmall" type="submit" name="makeSmall" value="MAKE SMALL">Make small</button>
        </div>
        <div id="options">
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
    </div>



<script>
    "use strict";

    function MediaController() {
      this.mediaArray;
      this.mediaIndex = 0;
      this.player;
      this.listenerAdded = false;
      this.playlistIndex = 0;
      this.playerListController = {
        head: 0,
        rear: 0,
        active: 0,
        cursor: 0,
        INCREMENT: 50
      };
    }

    MediaController.prototype.mediaLoader = function() {
          console.log("Media controller mediaLoader()");
          console.log(this);
          this.updateActiveMedia();
          if (this.mediaArray[this.mediaIndex].object_type === "video") {
              if(this.listenerAdded == false) {
                console.log("Listener added");
                this.listenerAdded = true;
                this.player.addEventListener('onStateChange', onPlayerStateChange(this));
              }
              console.log("Media controller video loaded");
              $("#playlistButtons").removeClass("buttonContainer").addClass("hide");
              $("#videoButtons").addClass("buttonContainer").removeClass("hide");
              this.player.loadVideoById(this.mediaArray[this.mediaIndex].object_id);
              function onPlayerStateChange(mediaController) {
                  console.log(arguments);
                  return function(event) {
                      if(event.data === 0) {
                          console.log("Woo!");
                          mediaController.incMediaIndex();
                          mediaController.mediaLoader();
                      }
                  }
              }
              console.log(this.player)
          }
          else if (this.mediaArray[this.mediaIndex].object_type === "playlist") {
              if(this.listenerAdded == false) {
                  console.log("Listener added");
                  this.listenerAdded = true;
                  this.player.addEventListener('onStateChange', onPlayerStateChange(this));
              }
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
          }
      }

      MediaController.prototype.printMediaObject = function(index, startPos) {
          var playerList = $("#playerList");
          var listItem = "<li data-indexNum=\"" + index + "\" class=\"list-group-item player-list-btn\">" + (index) + ". " + this.mediaArray[index].object_title + "</li>";
          var loadBtn = "<li id=\"player-list-loader\" data-direction=\"loadMore\" class=\"list-group-item\">Load more</li>";
          /*switch (position) {
              case "before":
                  playerList.find("button:last").before(listItem);
                  break;
              case "after":
                  playerList.find("button:first").after(listItem);
          }*/
          playerList.append(listItem);
          if ((index == this.playerListController.rear) && (index != this.mediaArray.length - 1)) {
              playerList.append(loadBtn);
              $("#player-list-loader").on('click', function(e) {
                  $("#player-list-loader").remove();
                  var elem = e.currentTarget;
                  var i = index;
                  this.playerListController.head = this.playerListController.rear;
                  this.playerListController.rear += this.playerListController.INCREMENT;
                  while (i >= this.playerListController.head && i <= this.playerListController.rear) {
                            this.printMediaObject(i, this.playerListController.head);
                            i++;
                        }
              }.bind(this));


              /*$("#playerList").on('scroll', function(e) {
                  var element = $(e.currentTarget);
                  if (element.scrollTop() + element.innerHeight() >= element[0].scrollHeight-20) {
                        console.log("Scrolled list to bottom");
                        $("#player-list-loader").remove();
                        var i = index;
                        this.playerListController.head = this.playerListController.rear;
                        this.playerListController.rear += this.playerListController.INCREMENT;
                        console.log("Index: " + index + "\n Head: " + this.playerListController.head + " \n Rear: " + this.playerListController.rear);
                        while (i >= this.playerListController.head && i <= this.playerListController.rear) {
                            this.printMediaObject(i, this.playerListController.head);
                            i++;
                        }
                  }
              }.bind(this));*/
          }

          $(".player-list-btn[data-indexnum=" + index + "]").on('click', function(e){
            var btnNum = parseInt(e.currentTarget.dataset.indexnum);
            console.log(btnNum);
            this.mediaIndex = btnNum;
            this.playerListController.cursor = btnNum;
            this.mediaLoader();
            this.updateCursor();
          }.bind(this));
      }

      MediaController.prototype.updateActiveMedia = function() {
          console.log("MediaController updateActiveMedia()");
          $(".player-list-btn").removeClass("active");
          this.playerListController.active = this.mediaIndex
          $("li[data-indexnum=" + this.mediaIndex +"]").addClass("active");
      }

      /*MediaController.prototype.updateCursor = function() {
          console.log("MediaController updateActiveMedia()");
          $(".player-list-btn").removeClass("cursor");
          $("li[data-indexnum=" + this.mediaIndex +"]").addClass("cursor");
      }

      MediaController.prototype.moveCursor = function(direction) {
          switch(direction) {
            case "up":
                if (this.playerListController.cursor === 0) {
                    this.playerListController.cursor = this.mediaArray.length - 1;
                  }
                  else {
                    this.playerListController.cursor--;
                }
              break;
            case "down":
                if (this.playerListController.cursor + 1 === this.mediaArray.length) {
                    this.playerListController.cursor = 0;
                }
                  else {
                    this.playerListController.cursor++;
                }
              break;
          }
          $(".player-list-btn").removeClass("cursor");
          $("li[data-indexnum=" + this.playerListController.cursor +"]").addClass("cursor");
      }*/

      MediaController.prototype.printMediaList = function() {
          console.log("MediaController printMediaList()");
          console.log(this);
          this.playerListController.head = 0;
          this.playerListController.cursor = 0;
          if (this.mediaArray.length < this.playerListController.INCREMENT) {
            this.playerListController.rear = this.mediaArray.length - 1;
          } else {
            this.playerListController.rear = this.playerListController.INCREMENT - 1;
          }
          document.getElementById("playerList").innerHTML = "";
          /*document.getElementById("playerList").innerHTML += "<button type=\"button\" data-direction=\"up\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-up\"></span></button>";
          document.getElementById("playerList").innerHTML += "<button type=\"button\" data-direction=\"down\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-down\"></span></button>";*/
          var i = 0;
          console.log(this.playerListController);
          while (i >= this.playerListController.head && i <= this.playerListController.rear) {
              this.printMediaObject(i, 0);
              i++;
          }
          $("li[data-indexnum=" + this.playerListController.active +"]").addClass("active");
          // document.getElementById("playerList").innerHTML += "<button type=\"button\" class=\"player-list-control list-group-item\"><span class=\"glyphicon glyphicon-chevron-down\"></span></button>";
          $(".player-list-btn").on('click', function(e){
            var btnNum = e.currentTarget.dataset.indexnum;
            console.log(btnNum);
            this.mediaIndex = parseInt(btnNum);
            this.mediaLoader();
          }.bind(this));
          /*$(".player-list-control").on('click', function(e){
            console.log(this);
            var btnDirection = e.currentTarget.dataset.direction;
            var activeOutOfRange = false;
            console.log("Before::  Head: " + this.playerListController.head +
            " Cursor: " + this.playerListController.cursor +
            " Rear: " + this.playerListController.rear +
            " Active: " + this.mediaIndex);
            if (btnDirection === "down") {
                if (this.playerListController.cursor === this.playerListController.rear) {
                    $("li[data-indexnum=" + this.playerListController.head +"]").remove();
                    if (this.playerListController.head + 1 === this.mediaArray.length) {
                        this.playerListController.head = 0;
                    }
                      else {
                        this.playerListController.head++;
                    }
                    if (this.playerListController.rear + 1 === this.mediaArray.length) {
                        this.playerListController.rear = 0;
                    }
                      else {
                        this.playerListController.rear++;
                    }
                    this.printMediaObject(this.playerListController.rear, "before");
                    this.updateActiveMedia();
                }
                this.moveCursor("down");
            }
            else if (btnDirection === "up") {
                if (this.playerListController.cursor === this.playerListController.head) {
                    $("li[data-indexnum=" + this.playerListController.rear +"]").remove();
                    if (this.playerListController.head === 0) {
                        this.playerListController.head = this.mediaArray.length - 1;
                      }
                      else {
                        this.playerListController.head--;
                    }
                    if (this.playerListController.rear === 0) {
                        this.playerListController.rear = this.mediaArray.length - 1;
                      }
                      else {
                        this.playerListController.rear--;
                    }
                    this.printMediaObject(this.playerListController.head,"after");
                    this.updateActiveMedia();
                }
                this.moveCursor("up");
                // this.updateActiveMedia();
                // this.decMediaIndex();
                // this.mediaLoader();
            }
            console.log("After::  Head: " + this.playerListController.head +
            " Cursor: " + this.playerListController.cursor +
            " Rear: " + this.playerListController.rear +
            " Active: " + this.mediaIndex);
          }.bind(this));*/
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

    var mc = new MediaController();
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
      mc.player = player;
    }

    function onPlayerReady(event) {
      console.log("Player loaded");
      mc.player = player;
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
          mc.mediaIndex = 0;
          mc.mediaArray = JSON.parse(resultString[resultString.length - 1]);
          mc.printMediaList();
          mc.mediaLoader();
        });
    });

    $('#nextVideoBtn').on('click', function(e){
        mc.incMediaIndex();
        mc.mediaLoader();
    });

    $('#prevVideoBtn').on('click', function(e){
        mc.decMediaIndex();
        mc.mediaLoader();
    });

    $('#nextPlaylistBtn').on('click', function(e){
        mc.incMediaIndex();
        mc.mediaLoader();
    });

    $('#prevPlaylistBtn').on('click', function(e){
        mc.decMediaIndex();
        mc.mediaLoader();
    });

    $('#makeBig').on('click', function(e){
        mc.player.setSize(1066, 480);
    });

    $('#makeSmall').on('click', function(e){
        mc.player.setSize(640, 390);
    });

    $('#objectTest').on('click', function(e){
        shuffleOne("video").done(function(result) {
          mc.player.loadVideoById(result);
        });
    });

    $("input[name=show]").click(function(e) {
        if (this.value == "all") {
            $("input[name=show]").prop("checked", false);
            $("input[value=all]").prop("checked", true);
        } else if (this.value != "all") {
            $("input[value=all]").prop("checked", false);
        }
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
