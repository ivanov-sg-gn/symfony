var load_block = $('<div class="js_grey_background grey_background"><span>Подождите, идёт загрузка</span></div>');
var grey_background_block = $('<div class="grey_background"></div>');



$(function () {
    // Добавление в корзину
    $(".js_add_basket").on('click', function (e) {
        e.preventDefault();

        var button = $(this);

        let tools = $(this).parent();
        let id = parseInt(tools.find("[name=goods_id]").val());
        let count = parseInt(tools.find("[name=goods_count]").val());
        let token = tools.find("[name=token]").val();

        if(id <= 0 || count <= 0){
            return false;
        }

        $.ajax({
            url: "/api/basket/add",
            type: 'POST',
            dataType: 'json',
            data: {id: id, count: count, token: token},
            success: function (data) {
                if(data.success == true){
                    button.text("Добавлен").css('color', '#4caf50');
                }
                else{
                    button.text("Ошибка").css('color', '#ff0000');
                }
            },
            fail: function(){
                button.text("Ошибка").css('color', '#ff0000');
            }
        });

        setTimeout(function () {
            button.text("В корзину").css('color', '');
        }, 800);

    });



    // Форма авторизации
    $('.js_authorization_form').on('click', function(e){
        e.preventDefault();

        $('body').css('overflow-y', 'hidden');

        let authorization_form = $('.authorization_form').show();
        authorization_form.find('input[name=username]').focus();


        authorization_form.find('.js_registration').on('click', function (e) {
            e.preventDefault();

            location.href = '/register';
        });

        authorization_form.find('form').on('submit', function (e) {
            e.preventDefault();

            let _form = $(this);

            let data = JSON.stringify({
                username: _form.find('input[name=username]').val(),
                password: _form.find('input[name=password]').val(),
                token: _form.find('input[name=token]').val()
            });


            $.ajax({
                url: '/json-login',
                headers: {'Content-Type':'application/json'},
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (data) {
                    if(data.success == true){
                        // ...
                    }
                    if('redirect_path' in data){
                        location.href = data.redirect_path;
                    }
                },
                error: function(data){
                    _form.find('.js_form_errors').html(data.responseJSON.message);
                }
            });
        });


        grey_background_block.click(function () {
            $('body').css('overflow-y', '');
            $(this).remove();
            authorization_form.hide();
        }).appendTo('body');

    });

})

