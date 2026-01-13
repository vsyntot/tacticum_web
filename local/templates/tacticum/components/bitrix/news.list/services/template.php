<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?if(!empty($arResult["ITEMS"])){?>
    <!-- Services Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Наши услуги</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Мы помогаем компаниям на всех этапах цифровой трансформации — от диагностики и планирования до
                    разработки и внедрения AI-решений
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?foreach( $arResult["ITEMS"] as $arItem ){
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                    $class = $arItem["PROPERTIES"]["CLASS"]["VALUE"];
                    if(empty($class)){
                        $class = "brain";
                    }

                    $link = $arItem["PROPERTIES"]["LINK"]["VALUE"];
                    $linktext = "";
                    if(!empty($link)){
                        $linktext = $arItem["PROPERTIES"]["LINKTEXT"]["VALUE"];

                        if(empty($linktext)){
                            $linktext = "Воспользоваться";
                        }
                    }?>
                    <?php
                    // Экранируем пользовательские данные; не удалять при правках шаблона.
                    $serviceName = htmlspecialcharsbx($arItem["NAME"]);
                    $serviceIconClass = htmlspecialcharsbx($class);
                    $serviceLink = htmlspecialcharsbx($link);
                    $serviceLinkText = htmlspecialcharsbx($linktext);
                    ?>

                    <div class="service-card bg-white rounded-xl p-8 shadow-sm">
                        <div class="service-icon w-20 h-20 bg-primary/10 rounded-xl flex items-center justify-center mb-6">
                            <i class="ri-<?=$serviceIconClass?>-line text-3xl text-primary"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-secondary mb-4"><?=$serviceName?></h3>
                        <?if(!empty($arItem["PROPERTIES"])){?>
                            <ul class="space-y-3 mb-8">
                            <?foreach($arItem["PROPERTIES"]["OPTIONS"]["VALUE"] as $sOption){?>
                                <li class="flex items-start gap-3">
                                    <div class="w-6 h-6 flex-shrink-0 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                                        <i class="ri-check-line text-primary"></i>
                                    </div>
                                    <span class="text-gray-600"><?=htmlspecialcharsbx($sOption)?></span>
                                </li>
                            <?}?>
                            </ul>
                            <?if(!empty($link)){?>
                                <button class="text-primary font-medium flex items-center gap-2 hover:gap-3 transition-all">
                                    <a href="<?=$serviceLink?>"><?=$serviceLinkText?> <i class="ri-arrow-right-line"></i></a>
                                </button>
                            <?}?>
                        <?}?>
                    </div>
                <?}?>
            </div>
        </div>
    </section>
<?}?>
