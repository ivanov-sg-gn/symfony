var orderLines = [];

$( document ).ready(function() {

    // Выводим корзину
    updateOrderLines();


    // Оформление заказа
    $('.js_create_order').on('submit', function(e){
        e.preventDefault();

        let form = $(this);
        let form_data = form.serialize();

        form.parents('.main').append(load_block);

        form.find('[type=submit]').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: form_data,
            success: function (data) {
                if(data.success == true){
                    window.location = '/basket/success/' + data.order;
                }
                else{
                    form.find('.form_messages').css('color', 'red').html($(`<p>${data.errorMessage}</p>`));
                }

                form.find('[type=submit]').prop('disabled', false);
                form.parents('.main').find('.js_grey_background').remove();
            },
            fail: function(){
                form.find('.form_messages').css('color', 'red').html($('<p>Ошибка в запросе</p>'));

                form.find('[type=submit]').prop('disabled', false);
                form.parents('.main').find('.js_grey_background').remove();
            }
        });
    });



    // Обновление товара
    var timer;
    $('.js_form_basket').on('keyup', 'input[name=count]', function () {
        clearTimeout(timer);
        let _this = $(this);

        let token = _this.parents('.js_form_basket').find('[name=token]').val();
        let goods = _this.parents('tr').find('input[name=goods]').val();
        let count = _this.parents('tr').find('input[name=count]').val();


        if (orderLines[goods].goods_qnt == count){
            return;
        }


        timer = setTimeout(function(){
            _this.parents('form').append(load_block);

            let form_data = {
                position: [
                    {
                        goods: goods,
                        count: count
                    }
                ],
                token: token
            };

            $.ajax({
                url: '/api/basket/update',
                type: 'POST',
                dataType: 'json',
                data: form_data,
                success: function (data) {
                    if(data.success == true){
                        updateOrderLines();
                    }
                    else{
                        _this.parents('tr').css('background', '#f44336');
                        setTimeout(function () {
                            _this.parents('tr').css('background', '');
                        }, 200);
                    }

                    _this.parents('form').find('.js_grey_background').remove();
                },
                fail: function(){
                    _this.parents('tr').css('background', '#f44336');
                    setTimeout(function () {
                        _this.parents('tr').css('background', '');
                    }, 200);

                    _this.parents('form').find('.js_grey_background').remove();
                }
            });
        }, 500);
    });

    // Удаление
    $('.js_form_basket').on('click', '.js_basket_delete_item', function (e) {
        e.preventDefault();

        let _this = $(this);
        let token = $(this).parents('.js_form_basket').find('input[name=token]').val();
        let goods = $(this).parents('tr').find('input[name=goods]').val();


        _this.parents('form').append(load_block);

        $.ajax({
            url: '/api/basket/del',
            type: 'POST',
            dataType: 'json',
            data: {token: token, goods: [goods]},
            success: function (data) {
                if(data.success == true){
                    updateOrderLines();
                }
                else{
                    _this.parents('tr').css('background', '#f44336');
                    setTimeout(function () {
                        _this.parents('tr').css('background', '');
                    }, 200);
                }

                _this.parents('form').find('.js_grey_background').remove();
            },
            fail: function(){
                _this.parents('tr').css('background', '#f44336');
                setTimeout(function () {
                    _this.parents('tr').css('background', '');
                }, 200);

                _this.parents('form').find('.js_grey_background').remove();
            }
        });
    });

});


function updateOrderLines(func){
    let _form = $('.js_form_basket');
    let token = _form.find('[name=token]').val();


    _form.append(load_block);

    $.ajax({
        url: "/api/basket/get",
        type: 'POST',
        dataType: 'json',
        data: { token: token },
        success: function (data) {
            if(data.success == true) {
                orderLines = data.data;

                viewOrderLines(data.data);

                if( typeof func == 'function' ){
                    func();
                }

            }
            else{
                _form.find('tbody').html($(`<tr><td colspan="${_form.find('thead tr td').length}">${data.errorMessage}</td></tr>`)).css('text-align', 'center');
            }

            _form.append(load_block).find('.js_grey_background').remove();
        },
        fail: function(){
            _form.find('tbody').html($(`<tr><td colspan="${_form.find('thead tr td').length}">Ошибка загрузки корзины</td></tr>`)).css('text-align', 'center');

            _form.append(load_block).find('.js_grey_background').remove();
        }
    });
}

function viewOrderLines(array){
    let _form = $('.js_form_basket');

    let i = 1;
    let count = 0;
    let cost = 0;

    _form.find('tbody').html('');
    _form.find('tfoot').html('');

    $.each(array, function (key, val) {
        count += val.goods_qnt;
        cost += val.goods_price * val.goods_qnt;

        _form.find('tbody').append(
            $(`<tr>
                <td>${ i }</td>
                <td><a href="${val.goods_info.link}">${val.goods_info.name}</a></td>
                <td>
                    <input type="hidden" value="${val.goods_id}" name="goods">
                    <input type="text" value="${val.goods_qnt}" name="count" class="basket_goods_count"> шт
                </td>
                <td>${val.goods_price} руб</td>
                <td>${val.goods_price * val.goods_qnt} руб</td>
                <td><a href="" class="js_basket_delete_item">Удалить</a></td>
            </tr>`)
        );
        i ++;
    });

    _form.find('tfoot').html(
        $(`<tr>
            <td colspan="2"><b>Итого</b></td>
            <td><b>${ count } шт</b></td>
            <td></td>
            <td><b>${ cost } руб</b></td>
            <td></td>
        </tr>`)
    );

}