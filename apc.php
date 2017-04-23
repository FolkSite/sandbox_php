<?php
$bar = 'BAR';
apc_store('foo', $bar);
var_dump(apc_fetch('foo'));
