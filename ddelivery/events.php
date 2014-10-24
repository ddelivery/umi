<?php

new umiEventListener('order-status-changed', 'ddelivery', 'onCmsOrderFinish');
new umiEventListener('systemModifyPropertyValue', 'ddelivery', 'onModifyStatusValue');
new umiEventListener('systemModifyObject', 'ddelivery', 'onModifyStatusObject');
