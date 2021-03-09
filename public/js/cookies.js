window.onload = function()
{
    var $no_me = $(".no_me"),
        $checkbox = $("#all");

    if (Cookies.get('checked')){
        $no_me.css({'display' : 'block'});
        $checkbox.prop('checked', true);
    } else {
        $checkbox.prop('checked', false);
    }

    if (Cookies.get('count_all') != 0){
        $("#checkbox").css({'display' : 'block'});
    }
};

$(document).ready(function () {
    $("#all").change(function () {
        if ($(this).is(":checked")){
            Cookies.set('checked', true);
            $(".no_me").slideDown(400);
        }else{
            Cookies.remove('checked');
            $(".no_me").slideUp(400);
        }
    });
});