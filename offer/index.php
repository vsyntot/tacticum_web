<?
$GLOBALS['TACTICUM_PAGE_ASSETS'] = ['faq'];
$GLOBALS['TACTICUM_BODY_CLASS'] = 'bg-gray-50';

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!function_exists("tacticum_offer_find_element")) {
    function tacticum_offer_find_element(int $offerId, string $offerCode): ?array
    {
        if (!\Bitrix\Main\Loader::includeModule("iblock")) {
            return null;
        }

        $iblockId = tacticum_iblock_id("offer");
        if ($iblockId <= 0) {
            return null;
        }

        $filter = [
            "IBLOCK_ID" => $iblockId,
            "ACTIVE" => "Y",
        ];
        if ($offerCode !== "") {
            if (!preg_match('/^[A-Za-z0-9_-]{1,120}$/', $offerCode)) {
                return null;
            }
            $filter["=CODE"] = $offerCode;
        } elseif ($offerId > 0) {
            $filter["=ID"] = $offerId;
        } else {
            return null;
        }

        $result = CIBlockElement::GetList(
            [],
            $filter,
            false,
            ["nTopCount" => 1],
            [
                "ID",
                "IBLOCK_ID",
                "NAME",
                "CODE",
                "DATE_CREATE",
                "TIMESTAMP_X",
                "PROPERTY_TITLE",
                "PROPERTY_DESCRIPTION",
                "PROPERTY_H1",
            ]
        );
        $element = $result->Fetch();
        if (!$element || trim((string)$element["CODE"]) === "") {
            return null;
        }

        $keywords = [];
        $propertyResult = CIBlockElement::GetProperty(
            $iblockId,
            (int)$element["ID"],
            ["sort" => "asc"],
            ["CODE" => "KEYWORDS"]
        );
        while ($property = $propertyResult->Fetch()) {
            $value = trim(tacticum_decode_iblock_text((string)($property["VALUE"] ?? "")));
            if ($value !== "") {
                $keywords[] = $value;
            }
        }

        $title = trim(tacticum_decode_iblock_text((string)($element["PROPERTY_TITLE_VALUE"] ?? "")));
        if ($title === "") {
            $title = trim(tacticum_decode_iblock_text((string)($element["NAME"] ?? "")));
        }
        if ($title === "") {
            $title = "Пример расчета проекта - Тактикум";
        }

        $description = trim(tacticum_decode_iblock_text((string)($element["PROPERTY_DESCRIPTION_VALUE"] ?? "")));
        if ($description === "") {
            $description = "Пример расчета AI-проекта Tacticum: состав работ, команда, сроки и бюджет.";
        }

        $element["SEO_TITLE"] = $title;
        $element["SEO_DESCRIPTION"] = $description;
        $element["SEO_H1"] = trim(tacticum_decode_iblock_text((string)($element["PROPERTY_H1_VALUE"] ?? "")));
        $element["KEYWORDS"] = $keywords;

        return $element;
    }
}

$offerId = (int)($_REQUEST["ID"] ?? 0);
$offerCode = trim(rawurldecode((string)($_REQUEST["CODE"] ?? "")));
$isOfferDetailRequest = $offerId > 0 || $offerCode !== "";
$offerElement = null;
$offerNotFound = false;
$offerCanonicalPath = "/offer/";

if ($isOfferDetailRequest) {
    $offerElement = tacticum_offer_find_element($offerId, $offerCode);
    $offerNotFound = $offerElement === null;

    if ($offerElement !== null) {
        $offerCanonicalPath = tacticum_offer_detail_path((string)$offerElement["CODE"]);
        $currentPath = parse_url((string)($_SERVER["REQUEST_URI"] ?? ""), PHP_URL_PATH) ?: "/";
        if ($offerId > 0 || $currentPath !== $offerCanonicalPath) {
            LocalRedirect($offerCanonicalPath, true, "301 Moved Permanently");
        }
    }
}

