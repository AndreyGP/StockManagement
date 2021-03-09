$(document).ready(function () {
    var $count             = Cookies.get('count_all'),
        $user_id_cookie    = Cookies.get('user_id')
        /*$my_count    = Cookies.get('my_count')*/;

    $('body').click(function (event) {

        var $push          = $(event.target),
            $id            = $push['context']['id'],
            $context       = $push['context']['title'],
            $cell          = $("div[id='" + $id + "']>div[class='panel-body']>div[class='row-cells']>h2");

        //Обработка нажатия на кнопку "ок"
        if($id != false && $context != false && $context === 'ok'){

            var $div       = $("div[id='" + $id + "']"),
                $data      ={
                    'id' : $id
                };

            $.ajax({
                url  : "/cells/cell-insert",
                type : "POST",
                data : $data,
                success : funcCellFinish
            });

            function funcCellFinish(data)
            {
                if (data != false){
                    $cell.css({'color' : 'red', 'text-decoration' : 'line-through', 'text-decoration-color' : 'red'});
                    $div.delay(300).slideUp(600, function () {
                        this.remove();
                    });

                    $count  = $count-1;
                    Cookies.set('count_all', $count);

                    if (!$count){
                        Cookies.remove('count_all');
                        Cookies.remove('my_count');
                        Cookies.remove('checked');

                        $("#checkbox").slideUp(300, function () {

                            $("div[id='overlay']").fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                                function(){ // пoсле выпoлнения предъидущей aнимaции
                                    $("div[id='modal_form']")
                                        .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                                        .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                                }
                            );

                        });
                    }
                }
            }
        }

        //Обработка нажатия на кнопку "not"
        if($id != false && $context != false && $context === 'not'){

            $cell
                .css({
                    'color' : 'red',
                    'text-decoration' : 'line-through',
                    'text-decoration-color' : 'red'
                })
                .delay(300)
                .slideUp(600, function () {

                    var $cell_search = $("div[id='" + $id + "']>div[class='panel-body']>div[class='row-cells']>h2").text(),
                        $letter = $cell_search.substr(0,1),
                        $rack = $cell_search.substr(1,2),
                        $row = $cell_search.substr(4,1),
                        $col = $cell_search.substr(6,1);

                    var $data = {
                        'letter' : $letter,
                        'rack'   : $rack,
                        'row'    : $row,
                        'col'    : $col
                    };

                    $.ajax({
                        url     : "/cells/free-cell",
                        type    : "POST",
                        data    : $data,
                        success : funcSuccessCell
                    });

                    function funcSuccessCell(data)
                    {
                        if(data !== 'false') {

                            setTimeout(nextAction, 0);

                            function nextAction() {

                                var $new_free_cell = JSON.parse(data),
                                    $cell_full = $new_free_cell['name']
                                        + '-'
                                        + $new_free_cell['row']
                                        + '-'
                                        + $new_free_cell['col']
                                        + $new_free_cell['cell'];

                                var $data_update = {
                                    'id_cell'   : $new_free_cell['id'],
                                    'table'     : $new_free_cell['name'],
                                    'cell_full' : $cell_full,
                                    'id_buffer' : $id
                                };

                                $.ajax({
                                    url : "/cells/cell-buffer-update",
                                    type : "POST",
                                    data : $data_update,
                                    success : funcBufferUpdate
                                });

                                function funcBufferUpdate(data) {
                                    if(data == true) {
                                        $cell
                                            .empty()
                                            .text($cell_full)
                                            .css({
                                                'color'                 : 'green',
                                                'text-decoration'       : 'underline',
                                                'text-decoration-color' : 'green'
                                            })
                                            .slideDown(600);
                                    }
                                }
                            }
                        }
                    }
                });
        }
    });

    //Создание сокет соединений и их обработка
    var $options = {
        dev_id  : 1876,
        user_id : Cookies.get('user_id'),
        user_key : Cookies.get('session'),
        node     : 'app.comet-server.ru'
    };

    cometApi.start($options, function () {
        //cometApi.isMaster();
    });

    cometApi.subscription("cells_buffer.insert_new", function(data){

        var $info_string = data.data,
            $info_array  = $info_string.split(', '),
            $insert_id   = $info_array['0'],
            $part_num    = $info_array['1'],
            $cell_full   = $info_array['2'],
            $user_s_n    = $info_array['3'];

        $count           = parseInt($count, 10);
        $count           = $count+1;
        Cookies.set('count_all', $count);

        var new_div =
            "        <div class=\"panel panel-primary new_cell\" id=\"" + $insert_id + "\">\n" +
            "\n" +
            "        </div>\n",
            new_html =
                "            <div class=\"panel-heading\">\n" +
                "                <h3 class=\"panel-title\">" + $part_num + "</h3>\n" +
                "            </div>\n" +
                "\n" +
                "            <div class=\"panel-body\">\n" +
                "                <div class=\"row-cells\">\n" +
                "                    <h2>" + $cell_full + "</h2>\n" +
                "                </div>\n" +
                "                <div class=\"row-button\">\n" +
                "                    <button type=\"button\" class=\"btn btn-success glyphicon glyphicon-ok button-success-ok\"" +
                " id=\"" + $insert_id + "\" title=\"ok\" aria-label=\"Right Aline\">\n" +
                "                    </button>\n" +
                "                    <button type=\"button\" class=\"btn btn-danger glyphicon glyphicon-remove button-success-not\"" +
                " id=\"" + $insert_id + "\" title=\"not\" aria-label=\"Right Aline\">\n" +
                "                    </button>\n" +
                "                </div>\n" +
                "            </div>\n" +
                "            <div class=\"clear\"></div>\n" +
                "\n" +
                "            <div class=\"panel-heading user_name\"><p>" + $user_s_n + "</p></div>\n";

        $("div[id='screen-block']").append(new_div);
        $("div[id='" + $insert_id + "']").slideUp().append(new_html).delay(100).slideDown(300);

    });

    cometApi.subscription("cells_buffer.delete_div_cell", function(data){

        var $info       = data.data,
            $array_info = $info.split(", "),
            $div_id     = $array_info[0],
            $user_id    = $array_info[1],
            $div        = $("div[id='" + $div_id + "']"),
            $cell       = $("div[id='" + $div_id + "']>div[class='panel-body']>div[class='row-cells']>h2"),
            $button_div = $("div[id='" + $div_id + "']>div[class='panel-body']>div[class='row-button']>button");

        if ($user_id != $user_id_cookie){

            $button_div.prop('disabled', true);

            $cell.css({'color' : 'red', 'text-decoration' : 'line-through', 'text-decoration-color' : 'red'});

            $div.delay(300).slideUp(600, function () {
                this.remove();
            });

            $count      = $count-1;
            Cookies.set('count_all', $count);

            if (!$count){
                Cookies.remove('count_all');
                Cookies.remove('my_count');
                Cookies.remove('checked');

                $("#checkbox").slideUp(300, function () {

                    $("div[id='overlay']").fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                        function(){ // пoсле выпoлнения предъидущей aнимaции
                            $("div[id='modal_form']")
                                .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                                .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                        }
                    );

                });
            }
        }

    });

    cometApi.subscription("cells_buffer.cell_update", function(data){

        var $info             = data.data,
            $array_info       = $info.split(", "),
            $div_id           = $array_info[0],
            $cell_full        = $array_info[1],
            $user             = $array_info[2],
            $context          = $array_info[3],
            $cell_div         = $("div[id='" + $div_id + "']>div[class='panel-body']>div[class='row-cells']>h2"),
            $button_div       = $("div[id='" + $div_id + "']>div[class='panel-body']>div[class='row-button']>button");

        if ($user != $user_id_cookie){

            $button_div.prop('disabled', true);

            $cell_div
                .css({
                    'color' : 'red',
                    'text-decoration' : 'line-through',
                    'text-decoration-color' : 'red'
                })
                .slideUp(200, function () {
                    $cell_div.empty()
                        .text($cell_full)
                        .css({
                            'color'                 : 'green',
                            'text-decoration'       : 'underline',
                            'text-decoration-color' : 'green'
                        })
                        .slideDown(200)
                        .delay(300);

                    $button_div.prop('disabled', false);
                });

        }

    });

});