function addThousandSeparator(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ' ' + '$2');
    }
    return x1 + x2;
}

$(window).load(function(){

    function popupOpen() {
        var popup_html = '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Пожалуйста, подождите...</span>';

        if ($('.popup_wrapper').length == 0)
            $('body').prepend('<div class="popup_wrapper">'+popup_html+'</div>');
        else
            $('.popup_wrapper').html(popup_html);

        $('.popup_wrapper:hidden').fadeIn();
    }

    $('html').on('click', '.popup_wrapper, .popup_wrapper .close', function(e){
        $('.popup_wrapper').fadeOut(400,function(){$(this).remove()});
        e.preventDefault();
    });

    $('html').bind('keyup', function(e){
        if (e.keyCode == 27 && $('.popup_wrapper:visible').length) {
            $('.popup_wrapper').fadeOut(400,function(){$(this).remove()});
        }
    });

    $('html').on('click', '.popup_wrapper .popup', function(e){ e.stopPropagation(); });

    function togglePassword(obj){
        if ( obj.parent().find('input[type="password"]').length > 0 ) {
            obj.parent().find('input[type="password"]').attr('type', 'text');
        } else {
            obj.parent().find('input[type="text"]').attr('type', 'password');
        }
        obj.toggleClass('fa-eye-slash');
    }

    $('.fa-eye').hover(
        function() {
            togglePassword($(this));
            // $(this).parent().find('input[type="password"]').attr('type', 'text');
            // $(this).toggleClass('fa-eye-slash');
        },
        function() {
            togglePassword($(this));
            // $(this).parent().find('input[type="password"]').attr('type', 'text');
            // $(this).toggleClass('fa-eye-slash');
        }
    );
    $('.fa-eye').click(function(e){
        e.preventDefault();
        togglePassword($(this));
    });

    //-- main page (order form)

    $('.form_btn.green.ok.edit').click(function(e){
        e.preventDefault();

        var data = $(this).parentsUntil('.content_block').last().serialize();

        $.ajax({
            url: "../js/ajax.edit_items.php",
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function () {
                popupOpen();
            },
            success: function (response) {
                console.log(response);
                if ($('.popup_wrapper:visible').length == 1) {
                    if (typeof response.errors !== 'undefined' && response.errors.length > 0) {
                        popup_html =
                            '<div class="popup errors">' +
                            '<b>При сохранении данных возникли ошибки:</b><br>' +
                            response.errors + '<br>' +
                            '</div>';
                    } else {
                        popup_html =
                            '<div class="popup">' +
                            '<b>Информация об оборудовании обновлена!</b><br>' +
                            '</div>';
                    }

                    $('.popup_wrapper').html(popup_html);
                }
            }
        });
    });
    
    $('.item_text').css('minHeight', function(){
        var max_height = 0;
        $('.item_text').each(function(){
            max_height = Math.max(max_height, parseInt($(this).height()));
        });
        return max_height;
    });

    function get_summary() {
        var summary_price = 0, summary_reward = 0;
        $('#step2 .reward:visible').each(function(){
            var item_type = $(this).parent().parent().attr('item-type'),
                cur_price = parseInt($(this).find('input.item_price_result').val()),
                cur_reward = parseInt($(this).find('input.user_reward').val()),
                cars_count = parseInt($('#step3 .item_type_'+item_type+'_child input').val()),
                cur_sum_price, cur_sum_reward,
                order_table = $('.order .item_type_'+item_type+'_child');

            if ( isNaN(cars_count) ) cars_count = 1;

            cur_sum_price = cur_price * cars_count;
            cur_sum_reward = cur_reward * cars_count;

            order_table.find('.item_price_result').text(addThousandSeparator(cur_price));
            order_table.find('.user_reward').text(addThousandSeparator(cur_reward));
            order_table.find('.cars_count').text(cars_count);
            order_table.find('.summary_price').text(addThousandSeparator(cur_sum_price));
            order_table.find('.summary_reward').text(addThousandSeparator(cur_sum_reward));

            summary_price += cur_sum_price;
            summary_reward += cur_sum_reward;
        });

        $('#step3 input.summary_price_total').val(summary_price);
        $('#step3 input.summary_reward_total').val(summary_reward);
        $('#step3 span.summary_price_total').text(addThousandSeparator(summary_price));
        $('#step3 span.summary_reward_total').text(addThousandSeparator(summary_reward));
    }

    $('.btn_chkbx').each(function(){
        var this_name = $(this).find('input').attr('name');
        if ($(this).hasClass('checked')) {
            $('html .'+this_name+'_child').show();
        } else {
            $('html .'+this_name+'_child').hide();
        }
        get_summary();
    });

    $('.btn_chkbx').click(function(){
        var this_name = $(this).find('input[type="hidden"]').attr('name');
        if ($(this).hasClass('checked')) {
            $(this).find('input[type="hidden"]').val(0);
        } else {
            $(this).find('input[type="hidden"]').val(1);
        }
        $(this).toggleClass('checked');
        $('html .'+this_name+'_child').toggle();
        get_summary();
        $(this).parentsUntil('form').last().find('.form_btn:hidden').fadeIn();
    });

    $('#step1 .btn_chkbx').click(function(e){
        var cur_input_name = $(this).find('input[type="hidden"]').attr('name'),
            alt_btn = $('#step1 .btn_chkbx input[name!="'+cur_input_name+'"]').parent(),
            alt_name = alt_btn.find('input[type="hidden"]').attr('name');
        if (alt_btn.hasClass('checked')) {
            alt_btn.find('input[type="hidden"]').val(0);
        } else {
            alt_btn.find('input[type="hidden"]').val(1);
        }
        alt_btn.toggleClass('checked');
        $('html .'+alt_name+'_child').toggle();
        $('#step1 .form_element').each(function(){
            var name = $(this).attr('name'),
                value = $(this).val();
            $('#step4 .form_element[name="'+name+'"]').val(value);
        });
        e.stopPropagation();
    });

    $('#step1 .form_element').bind('change keyup', function(e){
        var name = $(this).attr('name'),
            value = $(this).val();
        $('#step4 .form_element[name="'+name+'"]').val(value);
        e.stopPropagation();
    });

    $('#step4 .form_element[name*="name"], #step4 .form_element[name*="type"]').bind('change keyup', function(e){
        var name = $(this).attr('name'),
            value = $(this).val();
        $('#step1 .form_element[name="'+name+'"]').val(value);
        e.stopPropagation();
    });

    $('input.form_element').bind('keyup', function(e){
        if (e.keyCode == 13 ) // enter pressed
            $(this).parentsUntil('.content_block').find('.form_btn.green').click();
    });

    $('.cars_counters .delete').click(function(e){
        var item_type = $(this).parent().index()+1;
        $('#step2 .btn_chkbx input[name="item_type_'+item_type+'"]').parent().click();
        e.preventDefault();
    });

    $('.slider').each(function(){
        var slider_percentage = 0,
            price_result = 0,
            user_reward = 0,
            cur = $(this),
            slider_width = parseInt(cur.parent().parent().parent().parent().width() * 0.24) - 10 - cur.find('.handle').width(),
            price_min = parseInt(cur.parent().find('input.cart_price_min').val()),
            price_max = parseInt(cur.parent().find('input.cart_price_max').val()),
            reward_percent = parseFloat(cur.parent().find('input.reward_percent').val()),
            price_delta = price_max - price_min,
            parent = cur.parentsUntil('.container').last();

        if(reward_percent == 0.1){
            price_result = 8500;
        };


        cur.find('.handle').draggable({
            axis: 'x',
            containment: 'parent',
            drag: function(e,ui){
                parent.find('.form_btn.green').filter(':hidden').fadeIn();
                slider_percentage = ui.position.left * 100 / slider_width;



                //Мониторинг
                if(reward_percent == 0.1){
                    if(price_result <= 8500){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = price_result - 7200;
                    } else if(price_result > 8500){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = ((price_result - 8500) / 2) + 1300;
                    }
                };

                //Контроль топлива
                if(reward_percent == 0.2){
                    if(price_result <= 18500){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = price_result - 15500;
                    } else if(price_result > 18500){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = ((price_result - 18500) / 2) + 3000;
                    }
                };

                //Блокировка двигателя
                if(reward_percent == 0.15){
                    if(price_result <= 1800){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = price_result - 1300;
                    } else if(price_result > 1800){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = ((price_result - 1800) / 2) + 500;
                    }
                };

                //Маяк
                if(reward_percent == 0.12){
                    if(price_result <= 5000){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = price_result - 4000;
                    } else if(price_result > 5000){
                        price_result = ( slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = ((price_result - 5000) / 2) + 1000;
                    }
                };

                //price_result = price_result - price_result % 100;
                //user_reward = (price_result * reward_percent).toFixed();




                cur.find('.wrapper .fill').width(slider_percentage+'%');
                cur.parent().find('input.item_price_result').val(price_result);
                cur.parent().find('span.item_price_result').text(addThousandSeparator(price_result));
                cur.parent().find('input.user_reward').val(user_reward);
                cur.parent().find('span.user_reward').text(addThousandSeparator(user_reward));

                get_summary();
            }
        });
    });

    function edit_cars_fields(item_type, cars_count){
        var selector = '.item_type_' + item_type,
            target = selector + '_child .form_table',
            cur_count = $(target + ' tr').length,
            i;

        if (cur_count < cars_count) {
            var html;
            for ( i = (cur_count+1); i <= cars_count; i++ ) {
                html =
                    '<tr>'+
                        '<td><label for="item_type_'+item_type+'_car'+i+'">'+i+'. Марка авто</label></td>'+
                        '<td><input id="item_type_'+item_type+'_car'+i+'" type="text" class="form_element car_name" value="" name="cars['+item_type+']['+i+'][title]"></td>'+
                        '<td><label for="item_type_'+item_type+'_vin'+i+'">VIN</label></td>'+
                        '<td><input id="item_type_'+item_type+'_vin'+i+'" type="text" class="form_element car_vin" value="" name="cars['+item_type+']['+i+'][vin]"></td>'+
                    '</tr>';
                $(target + ' tbody').append(html);
            }
        } else if (cur_count > cars_count) {
            for ( i = cur_count; i > cars_count; i-- ) {
                $(target + ' tr:eq(-1)').remove();
            }
        }
    }

    $('div.cars_count').each(function(){
        var cur_counter = $(this),
            cars_count = parseInt(cur_counter.find('input').val()),
            item_type = $(this).parent().index()+1,
            parent = $(this).parentsUntil('.container').last();

        cur_counter.on('click', 'span', function(){
            if ( $(this).hasClass('increase') ) {
                cars_count++;
            } else {
                cars_count = Math.max(1,cars_count-1);
            }
            cur_counter.find('input').val(cars_count);
            get_summary();
            edit_cars_fields(item_type, cars_count);
            
            parent.find('.form_btn.green, .form_btn.save_tmp').filter(':hidden').fadeIn();
        });

        cur_counter.find('input').bind('change keyup', function(){
            cars_count = Math.max(1, parseInt($(this).val()));
            if ( isNaN(cars_count) ) cars_count = 1;
            $(this).val(cars_count);
            get_summary();
            edit_cars_fields(item_type, cars_count);
        });
    });

    function check_step(step, order_status) {
        var ret = false;

        if (!isNaN(step)) {
            step = parseInt(step);
            var step_el = $('#step'+step),  // объект шага
                target,                     // объект проверки
                condition,                  // условие проверки
                msg = [];                   // результирующее сообщение при неудачке

            step_el.find('.errors').remove(); // если уже была ошибка - убираем

            switch (step) {
                case 1:
                    target = step_el.find('input:visible'); // видимое поле ввода
                    condition = target.val().length; // длинна введенного текста
                    if (condition == 0) msg.push('Вы не заполнили поле - ' + target.attr('placeholder'));
                    break;
                case 2:
                    target = step_el.find('.btn_chkbx.checked'); // выбранные пакеты
                    condition = target.length; // кол-во выбранных пакетов
                    if (condition == 0) msg.push('Вы не выбрали ни один пакет оборудования');
                    break;
                case 3:
                    target = step_el.find('input:visible'); // видимые поля ввода (на этом шаге это могут быть только поля для указания кол-ва авто в пакетах)
                    condition = target.map(function(){ return parseInt($(this).val()); }).get().reduce(function(a,b){return a+b;}, 0); // сумма всех значений видимых полей. Люто? Добро пожаловать в яваскрипт :)
                    if (condition == 0) msg.push('Вы не указали количество оборудования');
                    break;
                case 4:
                    target = step_el.find('.client_info:visible input'); // все поля ввода видимой формы реквизитов
                    condition = target.map(function(){return +($(this).val().length > 0);}).get().join(); // эмм... строка со статусами всех полей: 0 - не заполненно, 1 - заполненно
                    if ( condition.indexOf('0') >= 0 && order_status == 2 ) msg.push('Вы не заполнили все необходимые реквизиты'); // в полученной строке есть 0?

                    target.each(function(){
                        console.log($(this).attr('id'));
                        var field_id = $(this).attr('id'),
                            field_value = $(this).val(),
                            match_pattern = '',
                            matches,
                            error_text = '';

                        if ( field_value.length > 0 ) {
                            switch (field_id) {
                                case 'man_name':
                                    if (field_value.length > 200) error_text = 'ФИО должно быть не более 200 символов';
                                    break;
                                case 'org_name':
                                    if (field_value.length > 200) error_text = 'Название организации должно быть не более 200 символов';
                                    break;
                                case 'man_address':
                                case 'org_address':
                                    if (field_value.length > 100) error_text = 'Адрес должен быть не более 100 символов';
                                    break;
                                case 'man_phone':
                                case 'org_phone':
                                    match_pattern = /^\+7(?:\s|-)?\(?\d{3}\)?(?:\s|-)?\d{3}(?:\s|-)?\d{2}(?:\s|-)?\d{2}$/i;
                                    matches = field_value.match(match_pattern);
                                    if (matches == null) error_text = 'Телефон должен быть указан в формате +7 (ххх) ххх-хх-хх';
                                    break;
                                case 'man_email':
                                case 'org_email':
                                    match_pattern = /^.*@.*\..{2,}$/i;
                                    matches = field_value.match(match_pattern);
                                    if (matches == null) error_text = 'E-mail указан не верно';
                                    break;
                                case 'org_inn':
                                    match_pattern = /^\d{10,12}$/i;
                                    matches = field_value.match(match_pattern);
                                    if (matches == null || (field_value.length != 10 && field_value.length != 12)) error_text = 'ИНН должен состоять из либо 10 (юр.лицо), либо 12 (физ.лицо) цифр';
                                    break;
                                case 'org_bank':
                                    if (field_value.length > 200) error_text = 'Название банка должно быть не более 200 символов';
                                    break;
                                case 'org_kpp':
                                    match_pattern = /^\d{4,9}$/i;
                                    matches = field_value.match(match_pattern);
                                    if (matches == null || field_value.length > 9) error_text = 'КПП должен быть не более 9 цифр';
                                    break;
                                case 'org_bik':
                                    match_pattern = /^\d{4,9}$/i;
                                    matches = field_value.match(match_pattern);
                                    if (matches == null || field_value.length > 9) error_text = 'БИК должен быть не более 9 цифр';
                                    break;
                                case 'org_leader':
                                    if (field_value.length > 200) error_text = '"Руководитель" должно быть не более 200 символов';
                                    break;
                                case 'org_rank':
                                    if (field_value.length > 100) error_text = 'Должность должна быть не более 100 символов';
                                    break;
                                case 'org_ks':
                                    match_pattern = /^\d{10,20}$/i;
                                    matches = field_value.match(match_pattern);
                                    if (matches == null || field_value.length > 20) error_text = 'К/С должен быть не более 20 цифр';
                                    break;
                                case 'org_act_upon':
                                    if (field_value.length > 100) error_text = '"Действует на основании" должно быть не более 100 символов';
                                    break;
                                case 'org_rs':
                                    match_pattern = /^\d{10,20}$/i;
                                    matches = field_value.match(match_pattern);
                                    if (matches == null || field_value.length > 20) error_text = 'Р/с должен быть не более 20 цифр';
                                    break;
                                case 'org_contact':
                                    if (field_value.length > 200) error_text = '"Контактное лицо" должно быть не более 200 символов';
                                    break;
                            }
                        }
                        
                        if ( error_text.length > 0 ) msg.push(error_text);
                    });
                    
                    target = step_el.find('.item_type_caption:visible input'); // все поля ввода данных автомобилей видимых пакетов
                    condition = target.map(function(){return +($(this).val().length > 0);}).get().join(); // эмм... строка со статусами всех полей: 0 - не заполненно, 1 - заполненно
                    if ( condition.indexOf('0') >= 0 && order_status == 2 ) msg.push('Вы не заполнили данные автомобилей для выбранных пакетов'); // в полученной строке есть 0?
                    
                    target.each(function(){
                        console.log($(this).attr('id'));
                        var field_value = $(this).val(),
                            error_text = '';

                        if ( field_value.length > 0 ) {
                            if ($(this).hasClass('car_name') && field_value.length > 150) error_text = 'Марка авто должна быть не более 150 знаков';
                            if ($(this).hasClass('car_vin') && field_value.length > 17) error_text = 'VIN должен быть не более 17 знаков';
                        }

                        if ( error_text.length > 0 ) msg.push(error_text);
                    });
                    
                    break;
            }

            // есть сообщение об ошибке?
            if ( msg.length > 0 ) {
                step_el.find('.cntnt').append('<div class="errors">'+msg.join('<br>')+'</div>');
            } else {
                ret = true;
            }
        }
        return ret;
    }


    var steps_saved = [];
    function save_step(step, save_order, order_status, need_scroll){
        save_order = save_order || false;
        order_status = order_status || 1;  // 1 - save tmp order, 2 - form order
        need_scroll = need_scroll || false;

        console.log('step ' + step + '; need scroll - ' + need_scroll);
        if (!isNaN(step)) {
            step = parseInt(step);
            var step_el = $('#step'+step+' form'),  // объект шага
                target = {},                        // объект с данными
                data;

            step_el.find('.errors, .success').remove();

            switch (step) {
                case 1:
                    target = step_el.find('.form_element:visible, .btn_chkbx.checked input');
                    break;
                case 2:
                    target = step_el.find('.btn_chkbx.checked input, .item_details:visible input:hidden');
                    break;
                case 3:
                    target = step_el.find('input:visible');
                    break;
                case 4:
                    target = step_el.find('.form_element:visible');
                    break;
            }

            if ( target.length > 0 ) {
                data = target.serialize() + '&step='+step;

                $.ajax({
                    url: "../js/ajax.save_step.php",
                    type: "POST",
                    data: data,
                    dataType: 'json',
                    beforeSend: function() { popupOpen(); },
                    success: function (response) {
                        console.log(response);
                        $('#step'+(step+1)+' .cntnt:hidden').slideDown();

                        // плавный скролл до след. шага
                        if ( need_scroll ) {
                            $('html, body').animate({
                                scrollTop: $('#step' + (step + 1)).offset().top - $('#step' + (step + 1) + ' form').height()
                            }, 1000);
                            need_scroll = false;
                        }

                        step_el.find('.cntnt').append('<div class="success">данные успешно сохранены</div>');

                        $('.popup_wrapper').remove();

                        steps_saved.push(step);

                        if ( step > 1 ) {
                            var next_step = step - 1;
                            if ( check_step(next_step, order_status) ) save_step(next_step, save_order, order_status);
                        } else if (step == 1 && save_order) {
                            commit_order(order_status);
                        }

                        step_el.find('.form_btn.green, .form_btn.save_tmp').fadeOut();
                    }
                });
            }
        }
    }

    function commit_order(order_status){
        order_status = order_status || 1; // 1 - save tmp order, 2 - form order
        var popup_html;
        if ( order_status == 1 || order_status == 2 ) {
            $.ajax({
                url: "../js/ajax.save_order.php",
                type: "POST",
                data: 'status=' + order_status,
                dataType: 'json',
                beforeSend: function() { popupOpen(); },
                success: function (response) {
                    // console.log(response, response.bad_lines.length);
                    if ( $('.popup_wrapper:visible').length == 1 ) {
                        if (typeof response.errors !== 'undefined' && response.errors.length > 0) {
                            popup_html =
                                '<div class="popup errors">' +
                                '<b>При сохранении данных возникли ошибки:</b><br>' +
                                response.errors + '<br>' +
                                    /*
                                '<b>output:</b> ' + response.output + '<br>' +
                                '<b>bad_lines:</b> ' + response.bad_lines + '<br>' +
                                '<b>result:</b> ' + response.result +
                                    */
                                '</div>';
                        } else if (order_status == 1) {
                            popup_html =
                                '<div class="popup">' +
                                '<b>Заявка успешно сохранена!</b><br>' +
                                // '<a href="#" class="form_btn close">продолжить</a>' +
                                // '<a href="/" class="form_btn green ok clear_cache">новый заказ</a>' +
                                // '<a href="/orders" class="form_btn">в историю</a>' +
                                '</div>';
                        } else {
                            popup_html =
                                '<div class="popup">' +
                                '<b>Заявка отправлена на обработку, в течении 15 минут с клиентом свяжутся наши операторы.<br><br>За изменением статусов заявки Вы можете наблюдать в <a href="/orders">"истории заявок"</a>.</b><br>' +
                                // '<a href="/" class="form_btn green ok">новый заказ</a>' +
                                // '<a href="/orders" class="form_btn">в историю</a>' +
                                '</div>';
                        }

                        $('.popup_wrapper').html(popup_html);
                    }
                }
            });
        }
    }

    $('.content_block').on('click', '.form_btn.green, .form_btn.save_tmp', function(){
        if ( !$(this).hasClass('edit') ) {
            steps_saved = [];
            var step = parseInt($(this).parent().parent().parent().parent().attr('id').substr(-1)),
                cnt = step,
                errors = [],
                save_order = $(this).hasClass('save_tmp') || step == 4,
                order_status = $(this).hasClass('save_tmp') ? 1 : 2;

            if (!isNaN(step) && step > 0 && step <= 4) {
                while (cnt >= 1) {
                    errors.push(check_step(cnt, order_status));
                    cnt--;
                }
                if (errors.indexOf(false) === -1) {
                    save_step(step, save_order, order_status, step < 4);
                }
            }
        }
    });

    $('html').on('click', '.clear_cache', function(e){
        e.preventDefault();
        $.ajax({
            url: "../js/ajax.clear_order.php",
            type: "POST",
            beforeSend: function () { popupOpen(); },
            success: function () {
                window.location = '/';
            }
        });
    });

    $('.form_element, .cars_count input').on('keyup change', function(){
        var parent = $(this).parentsUntil('.container').last();
        parent.find('.form_btn.green, .form_btn.save_tmp').filter(':hidden').fadeIn();
    });

    if ( $('.check_order_data').length > 0 ) {
        popupOpen();
        var popup_html =
            '<div class="popup">' +
                '<b>Данные текущей заявки не сохранены. Продолжить?</b><br>' +
                '<a href="/" class="form_btn green ok clear_cache">Ок</a>' +
                '<a href="/" class="form_btn">Отмена</a>' +
            '</div>';
        $('.popup_wrapper').html(popup_html);
    }

    //-- end of main page (order form)


    //-- balance page

    $('.fa-question').hover(
        function () { $(this).parent().find('.hint_text').css('display', 'inline'); },
        function () { $(this).parent().find('.hint_text').css('display', 'none'); }
    );

    $('.fa-question').click(function () { $(this).parent().find('.hint_text').css('display', 'inline'); });
    $('.hint_text').click(function() { $(this).css('display', 'none'); });

    $('.rewards_history_period').change(function(){ $(this).parent().submit(); });

    $('.user_card_type input').change(function(){
        var card = parseInt($('.user_card_type input:checked').val());
        if ( isNaN(card) ) $('#reward_card').parentsUntil('tbody').show();
        else $('#reward_card').parentsUntil('tbody').hide();
    });

    //-- end of balance page


    //-- orders history

    $('.details_link').click(function(e){
        e.preventDefault();
        var clicked_id = $(this).attr('id');
        var show_area = $(this).hasClass('show');

        if ( show_area && $('#details_'+clicked_id).is(':hidden') ) $('#details_'+clicked_id).slideDown();
        else  $('#details_'+clicked_id).slideUp();

        $(this).toggleClass('show hide');
    });
    
    $('.order .status a.change').click(function(e){
        e.preventDefault();
        var order_id = parseInt($(this).parent().find('input[name="order_id"]:hidden').val());
        if ( !isNaN(order_id) ) {
            $.ajax({
                url: "../js/ajax.set_order.php",
                type: "POST",
                data: 'order_id=' + order_id,
                dataType: 'json',
                beforeSend: function () { popupOpen(); },
                success: function (res) {
                    if ( res.error == true ) {
                        console.log(res.message);
                    } else {
                        // redirect to main
                        window.location.pathname = '';
                    }
                }
            });
        }
    });

    $('.order .status a.delete').click(function(e){
        e.preventDefault();
        var order_id = parseInt($(this).parent().find('input[name="order_id"]:hidden').val());
        if ( !isNaN(order_id) && confirm('Вы уверены, что хотите удалить заказ №'+order_id+'?') ) {
            $.ajax({
                url: "../js/ajax.delete_order.php",
                type: "POST",
                data: 'order_id=' + order_id,
                beforeSend: function () { popupOpen(); },
                success: function () {
                    window.location.reload(true);
                }
            });
        }
    });

    $('.order .status input[type="checkbox"]').change(function(){
        if ($(this).is(':checked')) {
            if (!confirm('Вы уверены, что хотите удалить эту заявку?') ) $(this).prop('checked', false);
        }
    });

    //-- end of orders history


    //-- users list
    $('a.user.delete').click(function(){
        return confirm('Вы уверены, что хотите удалить этого пользователя и все его данные?');
    });
    //-- end of users list

    $('.debug a').click(function(e){
        e.preventDefault();
        $.ajax({
            url: "../js/ajax.send_cache.php",
            type: "POST",
            beforeSend: function () { popupOpen(); },
            success: function (res) {
                $('.debug a').remove();
                $('.debug').prepend(res);
            }
        });
    });

    $('#editor_mode').click(function(e){
        e.preventDefault();
        var this_obj = $(this);
        $.ajax({
            url: "../js/ajax.editor_mode.php",
            type: "POST",
            dataType: 'json',
            beforeSend: function () { popupOpen(); },
            success: function (response) {
                if (typeof response.errors !== 'undefined' && response.errors.length > 0) {
                    popup_html =
                        '<div class="popup errors">' +
                        '<b>Возникли ошибки:</b><br>' +
                        response.errors + '<br>' +
                        '</div>';
                    $('.popup_wrapper').html(popup_html);
                } else {
                    this_obj.toggleClass('active');
                    window.location.reload();
                    $('.popup_wrapper').remove();
                }
            }
        });
    });
    
    
    $('#entities_per_page').change(function(){ $(this).parent().submit(); });
});