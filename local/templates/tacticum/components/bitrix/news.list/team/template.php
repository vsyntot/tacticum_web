<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php if (!empty($arResult['ITEMS'])) { ?>
    <?php
    $teamCount = count($arResult['ITEMS']);
    $teamGridClass = 'tacticum-team-grid';
    if ($teamCount <= 2) {
        $teamGridClass .= ' tacticum-team-grid--compact';
    }
    ?>
    <div class="<?=$teamGridClass?>">
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
                    class="team-member tacticum-team-card">
                <div class="tacticum-team-card__photo">
                    <?php if ($memberPhoto !== '') { ?>
                        <img
                                src="<?=$memberPhoto?>"
                                alt="<?=$memberName?>"
                                loading="lazy"
                                decoding="async"
                                class="tacticum-team-card__image">
                    <?php } else { ?>
                        <div class="tacticum-team-card__placeholder" aria-hidden="true">
                            <i class="ri-user-3-line text-4xl"></i>
                        </div>
                    <?php } ?>
                </div>
                <div class="tacticum-team-card__body">
                    <div class="tacticum-team-card__head">
                        <h3 class="tacticum-team-card__name"><?=$memberName?></h3>
                        <?php if ($memberPosition !== '') { ?>
                            <p class="tacticum-team-card__position"><?=$memberPosition?></p>
                        <?php } ?>
                    </div>
                    <?php if ($memberPreviewText !== '') { ?>
                        <div class="tacticum-team-card__focus">
                            <p class="tacticum-team-card__label">Фокус в запуске</p>
                            <div class="tacticum-team-card__text"><?=$memberPreview?></div>
                        </div>
                    <?php } ?>
                    <?php if ($hasMemberDetail) { ?>
                        <div class="tacticum-team-card__detail">
                            <p class="tacticum-team-card__label">Опыт</p>
                            <div class="tacticum-team-card__text"><?=$memberDetail?></div>
                        </div>
                    <?php } ?>
                    <?php if ($memberLinkedInRaw !== '' || $memberEmailRaw !== '') { ?>
                        <div class="tacticum-team-card__contacts" role="group" aria-label="Контакты участника команды">
                            <?php if ($memberLinkedInRaw !== '') { ?>
                                <a
                                        href="<?=$memberLinkedIn?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="tacticum-team-card__contact"
                                        aria-label="Профиль LinkedIn: <?=$memberName?>">
                                    <i class="ri-linkedin-fill" aria-hidden="true"></i>
                                </a>
                            <?php } ?>
                            <?php if ($memberEmailRaw !== '') { ?>
                                <a
                                        href="mailto:<?=$memberEmail?>"
                                        class="tacticum-team-card__contact"
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