if ($offerNotFound) {
    CHTTP::SetStatus("404 Not Found");
    @define("ERROR_404", "Y");
    $APPLICATION->SetTitle("Предложение не найдено - Тактикум");
    $APPLICATION->SetPageProperty("description", "Запрошенный пример расчета не найден или больше недоступен.");
    $APPLICATION->AddHeadString('<meta name="robots" content="noindex,nofollow">', true);
} elseif ($offerElement !== null) {
    $APPLICATION->SetTitle($offerElement["SEO_TITLE"]);
    $APPLICATION->SetPageProperty("description", $offerElement["SEO_DESCRIPTION"]);
    if (!empty($offerElement["KEYWORDS"])) {
        $APPLICATION->SetPageProperty("keywords", implode(", ", $offerElement["KEYWORDS"]));
    }
    tacticum_apply_seo_defaults($offerCanonicalPath, ["type" => "article"]);
} else {
    $APPLICATION->SetTitle("Предложение - Тактикум");
    $APPLICATION->SetPageProperty("description", "Персональное предложение Tacticum по AI-проекту: состав работ, команда, сроки и заявка на консультацию.");
    tacticum_apply_seo_defaults("/offer/");
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>

<?if (!$isOfferDetailRequest):?>
<section class="py-24 bg-white">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Персональное предложение Tacticum</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto mb-8">
            Персональное предложение формируется после AI-оценки проекта. Опишите задачу в калькуляторе, чтобы получить состав работ, команду и следующий шаг.
        </p>
        <a href="/calculator/" class="inline-flex items-center justify-center rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
            Открыть AI-калькулятор
        </a>
    </div>
</section>
<?elseif ($offerNotFound):?>
<section class="py-24 bg-white">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Предложение не найдено</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto mb-8">
            Возможно, ссылка устарела или расчет был удален. Вы можете сформировать новый пример расчета в AI-калькуляторе.
        </p>
        <a href="/calculator/" class="inline-flex items-center justify-center rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
            Открыть AI-калькулятор
        </a>
    </div>
</section>
<?else:?>
<?
$APPLICATION->IncludeComponent(
    "bitrix:news.detail",
    "offer",
    [
        "COMPONENT_TEMPLATE" => "offer",
        "IBLOCK_TYPE" => "client_requests",
        "IBLOCK_ID" => tacticum_iblock_id('offer'),
        "ELEMENT_ID" => "",
        "ELEMENT_CODE" => (string)$offerElement["CODE"],
        "CHECK_DATES" => "Y",
        "FIELD_CODE" => [
            0 => "ID",
            1 => "CODE",
            2 => "NAME",
            3 => "",
        ],
        "PROPERTY_CODE" => [
            0 => "IS_FINAL",
            1 => "GROUP_ID",
            2 => "RESPONSE_ID",
            3 => "RESPONSE",
            4 => "SUMMARY",
            5 => "GOALS",
            6 => "BUSINESS_CONTEXT",
            7 => "FUNCTIONAL_REQUIREMENTS",
            8 => "NONFUNCTIONAL_REQUIREMENTS",
            9 => "TEAM",
            10 => "STACK",
            11 => "BUDGET",
            12 => "TIMELINE",
            13 => "CLIENT_NAME",
            14 => "TITLE",
            15 => "DESCRIPTION",
            16 => "KEYWORDS",
            17 => "H1",
            18 => "",
        ],
        "IBLOCK_URL" => "/offer/",
        "DETAIL_URL" => "/offer/#ELEMENT_CODE#/",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_GROUPS" => "Y",
        "SET_TITLE" => "N",
        "SET_CANONICAL_URL" => "N",
        "SET_BROWSER_TITLE" => "N",
        "BROWSER_TITLE" => "-",
        "SET_META_KEYWORDS" => "N",
        "META_KEYWORDS" => "-",
        "SET_META_DESCRIPTION" => "N",
        "META_DESCRIPTION" => "-",
        "SET_LAST_MODIFIED" => "N",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
        "ADD_SECTIONS_CHAIN" => "N",
        "ADD_ELEMENT_CHAIN" => "N",
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "USE_PERMISSIONS" => "N",
        "STRICT_SECTION_CHECK" => "N",
        "PAGER_TEMPLATE" => ".default",
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "PAGER_TITLE" => "Страница",
        "PAGER_SHOW_ALL" => "N",
        "PAGER_BASE_LINK_ENABLE" => "N",
        "SET_STATUS_404" => "Y",
        "SHOW_404" => "N",
        "MESSAGE_404" => "Предложение не найдено"
    ],
    false
);
?>
<?endif;?>

<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>
