1) создать папку assets в корне сайта с правами 777

2) Закачать файлы с модулем на хостинг и положить его в папку /classes/modules/
3) Перейти в модуль конфигурации во вкладку "модули" http://ВАШ_ХОСТ/admin/config/modules/

4) Ввести путь до инсталяционного файла: Например: classes/modules/ddelivery/install.php

   


5) Далее необходимо настроить сам модуль (очень важно правильно настроить соответствия полей)
5.1)  Теперь нужно добавить способ доставки DDelivery - Это можно сделать в модуле Интернет магазина - вкладка - Доставка
    и назвать ее например: "DDelivery - (агрегатор служб доставки) (Пункты самовывоза и курьерская доставка)"


6) Необходимо также (желательно с использованием cron) синхронизировать статусы заказа с сервисом DDelivery
        Для синхронизации необходимо перейти по адресу http://ВАШ_ХОСТ/ddelivery/getStatusesFromDD


7) Если у Вас сайт на TPL шаблонизаторе - то можете его использовать без правок в шаблонах,
     если же у Вас сайт на XSLT шаблонизаторе то в шаблон который выводит сумму доставки нужно добавить параметр disable-output-escaping="yes".
     На стандартных демосайтах этот шаблон находится /templates/demodizzy/xslt/modules/emarket/purchase/delivery.xsl шаблон <xsl:template name="delivery-price">
     То есть:
     Там нужно найти строки
     <xsl:otherwise>
     	<xsl:value-of select="$price" />
     </xsl:otherwise>
    и в строку <xsl:value-of select="$price" /> заменить на  <xsl:value-of select="$price" disable-output-escaping="yes"/>

8) Для того чтобы работали формы автозаполнения для регионов и городов в формах на странице с формой нужно подключить скрипт
    dd.js также должны быть подключены jquery.js и jquery-ui.js
    <script type="text/javascript" src="/assets/html/assets/dd.js" charset="utf - 8"></script>

9) синхронизация статусов доставки происходит при обращении по адресу http//ВАШ_ХОСТ/ddelivery/getStatusesFromDD - желательно настроить такие заходы с использованием cron
Для того, чтобы указанные синхронизация выполнялись автоматически, необходимо настроить в хостинг-панели вашего сайта выполнение по расписанию файла cron.php,
который находится в корне сайта. Схема подключения зависит от вашего хостинг-провайдера, поэтому подробную информацию можно получить в Справке вашего хостинга.
Возможные названия разделов, в которых происходит настройка — «Планировщик заданий», «Управление Crontab» и т.п.


Для того чтобы триггер запуска установить в произвольном месте нужно подключить в шаблоне скрипты
jquery.js,
а также:

<link media="all" href="/assets/html/assets/the-modal.css" type="text/css" rel="stylesheet">
<link media="all" href="/assets/html/assets/demo-modals.css" type="text/css" rel="stylesheet">
<link href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" rel="stylesheet">
<script charset="utf-8" src="/assets/html/assets/jquery.the-modal.js" type="text/javascript">
<script charset="utf-8" src="/assets/html/js/ddelivery.js" type="text/javascript">
<script charset="utf-8" src="/assets/html/assets/dd.js" type="text/javascript">

И разместить в нужном месте HTML:

DDelivery - (агрегатор служб доставки) (Пункты самовывоза и курьерская доставка) -
<div id="test-modal" class="modal" style="display: none; ">
<span id="resultofchoise">Выберите подходящий вам способ доставки:</span>
<a class="trigger" href="javascript:void(0)">Выбор</a>
