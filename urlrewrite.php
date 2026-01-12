<?
$arUrlRewrite = array(
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