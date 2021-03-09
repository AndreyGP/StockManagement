$(document).ready(function () {

    var $name       = $("input[name='login']"),     //Поле ввода Логина
        $password   = $("input[name='password']"),  //Поле ввода пароля
        $submit     = $("#sub_button"),             //Кнопка "Войти"
        $name_label = $("#sizing-addon1"),           //Лэйбл к полю ввода логина
        $pass_label = $("#sizing-addon2");           //Лэйбл к полю ввода пароля

    $name           .val('')
                    .prop('disabled', false);
    $password       .val('')
                    .prop('disabled', false);

    //Обработка события ввода текста в поле login
    $name.bind('keyup', function () {

        var $login  = $name.val();

        $login      = $login.trim();
        $login      = $login.replace(/[\s\d\W]/ig, '').toLowerCase();

        $name        .empty().val($login);

        var $length = $login.length;

        if ($length > 0){

            $name_label.removeClass('label-default').addClass   ('label-warning');

            if ($length > 5 && /^[a-z]{3,}_[a-z]{3,}$/i.test($login)){

                var $data   = {
                    'login' : $login
                };

                jQuery.ajax({
                    url     : "/login",
                    type    : "POST",
                    data    : $data,
                    success : funcSuccess
                });

                function funcSuccess(data) {
                    if (data === 'SessionError'){
                        $("div[id='overlay_login']").fadeIn(400,         // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                            function() {                                            // пoсле выпoлнения предъидущей aнимaции
                                $("div[id='modal_form_login']")
                                .css('display', 'block')                 // убирaем у мoдaльнoгo oкнa display: none;
                                .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                            }
                        );
                    }

                    if (data === '1'){
                        //alert(data);
                        $name      .prop('disabled', true);
                        $name_label.removeClass('label-warning')
                                   .addClass   ('label-success');
                        $password  .focus();
                    }
                }
            }

        } else {
            $name_label.removeClass('label-warning').addClass   ('label-default');
        }

    });

    //Обработка события ввода текста в поле password
    $password.bind('keyup', function () {

        var $pass   = $password.val();

        $pass       = $pass.trim();
        $pass       = $pass.replace(/[\s\W]/ig, '');

        $password   .empty().val($pass);

        var $length = $pass.length;

        if ($length){

            $pass_label.removeClass('label-default')
                       .addClass   ('label-warning');

        } else {

            $pass_label.removeClass('label-warning')
                       .addClass   ('label-default');
        }

        if ($length >= 8){

            var $data      = {
                'password' : $pass,
                'user_id'  : Cookies.get('user_id')
            };

            $.ajax({
                url     : "/login",
                type    : "POST",
                data    : $data,
                success : funcSuccess
            });
        }

        function funcSuccess(data) {
            if (data === '1'){
                $pass_label.removeClass('label-warning')
                           .addClass   ('label-success');
                $password.prop('disabled', true);

                setTimeout(function () {
                    window.location.href='http://irbis.loc';
                }, 300);
            }
        }

    });

    //Обработка события нажатие на кнопку submit
    $submit.bind('click', function () {

    });

    //Обработка события нажатия на кнопку, крестик или фон-подложка для модального окна сообщения места хранения детали
    $('#modal_close_login, #overlay_login, #modal_form_login>button').bind("click", function(){ // лoвим клик пo крестику или пoдлoжке
        $('#modal_form_login')
        .animate({opacity: 0, top: '45%'}, 200,  // плaвнo меняем прoзрaчнoсть нa 0 и oднoвременнo двигaем oкнo вверх
            function(){ // пoсле aнимaции
                $(this).css('display', 'none'); // делaем ему display: none;
                $('#overlay_login').fadeOut(400); // скрывaем пoдлoжку
            }
        );
    });//end modal.click

});
//end script