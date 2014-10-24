<?php
$FORMS = Array();

$FORMS['delivery_block'] = <<<END
	Выберите подходящий вам способ доставки:

<link rel="stylesheet" type="text/css" href="/assets/html/assets/the-modal.css" media="all"/>
<link rel="stylesheet" type="text/css" href="/assets/html/assets/demo-modals.css" media="all"/>
<script type="text/javascript" src="/assets/html/assets/jquery.the-modal.js" charset="utf - 8"></script>
<script type="text/javascript" src="/assets/html/js/ddelivery.js" charset="utf - 8"></script>
<script type="text/javascript" src="/assets/html/assets/dd.js" charset="utf - 8"></script>
<script type="text/javascript" src="/assets/html/assets/dd.js" charset="utf - 8"></script>
<div class="modal" id="test-modal" style="display: none; ">
    <div id="ddelivery"></div>
</div>

	<ul>
		%items%
    <li>
    <input type="checkbox" value="" ddfor="dd_start_trigger" name="delivery-id">
        Оформить доставку DDelivery
    </li>

	</ul>
END;

$FORMS['delivery_item_free'] = <<<END
	<li><input type="radio" name="delivery-id" value="%id%" %checked%/> %name% - бесплатно</li>
END;

$FORMS['delivery_item_priced'] = <<<END
	<li><input type="radio" name="delivery-id" value="%id%" %checked%/> %name% - %price%</li>
END;

$FORMS['self_delivery_block'] = <<<END
	<p></p>
	или пункт выдачи:
		%items%
	<p></p>
	или укажите новый адрес:
	<p></p>
END;

$FORMS['self_delivery_item_free'] = <<<END
	<li><input type="radio" name="delivery-address" value="delivery_%id%" %checked%/> %name% - бесплатно</li>
END;

$FORMS['self_delivery_item_priced'] = <<<END
	<li><input type="radio" name="delivery-address" value="delivery_%id%" %checked%/> %name% - %price%</li>
END;

$FORMS['delivery_address_block'] = <<<END
	<p>Выберите адрес доставки:</p>
		%items%
		%delivery%
		<input type="radio" name="delivery-address" value="new" /> Новый адрес доставки:
	<p></p>
	%data getCreateForm(%type_id%, 'purchase')%
END;

$FORMS['delivery_address_item'] = <<<END
	<li><input type="radio" name="delivery-address" value="%id%" %checked%/> %data getPropertyGroupOfObject(%id%, 'common', 'purchase')%</li>
END;
?>