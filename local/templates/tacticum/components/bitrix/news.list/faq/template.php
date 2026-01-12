<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$curPage = $APPLICATION->GetCurPage(false);
?>

<?if(!empty($arResult["ITEMS"])){?>
    <section class="py-16 <?if(substr_count($curPage, "aiagents") != 0){?>bg-gray-50<?}?>">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Часто задаваемые вопросы</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Ответы на популярные вопросы о нашей модели сотрудничества и ценообразовании
                </p>
            </div>
            <div class="max-w-3xl mx-auto">
                <?foreach( $arResult["ITEMS"] as $arItem ){
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')))
                    ?>
                    <div class="faq-item py-4">
                        <div class="faq-question flex items-center justify-between">
                            <h3 class="text-xl font-medium text-secondary"><?=$arItem["NAME"]?></h3>
                            <div class="w-8 h-8 flex items-center justify-center bg-primary/10 rounded-full">
                                <i class="ri-add-line faq-icon text-primary"></i>
                            </div>
                        </div>
                        <div class="faq-answer mt-2 text-gray-600">
                            <p class="mt-2">
                                <?=$arItem["~DETAIL_TEXT"]?>
                            </p>
                        </div>
                    </div>
                <?}?>
            </div>
        </div>
    </section>
<?}?>
