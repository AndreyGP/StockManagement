$(document).ready(function () {

//Заранее выбранные html-объекты:
    var $letter         = $("select[id='letter']"),     //Инпут с буквами стеллажа
        $rack           = $("select[id='rack']"),       //Инпут с цифрами стеллажа
        $row            = $("select[id='row']"),        //Инпут с цифрами ряда стеллажа
        $col            = $("select[id='col']"),        //Инпут с цифрами полки ряда стеллажа
        $p_num          = $("input[id='p_num']"),       //Инпут с полем ввода кат№
        $div_cell       = $("div[id='cell']"),          //Блок для отображения свободной ячейки
        $push_cell      = $("#push_cell"),              //Кнопка "Занять ячейку"
        $div_push_cell  = $("div[class='push_cell']"),  //Блок с кнопкой $push_cell
        $div_c_up       = $("div[class='c_up']"),       //Блок с кнопкой "Отразить в 1С"
        $c_up           = $("a[id='c_up']"),            //Кнопка-ссылка "Отразить в 1С"
        $c_up_show_hide = $(".c_up_hide");

//Состояние на случай F5
    $rack.empty() .prop('disabled', true);
    $row.empty()  .prop('disabled', true);
    $col.empty()  .prop('disabled', true);
    $letter       .prop('disabled', true)
                  .find(':first-child')
                  .prop('selected', true);
    $p_num        .val('')
                  .prop('disabled', false).focus();


//Обработчики событий по отобранным селекторам:

    //Обработка события ввода текста в поле инпута каталожного №
    $p_num.bind("keyup", function () {

        $letter.prop('disabled', true)/*.find(':first-child').prop('selected', true)*/;
        $rack  .prop('disabled', true);
        $row   .prop('disabled', true);
        $col   .prop('disabled', true);

        var $text   = $p_num.val();
        $text       = $text.trim();
        $text       = $text.replace(/[\s\W]/ig, '');

        $p_num.empty().val($text);

        $text       = $p_num.val();
        var $length = $text.length;

        if($length === 0){
            $p_num.css({'text-transform' : 'none'}).prop('placeholder', 'Введите каталожный №');
            $letter.prop('disabled', false)/*.find(':first-child').prop('selected', true)*/;
            $rack.prop('disabled', false);
            $row.prop('disabled', false);
            $col.prop('disabled', false);

            $push_cell.prop('disabled', true).slideUp(600);
        }

        $p_num.css({'text-transform' : 'uppercase'});

        if($length >= 9){
            if($letter.val() === '0'){
                $letter.prop('disabled', false);
            }
            $push_cell.prop('disabled', false).slideDown(600);
            var $data = {
                'p_num' : $text
            };

            $.ajax({
                url : "/cells/number-search",
                type : "POST",
                data : $data,
                success : funcSuccessNum
            });
        } else {
            $push_cell.prop('disabled', true).slideUp(600);
        }

        function funcSuccessNum(data)
        {
            if(data !== 'null'){//alert(data);
                data = JSON.parse(data);
                $("div[id='overlay']").fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                    function()
                    { // пoсле выпoлнения предъидущей aнимaции
                        $("p[class='after']")
                        .after("<p class=\"h1 cell modal_cell\" >" + data['cells'] + "</p>");
                        $("div[id='modal_form']")
                        .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                        .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                    }
                );
            }
        }

    });//end $p_num.bind

    //Обработка события нажатия на кнопку, крестик или фон-подложка для модального окна сообщения места хранения детали
    $('#modal_close, #overlay, #modal_form>button').bind("click", function(){ // лoвим клик пo крестику или пoдлoжке
        $('#modal_form')
        .animate({opacity: 0, top: '45%'}, 200,  // плaвнo меняем прoзрaчнoсть нa 0 и oднoвременнo двигaем oкнo вверх
            function(){ // пoсле aнимaции
                $(this).css('display', 'none'); // делaем ему display: none;
                $('#overlay').fadeOut(400); // скрывaем пoдлoжку
                $("p[class='after']").next().empty();
            }
        );
        $("input[id='p_num']").val('');
        $("p[class='after']").next().empty();
        $letter.prop('disabled', true);
    });//end modal.click

    //Обработка события изменения буквы стеллажа
    $letter.bind("change", function () {

        $p_num.prop('disabled', true);
        $row.empty().prop('disabled', true);
        $col.empty().prop('disabled', true);
        $div_cell
        .empty()
        .css({
            'display' : 'none'
        });
        $push_cell.prop('disabled', true).slideUp(600);

        if ($letter.val() !== '0'){

            var $data = {
                value : $letter.val(),
                type : 'rack'
            };

            $.ajax({
                url : "/cells/select",
                type : "POST",
                data : $data,
                success : funcSuccessLett
            });
        } else {
            $p_num.prop('disabled', false);
            $rack.empty().prop('disabled', true);
            $row.empty().prop('disabled', true);
            $col.empty().prop('disabled', true);
        }

        function funcSuccessLett(data)
        {
            data = JSON.parse(data);
            $rack.empty();
            $rack.append($("<option value='0'>--</option>\n\r"));
            for(var $key in data){
                $rack.append($("<option value='" + data[$key]['id'] + "'>" + data[$key]['number'] + "</option>\n\r"));
            }
            $rack.prop('disabled', false);
        }
    });//end $letter.bind

    //Обработка события изменения номера стеллажа
    $rack.bind("change", function () {

        $div_cell
        .empty()
        .css({
            'display' : 'none'
        });
        $push_cell.prop('disabled', true).slideUp(600);
        $p_num.prop('disabled', true);

        if ($rack.val() !== '0'){

            var $data = {
                value : $rack.val(),
                type : 'row',
                table : $letter.val()
            };

            $.ajax({
                url : "/cells/select",
                type : "POST",
                data : $data,
                success : funcSuccessRack
            });
        } else {
            $row.empty().prop('disabled', true);
            $col.empty().prop('disabled', true);
        }

        function funcSuccessRack(data)
        {
            data = JSON.parse(data);
            //alert('Ответ сервера:' + data);
            $row.empty().append($("<option value='0'>-</option>\n\r"));
            $col.empty().append($("<option value='0'></option>\n\r")).prop('disabled', true);
            for(var $r = 1; $r <= data['rows']; $r++){
                $row.append($("<option value='" + $r + "'>" + $r + "</option>\n\r"));
            }
            for(var $c = 1; $c <= data['cols']; $c++){
                $col.append($("<option value='" + $c + "'>" + $c + "</option>\n\r"));
            }
            $row.prop('disabled', false);
        }
    });//end $rack.bind

    //Обработка события изменения выбранного ряда стеллажа
    $row.bind("change", function () {

        $div_cell
        .empty()
        .css({
            'display' : 'none'
        });
        $push_cell.prop('disabled', true).slideUp(600);
        $p_num.prop('disabled', true);

        $col.prop('disabled', true).find(':first-child').prop('selected', true);
        if ($row.val() !== '0'){
            $col.prop('disabled', false).find(':first-child').text('-');
        } else {
            $col.prop('disabled', true).find(':first-child').text('');
        }
    });//end $row.bind

    //Обработка события изменения выбранной полки на ряде стеллажа
    $col.bind("change", function () {

        if ($row.val() !== '0'){

            var $data = {
                'letter' : $letter.val(),
                'rack' : $rack.val(),
                'row' : $row.val(),
                'col' : $col.val()
            };

            $.ajax({
                url : "/cells/free-cell",
                type : "POST",
                data : $data,
                success : funcSuccessCell
            });

            function funcSuccessCell(data) {
                //alert(data);
                if(data !== 'false'){
                    data = JSON.parse(data);
                    $div_cell
                    .empty()
                    .append($("<p class='h1 cell'>" + data['name'] + '-' + data['row'] + '-' + data['col'] + data['cell'] + "</p>"))
                    .append($("<input type='text' id='cell_id' value='" + data['id'] + "' style='display: none'>"))
                    .css({
                        'display' : 'block',
                        'text-decoration' : 'underline',
                        'text-decoration-color' : 'green'
                    });

                    if($p_num.val() !== ''){
                        $push_cell.prop('disabled', false).slideDown(600);
                    }else{
                        $p_num.prop('disabled', false);
                    }//endif

                    $div_push_cell.css({'display' : 'block'});
                }else{
                    $div_cell
                    .empty()
                    .append($("<h1 class='not_free'>Not free</h1>"))
                    .css({
                        'display' : 'block',
                        'text-decoration' : 'underline',
                        'text-decoration-color' : 'red'
                    })
                    .find(':first-child')
                    .css({
                        'color' : 'red'
                    });
                    $push_cell.prop('disabled', true).slideUp(600);
                }//endif
            }
        } else {
            $push_cell.prop('disabled', true).slideUp(600);
        }//endif
    });//end $col.bind

    //Обработка события нажатия кнопки "Занять ячейку"
    $push_cell.bind("click", function () {

        var $part_num = $p_num.val(),
            $cell_free = $("p[class='h1 cell']"),
            $cell_full = $cell_free.text(),
            $id = $("input[id='cell_id']").val();

        var $data = {
            id : $id,
            table : $cell_full.substr(0,3),
            cell_full : $cell_full,
            part_num : $part_num
        };
        $.ajax({
            url : "/cells/cell-put",
            type : "POST",
            data : $data,
            success : funcSuccessPut
        });

        function funcSuccessPut(data) {
            //alert(data);

            $push_cell.prop('disabled', true)
                .slideUp(600);
            $("div[class='button-group']>hr").css({"display" : "block"});
            $("div[class='button-group']>p").css({"display" : "block"});
            $div_c_up.css({"display" : "block"});
            $c_up.prop('disabled', false);
            $c_up_show_hide.removeClass('c_up_hide').addClass('c_up_show').css({"display" : "block"});

            $div_cell
            .css({
                'text-decoration' : 'none'
            })
            .find(':first-child')
            .css({
                'text-decoration' : 'line-through',
                'text-decoration-color' : 'red',
                'color' : 'red'
            });

            $cell_free
            .slideUp(600, function () {

                $div_cell
                .css({
                    'text-decoration' : 'underline',
                    'text-decoration-color' : 'green'
                })
                .find(':first-child')
                .css({
                    'text-decoration' : 'none',
                    'color' : 'green'
                });

                if(data === false){

                    $cell_free
                    .text($cell_full)
                    .slideDown(600, function () {
                        $p_num.prop('disabled', true);
                        $letter.prop('disabled', false);
                        $rack.prop('disabled', false);
                        $row.prop('disabled', false);
                        $col.prop('disabled', false);
                    });
                    alert('Повторите снова!');

                    $push_cell.prop('disabled', false)
                    .slideDown(600);

                }else{

                    var $data = {
                        'letter' : $letter.val(),
                        'rack' : $rack.val(),
                        'row' : $row.val(),
                        'col' : $col.val()
                    };

                    $.ajax({
                        url : "/cells/free-cell",
                        type : "POST",
                        data : $data,
                        success : funcSuccessCell
                    });

                    function funcSuccessCell(data) {
                        //alert(data);
                        if(data !== 'false'){

                            $("li[id='1_C_li']").prop('disabled', false);
                            $("a[id='1_C_a']").attr('href', "/cells/buffer");

                            data = JSON.parse(data);
                            $div_cell
                            .empty()
                            .append($("<p class='h1 cell'>" + data['name'] + '-' + data['row'] + '-' + data['col'] + data['cell'] + "</p>"))
                            .append($("<input type='text' id='cell_id' value='" + data['id'] + "' style='display: none'>"))
                            .css({
                                'display' : 'block',
                                'text-decoration' : 'underline',
                                'text-decoration-color' : 'green'
                            });

                            $cell_free
                            .slideDown(600, function () {
                                $p_num.val('').prop('disabled', false).focus();
                                $letter.prop('disabled', false);
                                $rack.prop('disabled', false);
                                $row.prop('disabled', false);
                                $col.prop('disabled', false);
                            });

                            $div_push_cell.css({'display' : 'block'});

                            //$push_cell.prop('disabled', false).slideDown(600);

                        }else{

                            $div_cell
                            .empty()
                            .append($("<h1 class='not_free'>Not free</h1>"))
                            .css({
                                'display' : 'block',
                                'text-decoration' : 'underline',
                                'text-decoration-color' : 'red'
                            })
                            .find(':first-child')
                            .css({
                                'color' : 'red'
                            });

                            $cell_free
                            .slideDown(600, function () {
                                $p_num.val('').prop('disabled', false).focus();
                                $letter.prop('disabled', false);
                                $rack.prop('disabled', false);
                                $row.prop('disabled', false);
                                $col.prop('disabled', false);
                            });

                            $push_cell.prop('disabled', true).slideUp(600);

                        }//endif
                    }//end funcSuccessCell
                }//endif
            });//end $cell_free
        }//end funcSuccessPut
    });//end $push_cell.bind

});//end script