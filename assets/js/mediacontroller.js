'use strict';

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

MediaController.prototype.mediaLoader = function () {
    console.log("Media controller mediaLoader()");
    console.log(this);
    this.updateActiveMedia();
    if (this.listenerAdded === false) {
        console.log("Video listener added");
        this.listenerAdded = true;
        this.player.addEventListener('onStateChange', onPlayerStateChange(this));
    }
    console.log(this.mediaArray[this.mediaIndex].object_title);
    if (this.mediaArray[this.mediaIndex].object_type === "video") {
        console.log("Media controller video loaded");
        $("#playlistButtons").removeClass("buttonContainer").addClass("hide");
        $("#videoButtons").addClass("buttonContainer").removeClass("hide");
        this.player.loadVideoById(this.mediaArray[this.mediaIndex].object_id);
    } else if (this.mediaArray[this.mediaIndex].object_type === "playlist") {
        console.log("Media controller playlist loaded");
        $("#videoButtons").removeClass("buttonContainer").addClass("hide");
        $("#playlistButtons").addClass("buttonContainer").removeClass("hide");
        this.player.loadPlaylist({
            list: this.mediaArray[this.mediaIndex].object_id,
            listType: "playlist"
        });
    }

    function onPlayerStateChange(mc) {
        return function(event) {
            if (event.data === 0 &&
                mc.mediaArray[mc.mediaIndex].object_type === "video") {
                console.log("Video has ended.");
                console.log(mc.player);
                mc.incMediaIndex();
                mc.mediaLoader();
            } else if (event.data === 1 &&
                mc.mediaArray[mc.mediaIndex].object_type === "playlist") {
                console.log("Playlist video playing")
                mc.playlistIndex = mc.player.getPlaylistIndex();
                console.log(mc.player.getPlaylist().length);
            } else if (event.data === 0 &&
                mc.playlistIndex === mc.player.getPlaylist().length - 1) {
                console.log("Playlist has ended");
                mc.playlistIndex = 0;
                mc.incMediaIndex();
                mc.mediaLoader();
            }
        }
    }
}

MediaController.prototype.printMediaObject = function(index, startPos) {
    var playerList = $("#playerList");
    var listItem = "<li data-indexNum=\"" + index + "\" class=\"list-group-item player-list-btn\">" + (index+1) + ". " + this.mediaArray[index].object_title + "</li>";
    var loadBtn = "<li id=\"player-list-loader\" data-direction=\"loadMore\" class=\"list-group-item\">Load more</li>";
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
    }

    $(".player-list-btn[data-indexnum=" + index + "]").on('click', function(e) {
        var btnNum = parseInt(e.currentTarget.dataset.indexnum);
        console.log(btnNum);
        this.mediaIndex = btnNum;
        this.playerListController.cursor = btnNum;
        this.mediaLoader();
    }.bind(this));
}

MediaController.prototype.updateActiveMedia = function() {
    console.log("MediaController updateActiveMedia()");
    $(".player-list-btn").removeClass("active");
    this.playerListController.active = this.mediaIndex
    $("li[data-indexnum=" + this.mediaIndex + "]").addClass("active");
}

MediaController.prototype.printMediaList = function() {
    console.log("MediaController printMediaList()");
    //console.log(this);
    this.playerListController.head = 0;
    this.playerListController.cursor = 0;
    if (this.mediaArray.length < this.playerListController.INCREMENT) {
        this.playerListController.rear = this.mediaArray.length - 1;
    } else {
        this.playerListController.rear = this.playerListController.INCREMENT - 1;
    }
    document.getElementById("playerList").innerHTML = "";
    var i = 0;
    //console.log(this.playerListController);
    while (i >= this.playerListController.head && i <= this.playerListController.rear) {
        this.printMediaObject(i, 0);
        i++;
    }
    $("li[data-indexnum=" + this.playerListController.active + "]").addClass("active");
    $(".player-list-btn").on('click', function(e) {
        var btnNum = e.currentTarget.dataset.indexnum;
        //console.log(btnNum);
        this.mediaIndex = parseInt(btnNum);
        this.mediaLoader();
    }.bind(this));
}

MediaController.prototype.incMediaIndex = function() {
    if (this.mediaIndex + 1 === this.mediaArray.length) {
        this.mediaIndex = 0;
    } else {
        this.mediaIndex++;
    }
}

MediaController.prototype.decMediaIndex = function() {
    if (this.mediaIndex === 0) {
        this.mediaIndex = this.mediaArray.length - 1;
    } else {
        this.mediaIndex--;
    }
}