<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$officeAddress = '119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1, помещ. 3/3';
$officePlaceName = 'Тактикум';
$officeLandmarkName = 'БЦ Victory Park';
$officeMapObjectId = '243968538014';
$officeLatitude = '55.723957';
$officeLongitude = '37.503747';
$officeMapPoint = $officeLongitude . ',' . $officeLatitude;
$officeMapZoom = '17.13';

$arResult = [
    'OFFICE_ADDRESS' => $officeAddress,
    'OFFICE_PLACE_NAME' => $officePlaceName,
    'OFFICE_LANDMARK_NAME' => $officeLandmarkName,
    'OFFICE_MAP_URL' => 'https://yandex.ru/maps/org/taktikum/' . $officeMapObjectId . '/?' . http_build_query([
        'll' => $officeMapPoint,
        'z' => $officeMapZoom,
    ]),
    'OFFICE_MAP_WIDGET_URL' => 'https://yandex.ru/map-widget/v1/?' . http_build_query([
        'll' => $officeMapPoint,
        'mode' => 'search',
        'oid' => $officeMapObjectId,
        'ol' => 'biz',
        'z' => $officeMapZoom,
    ]),
];

$this->IncludeComponentTemplate();
