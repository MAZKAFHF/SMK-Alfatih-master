<?php

$configuredHosts = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('TRUSTED_HOSTS', ''))
)));

return [
    'trusted_hosts' => $configuredHosts,
];
