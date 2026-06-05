<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Контакты Tacticum - продукты, внедрение и AI-проекты");
$APPLICATION->SetPageProperty("description", "Контакты Tacticum: телефон, email, адрес офиса и форма заявки по AI-продуктам, внедрению, оценке проекта или подбору команды.");
tacticum_apply_seo_defaults('/contacts/', [
    'schema' => [
        '@type' => 'ContactPage',
        '@id' => tacticum_public_url('/contacts/#contact-page'),
        'name' => 'Контакты Tacticum',
        'url' => tacticum_public_url('/contacts/'),
        'mainEntity' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
    ],
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:contacts.page",
    "",
    [],
    false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
