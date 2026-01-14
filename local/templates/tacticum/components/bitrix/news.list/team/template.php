<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//echo "<pre>"; print_r($arResult); echo "</pre>";
?>

<?if(!empty($arResult["ITEMS"])){?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <?foreach( $arResult["ITEMS"] as $arItem ){
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

//            $arPhoto = CFile::ResizeImageGet(
//                $arItem["PROPERTIES"]["PHOTO"]["VALUE"],
//                array("width" => 150, "height" => 200),
//                BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
//                true
//            );

//            $arPhoto = CFile::GetByID($arItem["PROPERTIES"]["PHOTO"]["VALUE"]);

//            echo "<pre>"; print_r($arPhoto); echo "</pre>";
            ?>
            <?php
            // Экранируем пользовательские данные; HTML допускаем только через CBXSanitizer (Bitrix).
            $sanitizer = new \CBXSanitizer();
            $sanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_MIDDLE);
            $memberPhoto = htmlspecialcharsbx(CFile::GetPath($arItem["PROPERTIES"]["PHOTO"]["VALUE"]));
            $memberName = htmlspecialcharsbx($arItem["NAME"]);
            $memberPosition = htmlspecialcharsbx($arItem["PROPERTIES"]["POSITION"]["VALUE"]);
            $memberPreview = $sanitizer->SanitizeHtml((string)$arItem["PREVIEW_TEXT"]);
            $memberDetail = $sanitizer->SanitizeHtml((string)$arItem["DETAIL_TEXT"]);
            $memberLinkedIn = htmlspecialcharsbx($arItem["PROPERTIES"]["LINKEDIN"]["VALUE"]);
            $memberEmail = htmlspecialcharsbx($arItem["PROPERTIES"]["EMAIL"]["VALUE"]);
            ?>

            <div class="team-member bg-white rounded-xl overflow-hidden shadow-sm group relative">
                <div class="h-96 overflow-hidden">
                    <img src="<?=$memberPhoto?>" alt="<?=$memberName?>" class="w-full h-full object-cover object-top">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-secondary mb-1"><?=$memberName?></h3>
                    <p class="text-gray-500 mb-3"><?=$memberPosition?></p>
                    <p class="text-gray-600"><?=$memberPreview?></p>
                </div>
                <div class="member-overlay absolute inset-0 bg-primary/90 p-6 flex flex-col justify-center opacity-0 transition-opacity duration-300">
                    <h3 class="text-xl font-bold text-white mb-3"><?=$memberName?></h3>
                    <p class="text-white/90 mb-4"><?=$memberDetail?></p>
                    <div class="flex items-center gap-3">
                        <?if(!empty($arItem["PROPERTIES"]["LINKEDIN"]["VALUE"])){?>
                            <a href="<?=$memberLinkedIn?>" target="_blank" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                                <i class="ri-linkedin-fill text-white"></i>
                            </a>
                        <?}?>
                        <?if(!empty($arItem["PROPERTIES"]["EMAIL"]["VALUE"])){?>
                            <a href="mailto:<?=$memberEmail?>" target="_blank" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                                <i class="ri-mail-fill text-white"></i>
                            </a>
                        <?}?>
                    </div>
                </div>
            </div>
        <?}?>
    </div>
<?}?>
