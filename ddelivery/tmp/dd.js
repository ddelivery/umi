function DDeliveryStart() {
    jQuery('#test-modal').modal().open();

    var params = {
        //orderId: 4
    };

    console.log(params);

    var callback = {
        close: function () {
            closePopup();
            alert('Окно закрыто');
        },
        change: function (data) {
            $('#continue').html('<a href="/emarket/purchase/payment/choose/">Продолжить оформление</a>');
            closePopup();
            alert(data.comment + ' интернет магазину нужно взять с пользователя за доставку ' + data.clientPrice + ' руб. OrderId: ' + data.orderId);
        }
    };



    DDelivery.delivery(
        'ddelivery',
        '/ddelivery/ajax/? <?php isset($_GET["XDEBUG_SESSION_START"]) ? "XDEBUG_SESSION_START=".(int)$_GET["XDEBUG_SESSION_START"] : ""; ?>',
        params,
        callback
    );
}

function closePopup()
{
    jQuery(function($){
         $.modal().close();

    })
}

/**
 * Динамическое подключение скриптов
 * @constructor
 */
function GetScript(src) {
    //Подключаю внешний скрипт и запускаю из него метод
    $$i({
        create:'script',
        attribute: {
            'type':'text/javascript',
            'src': src//адрес на подключаемый скрипт
        },
        insert:$$().body,
        onready:function() {
            modules.sound.start();//этот метод запускается уже из подключенного скрипта
        }
    });
}


jQuery(document).ready(function($) {




    // bind event handlers to modal triggers
//    $('body').on('click', '.trigger', function(e){
    $('body').on('click', '[ddfor="dd_start_trigger"]', function(e){

          if ($(this).attr('type') == 'checkbox'){
              if ($(this).prop("checked")){
                  e.preventDefault();
                  DDeliveryStart();
                  return;

              }
          }

        if ($(this).attr('type') == 'radio'){
            if ($(this).prop("checked")){
                e.preventDefault();
                DDeliveryStart();
                return;
            }
        }

        e.preventDefault();
        DDeliveryStart();

    });
});


/**
 * Подключаем нужные скрипты
 */

//if (typeof(jQuery) == 'undefined') {
//    GetScript('http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js');
//}