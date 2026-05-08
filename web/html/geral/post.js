function post(url, args = null, callBack = function(){return true}){
    $.ajax({
        type: "POST",
        url: url,
        data: args,
        success: function(response) {
            callBack(response);
        },
        dataType: 'json'
    })
}

function getJson(url, callBack){
    $.ajax({
        data: '',
        type: "POST",
        url: url,
        async: true,
        success: function(json) {
          callBack(json);
        },
        dataType: 'json'
      });
}