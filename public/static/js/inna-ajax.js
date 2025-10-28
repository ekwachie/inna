
$("button").on("click", function(){
$.ajax({
    method: "POST",
    url: "/ajax/ff",
    data: { username: "John", password: "Boston" }
  })
    .done(function( data ) {
        // decode json data
        var json = JSON.parse(data);
        swal(json.msg, {
            icon: json.type,
        });
    });
});