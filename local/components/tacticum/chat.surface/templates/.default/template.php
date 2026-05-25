<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$variant = (string)($arResult['VARIANT'] ?? 'light');
$surface = (string)($arResult['SURFACE'] ?? ($variant === 'hero' ? 'hero' : 'calculator'));
$title = (string)($arResult['TITLE'] ?? '');
$intro = (string)($arResult['INTRO'] ?? '');
$placeholder = (string)($arResult['PLACEHOLDER'] ?? '');
$rootClass = (string)($arResult['ROOT_CLASS'] ?? '');
$quickReplies = is_array($arResult['QUICK_REPLIES'] ?? null) ? $arResult['QUICK_REPLIES'] : [];
$introItems = is_array($arResult['INTRO_ITEMS'] ?? null) ? $arResult['INTRO_ITEMS'] : [];
$introOutro = (string)($arResult['INTRO_OUTRO'] ?? '');
$initialUserMessage = (string)($arResult['INITIAL_USER_MESSAGE'] ?? '');

if ($variant === 'hero'):?>
    <div class="<?=htmlspecialcharsbx($rootClass)?>" id="main_chat" data-tacticum-chat="hero" data-chat-surface="<?=htmlspecialcharsbx($surface)?>">
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-lg">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <div class="text-white/70 text-sm"><?=htmlspecialcharsbx($title)?></div>
            </div>
            <div class="flex flex-col h-[400px]" data-hero-chat-shell>
                <div class="flex-1 min-h-0 overflow-y-auto mb-4 space-y-4" data-hero-chat-messages data-chat-messages>
                    <?if ($initialUserMessage !== ''):?>
                        <div class="bg-white/10 rounded-lg p-3 text-white">
                            <p class="text-sm text-white/70 mb-1">Пользователь:</p>
                            <p><?=htmlspecialcharsbx($initialUserMessage)?></p>
                        </div>
                    <?endif;?>
                    <?if ($intro !== '' || $introItems !== [] || $introOutro !== ''):?>
                        <div class="bg-primary/20 rounded-lg p-3 text-white">
                            <p class="text-sm text-white/70 mb-1">AI-ассистент:</p>
                            <?if ($intro !== ''):?>
                                <p><?=htmlspecialcharsbx($intro)?></p>
                            <?endif;?>
                            <?if ($introItems !== []):?>
                                <ul class="list-disc pl-5 mt-2 space-y-1">
                                    <?foreach ($introItems as $item):?>
                                        <li><?=htmlspecialcharsbx((string)$item)?></li>
                                    <?endforeach;?>
                                </ul>
                            <?endif;?>
                            <?if ($introOutro !== ''):?>
                                <p class="mt-2"><?=htmlspecialcharsbx($introOutro)?></p>
                            <?endif;?>
                        </div>
                    <?endif;?>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" placeholder="<?=htmlspecialcharsbx($placeholder)?>" data-chat-input class="flex-1 bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    <button type="button" id="aichat" data-chat-send aria-label="Отправить сообщение" class="bg-primary w-10 h-10 rounded-full flex items-center justify-center text-white">
                        <i class="ri-send-plane-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?else:?>
    <div class="<?=htmlspecialcharsbx($rootClass)?>" data-tacticum-chat="light" data-chat-surface="<?=htmlspecialcharsbx($surface)?>">
        <div class="bg-white p-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <div class="text-gray-500 text-sm"><?=htmlspecialcharsbx($title)?></div>
            </div>
        </div>

        <div class="p-6 space-y-6" data-chat-messages>
            <?if ($intro !== ''):?>
                <div class="bg-primary/10 rounded-lg p-4">
                    <p class="text-gray-700">
                        <?=htmlspecialcharsbx($intro)?>
                    </p>
                </div>
            <?endif;?>
        </div>

        <div class="bg-white p-4 border-t border-gray-200">
            <div class="flex items-center gap-2">
                <input type="text" placeholder="<?=htmlspecialcharsbx($placeholder)?>" data-chat-input class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/50">
                <button type="button" data-chat-send aria-label="Отправить сообщение" class="bg-primary w-10 h-10 rounded-full flex items-center justify-center text-white">
                    <i class="ri-send-plane-fill"></i>
                </button>
            </div>
            <?if ($quickReplies !== []):?>
                <div class="mt-3 flex flex-wrap gap-2">
                    <?foreach ($quickReplies as $reply):?>
                        <button type="button" data-chat-quick-reply data-message="<?=htmlspecialcharsbx((string)$reply)?>" class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">
                            <?=htmlspecialcharsbx((string)$reply)?>
                        </button>
                    <?endforeach;?>
                </div>
            <?endif;?>
        </div>
    </div>
<?endif;?>
