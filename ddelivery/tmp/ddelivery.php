<?php


class ddeliveryDelivery extends delivery
{

    public function validate(order $order)
    {
        // TODO: Implement validate() method.
        return true;
    }

    public function getDeliveryPrice(order $order)
    {

        if (!def_module::isXSLTResultMode()) {

            return <<<END
<link rel="stylesheet" type="text/css" href="/assets/html/assets/the-modal.css" media="all"/>
<link rel="stylesheet" type="text/css" href="/assets/html/assets/demo-modals.css" media="all"/>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script type="text/javascript" src="/assets/html/assets/jquery.the-modal.js" charset="utf - 8"></script>
<script type="text/javascript" src="/assets/html/js/ddelivery.js" charset="utf - 8"></script>
<script type="text/javascript" src="/assets/html/assets/dd.js" charset="utf - 8"></script>
<div class="modal" id="test-modal" style="display: none; ">
    <div id="ddelivery"></div>
</div>
<span id = "resultofchoise" >Выберите подходящий вам способ доставки:</span> <a href="javascript:void(0)" class="trigger">Выбор</a>
END;
        } else {

            return <<<END
<link rel="stylesheet" type="text/css" href="/assets/html/assets/the-modal.css" media="all"/>
<link rel="stylesheet" type="text/css" href="/assets/html/assets/demo-modals.css" media="all"/>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script type="text/javascript" src="/assets/html/assets/jquery.the-modal.js" charset="utf - 8"></script>
<script type="text/javascript" src="/assets/html/js/ddelivery.js" charset="utf - 8"></script>
<script type="text/javascript" src="/assets/html/assets/dd.js" charset="utf - 8"></script>
<div class="modal" id="test-modal" style="display: none; ">
    <div id="ddelivery"></div>
</div>
<span id = "resultofchoise" >Выберите подходящий вам способ доставки:</span> <a href="javascript:void(0)" class="trigger">Выбор</a>
END;
        }
    }
}
