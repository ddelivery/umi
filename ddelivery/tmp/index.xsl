<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">


    <!--Подключение скриптов-->
    <xsl:template match="/" mode="ddscripts">
        <link rel="stylesheet" type="text/css" href="/js/html/assets/the-modal.css" media="all"/>
        <link rel="stylesheet" type="text/css" href="/js/html/assets/demo-modals.css" media="all"/>
        <script type="text/javascript" src="/js/html/assets/jquery.the-modal.js" charset="utf - 8"></script>
        <script type="text/javascript" src="/js/html/js/ddelivery.js" charset="utf - 8"></script>
        <script type="text/javascript" src="/js/html/assets/dd.js" charset="utf - 8"></script>
    </xsl:template>

    <!--Подключение кастомного скрипта-->
    <xsl:template match="/" mode="customddjs">
       <script type="text/javascript" src="/js/html/assets/dd.js" charset="utf - 8"></script>
    </xsl:template>

    <!--Подключение дива в который будут помещаться данные DDelivery-->
    <xsl:template match="/" mode="ddeliverybox" priority="1">
        <div class="modal" id="test-modal" style="display: none; ">
            <div id="ddelivery"></div>
        </div>
    </xsl:template>


    <!--Внимание важно:   При создании своих собственных элементов необходимо подключить шаблоны которые выше
    Например
    <xsl:template match="/" mode="checkbox" priority="1">
        <xsl:apply-templates select="/" mode="ddscripts"/>
        <xsl:apply-templates select="/" mode="ddeliverybox"/>
        <xsl:apply-templates select="/" mode="customddjs"/>
        Здесь располагается ваш элемент запуска службы DDelivery

    </xsl:template>

    -->


<!--Варианты подключений-->

    <!--Подключение checkbox-->
    <xsl:template match="/" mode="checkbox" priority="1">
        <xsl:apply-templates select="/" mode="ddscripts"/>
        <xsl:apply-templates select="/" mode="ddeliverybox"/>
        <xsl:apply-templates select="/" mode="customddjs"/>

        <input type="checkbox" name="ddtrigger" ddfor="dd_start_trigger" value="0"/>Отметьте галочкой если требуется
        оформить доставку
        <br/>
    </xsl:template>


    <!--Подключение checkbox-->
    <xsl:template match="/" mode="radio" priority="1">
        <xsl:apply-templates select="/" mode="ddscripts"/>
        <xsl:apply-templates select="/" mode="ddeliverybox"/>
        <xsl:apply-templates select="/" mode="customddjs"/>

        <input type="radio" name="ddtrigger" ddfor="dd_start_trigger" value="0"/>Выберите, если требуется
        оформить доставку

    </xsl:template>


    <xsl:template match="/" mode="divselect">
        <xsl:apply-templates select="/" mode="ddscripts"/>
        <xsl:apply-templates select="/" mode="ddeliverybox"/>
        <xsl:apply-templates select="/" mode="customddjs"/>


        <img src="/images/cms/admin/mac/icons/medium/ddelivery.png" ddfor="dd_start_trigger"/>
        <input type="checkbox" name="ddtrigger" ddfor="dd_start_trigger" value="0"/>Отметьте галочкой если требуется
        оформить доставку
        <br/>
        <input type="radio" name="ddtrigger" ddfor="dd_start_trigger" value="0"/>Выберите, если требуется
        оформить доставку

    </xsl:template>

</xsl:stylesheet>