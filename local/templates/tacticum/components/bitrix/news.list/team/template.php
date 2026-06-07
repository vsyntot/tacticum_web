<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php if (!empty($arResult['ITEMS'])) { ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <?php foreach ($arResult['ITEMS'] as $arItem) {
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);

            $memberPhotoValue = $arItem['PROPERTIES']['PHOTO']['VALUE'] ?? null;
            $memberPhotoPath = !empty($memberPhotoValue) ? (string)CFile::GetPath($memberPhotoValue) : '';
            $memberPhoto = htmlspecialcharsbx($memberPhotoPath);
            $memberName = tacticum_escape_iblock_text((string)$arItem['NAME']);
            $memberPosition = tacticum_escape_iblock_text((string)($arItem['PROPERTIES']['POSITION']['VALUE'] ?? ''));
            $memberPreview = tacticum_sanitize_iblock_html((string)($arItem['~PREVIEW_TEXT'] ?? $arItem['PREVIEW_TEXT'] ?? ''));
            $memberDetail = tacticum_sanitize_iblock_html((string)($arItem['~DETAIL_TEXT'] ?? $arItem['DETAIL_TEXT'] ?? ''));
            $memberPreviewText = trim(strip_tags($memberPreview));
            $memberDetailText = trim(strip_tags($memberDetail));
            $hasMemberDetail = $memberDetailText !== '' && $memberDetailText !== $memberPreviewText;
            $memberLinkedInRaw = trim((string)($arItem['PROPERTIES']['LINKEDIN']['VALUE'] ?? ''));
            $memberEmailRaw = trim((string)($arItem['PROPERTIES']['EMAIL']['VALUE'] ?? ''));
            $memberLinkedIn = htmlspecialcharsbx($memberLinkedInRaw);
            $memberEmail = htmlspecialcharsbx($memberEmailRaw);
            ?>

            <article
                    id="<?=$this->GetEditAreaId($arItem['ID']);?>"
                    class="team-member h-full bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow border border-gray-100 flex flex-col">
                <div class="h-80 overflow-hidden bg-gray-50">
                    <?php if ($memberPhoto !== '') { ?>
                        <img
                                src="<?=$memberPhoto?>"
                                alt="<?=$memberName?>"
                                loading="lazy"
                                decoding="async"
                                class="w-full h-full object-cover object-top">
                    <?php } else { ?>
                        <div class="w-full h-full flex items-center justify-center bg-primary/10 text-primary" aria-hidden="true">
                            <i class="ri-user-3-line text-4xl"></i>
                        </div>
                    <?php } ?>
                </div>
                <div class="p-6 flex flex-col flex-1">
                    <div class="mb-5">
                        <h3 class="text-xl font-bold text-secondary mb-1"><?=$memberName?></h3>
                        <?php if ($memberPosition !== '') { ?>
                            <p class="text-gray-500"><?=$memberPosition?></p>
                        <?php } ?>
                    </div>
                    <?php if ($memberPreviewText !== '') { ?>
                        <div class="text-gray-600 leading-relaxed mb-4"><?=$memberPreview?></div>
                    <?php } ?>
                    <?php if ($hasMemberDetail) { ?>
                        <div class="text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-4"><?=$memberDetail?></div>
                    <?php } ?>
                    <?php if ($memberLinkedInRaw !== '' || $memberEmailRaw !== '') { ?>
                        <div class="mt-auto pt-6 flex items-center gap-3" role="group" aria-label="Контакты участника команды">
                            <?php if ($memberLinkedInRaw !== '') { ?>
                                <a
                                        href="<?=$memberLinkedIn?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-colors"
                                        aria-label="Профиль LinkedIn: <?=$memberName?>">
                                    <i class="ri-linkedin-fill" aria-hidden="true"></i>
                                </a>
                            <?php } ?>
                            <?php if ($memberEmailRaw !== '') { ?>
                                <a
                                        href="mailto:<?=$memberEmail?>"
                                        class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-colors"
                                        aria-label="Написать участнику команды: <?=$memberName?>">
                                    <i class="ri-mail-fill" aria-hidden="true"></i>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </article>
        <?php } ?>
    </div>
<?php } ?>
