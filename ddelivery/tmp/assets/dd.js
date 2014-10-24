function DDeliveryStart() {
    jQuery('#test-modal').modal().open();

    var params = {
        //orderId: 4
    };


    var callback = {
        close: function () {
            closePopup();
            //alert('Окно закрыто');
        },
        change: function (data) {
            $('#continue').html('<a href="/emarket/purchase/payment/choose/">Продолжить оформление</a>');
            closePopup();
            $('#resultofchoise').html(data.comment + ' Стоимость доставки ' + data.clientPrice + ' руб.');
            //alert(data.comment + ' интернет магазину нужно взять с пользователя за доставку ' + data.clientPrice + ' руб. OrderId: ' + data.orderId);
        }
    };


    DDelivery.delivery(
        'ddelivery',
        '/ddelivery/ajax/? <?php isset($_GET["XDEBUG_SESSION_START"]) ? "XDEBUG_SESSION_START=".(int)$_GET["XDEBUG_SESSION_START"] : ""; ?>',
        params,
        callback
    );
}

function closePopup() {
    jQuery(function ($) {
        $.modal().close();

    })
}


jQuery(document).ready(function ($) {
    // bind event handlers to modal triggers


    /**
     * При редактировании полей для новой формы отметим галочкой что адрес доставки новый
     */
    $("form [name*='data[new]']").on('change', function (){
        $("input[value='new']").attr('checked', true);
    });

    /**
     * Определим селекторы города и региона
     * @type {*|jQuery|HTMLElement}
     */
    var city = $('[name="data[new][city]"]');
    var region = $('[name="data[new][region]"]');


    /**
     * Запуск DDelivery при клике на 'Выбор'
     */
    $('body').on('click', '.trigger', function (e) {

        //Отметим checkbox (предпочитаемый способ доставки)
        check = $(this).parents('li').children('input');
        check.attr('checked', true);


        //Заберем все value формы
        form = $(this).closest('form');
        array = jQuery.param(form.serializeArray());

        //Заберем id города если он был найден до этого
        if (typeof city.attr('bid') === 'undefined') {
            bid = '';
        } else {
            bid = '&bid=' + city.attr('bid');
        }

        //Отправим данные формы в сессию
        jQuery.post('/ddelivery/setAddressFromForm', array + bid);

        e.preventDefault();
        DDeliveryStart();

    });


    /**
     * Навесим дефолтный автокомплит на выбор города
     */
    city.autocomplete({
        source: "/ddelivery/cities/",
        minLength: 2,
        select: function (event, ui) {
            city.attr('bid', ui.item.id);
        }
    });


    /**
     * Навесим дефолтный автокомплит на регион
     */
    region.autocomplete({
        source: "/ddelivery/regions",
        minLength: 2,
        select: function (event, ui) {
            region.attr('value', ui.item.value);
            /**
             * При выборе региона необходимо перевесить автокомплит и указать регион
             */
            city.autocomplete({
                source: "/ddelivery/cities/?region=" + region.attr('value'),
                minLength: 2,
                select: function (event, ui) {
                    city.attr('bid', ui.item.id);
                }
            });
        }
    });

});
