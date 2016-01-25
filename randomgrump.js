'use strict';

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
    console.log(mc.player);
}

//function onPlayerStateChange(event) {
//    console.log(mc.player.getPlayerState());
//}

function shuffleOne(tableName) {
    return $.ajax({
        url: 'ajax.php',
        type: 'post',
        data: {
            'getOne': 'true',
            'table': tableName
        }
    });
}

function returnQuery(query) {
    return $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {
            'getList': 'true',
            'table': query["tableName"],
            'published': query['published'],
            'show': query["show"]
        }
    });
}

//BUTTON BINDINGS
///////////////////////////////////////////////////
$('#videoBtn').on('click', function(e) {
    shuffleOne("video").done(function(result) {
        player.loadVideoById(result);
    });
});

$('#playlistBtn').on('click', function(e) {
    shuffleOne("playlist").done(function(result) {
        player.loadPlaylist({
            list: result,
            listType: "playlist"
        });
    });
});

$('#playerForm').on('submit', function(e) {
    $("#playerButtons").removeClass("buttonContainer").addClass("hide");
    e.preventDefault();
    console.log("////////////////SUBMIT////////////////")
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
    returnQuery(query).done(function(result) {
        var resultString = result.split("xxx");
        mc.mediaIndex = 0;
        mc.mediaArray = JSON.parse(resultString[resultString.length - 1]);
        mc.printMediaList();
        mc.mediaLoader();
    });
});

$('#nextVideoBtn').on('click', function(e) {
    mc.incMediaIndex();
    mc.mediaLoader();
});

$('#prevVideoBtn').on('click', function(e) {
    mc.decMediaIndex();
    mc.mediaLoader();
});

$('#nextPlaylistBtn').on('click', function(e) {
    mc.incMediaIndex();
    mc.mediaLoader();
});

$('#prevPlaylistBtn').on('click', function(e) {
    mc.decMediaIndex();
    mc.mediaLoader();
});

$('#makeBig').on('click', function(e) {
    mc.player.setSize(1066, 480);
});

$('#makeSmall').on('click', function(e) {
    mc.player.setSize(640, 390);
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
/*(function () {
     var ael = Node.prototype.addEventListener,
         rel = Node.prototype.removeEventListener;
     Node.prototype.addEventListener = function (a, b, c) {
         console.log('Listener', 'added', this, a, b, c);
         ael.apply(this, arguments);
     };
     Node.prototype.removeEventListener = function (a, b, c) {
         console.log('Listener', 'removed', this, a, b, c);
         rel.apply(this, arguments);
     };
 }(
)
);*/