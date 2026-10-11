<?php

$query = trim((string)($_SERVER['QUERY_STRING'] ?? ''));
$location = '/adp/';
if ($query !== '') {
    $location .= '?' . $query;
}

header('Location: ' . $location, true, 301);
exit;
