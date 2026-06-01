<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<form<?php if ($arResult['FORM_HTML_ID'] !== ''): ?> id="<?=htmlspecialcharsbx($arResult['FORM_HTML_ID'])?>"<?php endif; ?>
      class="<?=htmlspecialcharsbx($formClass)?>"
      data-tacticum-form
      data-form-id="<?=htmlspecialcharsbx($arResult['FORM_ID'])?>"
      <?php if ($arResult['ENDPOINT'] !== ''): ?>data-endpoint="<?=htmlspecialcharsbx($arResult['ENDPOINT'])?>"<?php endif; ?>
      <?php if ($arResult['CLOSE_TARGET'] !== ''): ?>data-tacticum-close-target="<?=htmlspecialcharsbx($arResult['CLOSE_TARGET'])?>"<?php endif; ?>
      <?php if ($arResult['CLOSE_MODE'] !== ''): ?>data-tacticum-close-mode="<?=htmlspecialcharsbx($arResult['CLOSE_MODE'])?>"<?php endif; ?>>
    <?php foreach ($arResult['LEAD_CONTEXT'] as $contextName => $contextValue): ?>
        <input type="hidden" name="<?=htmlspecialcharsbx($contextName)?>" value="<?=htmlspecialcharsbx($contextValue)?>">
    <?php endforeach; ?>

    <?php if ($arResult['FORM_TITLE'] !== ''): ?>
        <h3 class="text-xl font-bold mb-6"><?=htmlspecialcharsbx($arResult['FORM_TITLE'])?></h3>
    <?php endif; ?>

    <div class="<?=htmlspecialcharsbx($gridClass)?>">
        <div class="<?=htmlspecialcharsbx($fieldClass)?>">
            <?php if (!$labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($nameId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Имя<?=$requiredMark?></label>
            <?php endif; ?>
            <input type="text" id="<?=htmlspecialcharsbx($nameId)?>" name="name" required autocomplete="name" placeholder="<?=htmlspecialcharsbx($namePlaceholder)?>"
                   class="<?=htmlspecialcharsbx($controlClass)?>">
            <?php if ($labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($nameId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Имя</label>
            <?php endif; ?>
        </div>
        <div class="<?=htmlspecialcharsbx($fieldClass)?>">
            <?php if (!$labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($companyId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Компания</label>
            <?php endif; ?>
            <input type="text" id="<?=htmlspecialcharsbx($companyId)?>" name="company" autocomplete="organization" placeholder="<?=htmlspecialcharsbx($companyPlaceholder)?>"
                   class="<?=htmlspecialcharsbx($controlClass)?>">
            <?php if ($labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($companyId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Компания</label>
            <?php endif; ?>
        </div>
        <div class="<?=htmlspecialcharsbx($fieldClass)?>">
            <?php if (!$labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($emailId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Email<?=$requiredMark?></label>
            <?php endif; ?>
            <input type="email" id="<?=htmlspecialcharsbx($emailId)?>" name="email" required autocomplete="email" placeholder="<?=htmlspecialcharsbx($emailPlaceholder)?>"
                   class="<?=htmlspecialcharsbx($controlClass)?>">
            <?php if ($labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($emailId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Email</label>
            <?php endif; ?>
        </div>
        <div class="<?=htmlspecialcharsbx($fieldClass)?>">
            <?php if (!$labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($phoneId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Телефон<?=$requiredMark?></label>
            <?php endif; ?>
            <input type="tel" id="<?=htmlspecialcharsbx($phoneId)?>" name="phone" required autocomplete="tel" placeholder="<?=htmlspecialcharsbx($phonePlaceholder)?>"
                   class="<?=htmlspecialcharsbx($controlClass)?>">
            <?php if ($labelAfterControl): ?>
                <label for="<?=htmlspecialcharsbx($phoneId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Телефон</label>
            <?php endif; ?>
        </div>
    </div>

    <div class="<?=htmlspecialcharsbx($messageClass)?>">
        <?php if (!$labelAfterControl): ?>
            <label for="<?=htmlspecialcharsbx($messageId)?>" class="<?=htmlspecialcharsbx($labelClass)?>"><?=htmlspecialcharsbx($arResult['MESSAGE_LABEL'])?><?=$requiredMark?></label>
        <?php endif; ?>
        <textarea id="<?=htmlspecialcharsbx($messageId)?>" name="message" rows="4" required placeholder="<?=htmlspecialcharsbx($messagePlaceholder)?>"
                  class="<?=htmlspecialcharsbx($controlClass)?>"></textarea>
        <?php if ($labelAfterControl): ?>
            <label for="<?=htmlspecialcharsbx($messageId)?>" class="<?=htmlspecialcharsbx($labelClass)?>"><?=htmlspecialcharsbx($arResult['MESSAGE_LABEL'])?></label>
        <?php endif; ?>
    </div>

    <?php if (!empty($arResult['SCENARIO_OPTIONS']) || $arResult['SHOW_QUALIFICATION']): ?>
        <div class="<?=htmlspecialcharsbx($gridClass)?>">
            <?php if (!empty($arResult['SCENARIO_OPTIONS'])): ?>
                <div class="<?=htmlspecialcharsbx($fieldClass)?>">
                    <label for="<?=htmlspecialcharsbx($scenarioId)?>" class="<?=htmlspecialcharsbx($selectLabelClass)?>"><?=htmlspecialcharsbx($arResult['SCENARIO_LABEL'])?></label>
                    <select id="<?=htmlspecialcharsbx($scenarioId)?>" name="lead_scenario" class="<?=htmlspecialcharsbx($selectControlClass)?>">
                        <option value=""><?=htmlspecialcharsbx($arResult['SCENARIO_EMPTY_LABEL'])?></option>
                        <?php foreach ($arResult['SCENARIO_OPTIONS'] as $scenarioOption): ?>
                            <option value="<?=htmlspecialcharsbx($scenarioOption['VALUE'])?>"><?=htmlspecialcharsbx($scenarioOption['LABEL'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($arResult['SHOW_QUALIFICATION']): ?>
                <div class="<?=htmlspecialcharsbx($fieldClass)?>">
                    <label for="<?=htmlspecialcharsbx($budgetId)?>" class="<?=htmlspecialcharsbx($selectLabelClass)?>">Бюджетный ориентир</label>
                    <select id="<?=htmlspecialcharsbx($budgetId)?>" name="lead_budget" class="<?=htmlspecialcharsbx($selectControlClass)?>">
                        <option value="">Пока не определен</option>
                        <option value="up-to-1m">до 1 млн руб.</option>
                        <option value="1-3m">1-3 млн руб.</option>
                        <option value="3-7m">3-7 млн руб.</option>
                        <option value="7m-plus">7+ млн руб.</option>
                    </select>
                </div>
                <div class="<?=htmlspecialcharsbx($fieldClass)?>">
                    <label for="<?=htmlspecialcharsbx($timelineId)?>" class="<?=htmlspecialcharsbx($selectLabelClass)?>">Желаемый срок</label>
                    <select id="<?=htmlspecialcharsbx($timelineId)?>" name="lead_timeline" class="<?=htmlspecialcharsbx($selectControlClass)?>">
                        <option value="">Обсуждается</option>
                        <option value="asap">Нужен быстрый старт</option>
                        <option value="1-2-months">1-2 месяца</option>
                        <option value="3-6-months">3-6 месяцев</option>
                        <option value="6-plus-months">Дольше 6 месяцев</option>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="<?=htmlspecialcharsbx($consentClass)?>">
        <input type="checkbox" id="<?=htmlspecialcharsbx($agreementId)?>" data-tacticum-consent required
               class="<?=htmlspecialcharsbx($checkboxClass)?>">
        <label for="<?=htmlspecialcharsbx($agreementId)?>" class="<?=htmlspecialcharsbx($consentLabelClass)?>">
            Я согласен на обработку персональных данных и принимаю условия
            <a href="/policies/" target="_blank" rel="noopener" class="<?=htmlspecialcharsbx($consentLinkClass)?>">политики конфиденциальности</a>
        </label>
    </div>

    <button type="submit" class="<?=htmlspecialcharsbx($submitClass)?>">
        <?=htmlspecialcharsbx($arResult['BUTTON_TEXT'])?>
    </button>
</form>
