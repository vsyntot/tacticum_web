<?
$arUrlRewrite = array(
    array(
        'CONDITION' => '#^/offer/([A-Za-z0-9_-]+)/?(?:\?(.*))?$#',
        'RULE' => '$2&CODE=$1',
        'ID' => '',
        'PATH' => '/offer/index.php',
    ),
    array(
        'CONDITION' => '#^/local/rest/#',
        'RULE' => '',
        'ID' => 'bitrix:rest.hook',
        'PATH' => '/local/rest/index.php',
    ),
    array(
        "CONDITION" => "#^/rest/#",
        "RULE" => "",
        "ID" => "bitrix:rest",
        "PATH" => "/bitrix/services/rest/index.php",
    ),
);

?>
