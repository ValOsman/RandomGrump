'use strict';
//Sometimes, the Youtube Iframe API doesn't call its own onReady function, so the player never gets loaded. When this happens, this acts as a fallback.
$(document).ready(function(){
    try {
        loadYouTubePlayer();
    } catch (e) {
        console.log("The YouTube Iframe API is really hit or miss.");
    }
});

var mc = new MediaController();
var player;

function aspectRatio(newWidth) {
    var width = 16;
    var height = 9;
    
    var newHeight = Math.ceil((height/width) * newWidth);    
    
    return {
        'width': newWidth,
        'height': newHeight
    }
}

function onYouTubeIframeAPIReady() {
    loadYouTubePlayer();
}

function onPlayerReady(event) {
    shuffleOne("video").done(function(result) {
        mc.player.cueVideoById(result);
    });
}

function loadYouTubePlayer() {
    player = new YT.Player('player', {
        height: 0,
        width: 0,
        events: {
            'onReady': onPlayerReady
        }
    });
    mc.player = player;
    resizePlayer();    
}


function shuffleOne(tableName) {
    return $.ajax({
        url: 'assets/php/ajax.php',
        type: 'post',
        data: {
            'getOne': 'true',
            'table': tableName
        }
    });
}

function returnQuery(query) {
    return $.ajax({
        url: 'assets/php/ajax.php',
        type: 'POST',
        data: {
            'getList': 'true',
            'table': query["tableName"],
            'published': query['published'],
            'oneOffs': query['oneOffs'],
            'show': query["show"]
        }
    });
}

function resizePlayer() {
    var container = $('.outer-container');
    var containerPadding = container.outerWidth() - container.width();
    var containerWidth = container.outerWidth() - containerPadding;
    var size = aspectRatio(containerWidth);
    //console.log([container.outerWidth(), containerPadding, containerWidth]);
    mc.player.setSize(size.width, size.height);
}

//http://stackoverflow.com/a/12646864
function shuffle(array) {
    for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }
    return array;
}

//http://stackoverflow.com/a/8175221
function sortByKey(array, key, order) {
    return array.sort(function(a, b) {
        var x = a[key]; var y = b[key];
        if (order == 'ASC') {
            return ((x < y) ? -1 : ((x > y) ? 1 : 0));
        } else {
            return ((x > y) ? -1 : ((x < y) ? 1 : 0));
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
    e.preventDefault();
    //console.log("////////////////SUBMIT////////////////")
    //console.log($(this).elements);
    var formArray = $(this).serializeArray();
    //console.log(formArray);
    var query = {
        tableName: formArray[0]['value'],
        published: formArray[1]['value'],
    };
    if (formArray.length < 4) {
        var oneOff = {name: "one-off", value: ''};
        formArray.splice(2, 0, oneOff);
    }
    var showIndex = 3;
    if (formArray[2]['name'] == "one-off") {
       query["oneOffs"]  = formArray[2]['value'];
        showIndex = 3;
    }
    var showArray = []
    for (var i = showIndex; i < formArray.length; i++) {
        if (formArray[i]['name'] === 'show')
            showArray.push(formArray[i]['value']);
    }
    query["show"] = JSON.stringify(showArray);
    //console.log(query);
    returnQuery(query).done(function(result) {
        //console.log(result);
        var resultArray = JSON.parse(result);
        if (resultArray.length > 0) {
            $("#playerButtons").removeClass("buttonContainer").addClass("hide");
            $("#playerListContainer").removeClass("hide");
            mc.mediaIndex = 0;
            mc.mediaArray = resultArray;
            //console.log(mc.mediaArray);
            mc.printMediaList();
            mc.mediaLoader();
        } else {
            alert("Sorry, your query didn't return any results.");
        }
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

$("input[name=table]").click(function(e) {
    if (this.value == "playlist") {
      $("fieldset[name=one-offs]").prop("disabled", true);
    } else {
        $("fieldset[name=one-offs]").prop("disabled", false);
    }    
});

$("#shuffle").click(function(e) {
    //console.log("Shuffle");
    shuffle(mc.mediaArray) ;
    mc.mediaIndex = 0;
    //console.log(mc.mediaArray);
    mc.printMediaList();
    mc.mediaLoader();
});

$("#sort-asc").click(function(e) {
    //console.log("Sort asc");
    sortByKey(mc.mediaArray, 'object_published_date', 'ASC');
    mc.mediaIndex = 0;
    //console.log(mc.mediaArray);
    mc.printMediaList();
    mc.mediaLoader();
});

$("#sort-desc").click(function(e) {
    //console.log("Sort desc");
    sortByKey(mc.mediaArray, 'object_published_date', 'DESC');
    mc.mediaIndex = 0;
    //console.log(mc.mediaArray);
    mc.printMediaList();
    mc.mediaLoader();;
});

$(window).resize(function(e) {
    resizePlayer();
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

