<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?if(!empty($arResult["ITEMS"])){?>
    <div class="mt-16">
        <h3 class="text-xl font-bold text-secondary text-center mb-8">
            Наши клиенты
        </h3>
        <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16">
            <?foreach( $arResult["ITEMS"] as $arItem ){
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')))
                ?>
                <div class="client-logo w-32 h-16 flex items-center justify-center">
                    <div class="text-gray-400 font-bold text-xl"><?=$arItem["NAME"]?></div>
                </div>
            <?}?>
        </div>
    </div>
<?}?>
