<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<section class="pt-20 py-12 md:py-32 bg-white">
    <div class="container mx-auto px-4">
        <main class="container mx-auto px-4 py-10 max-w-4xl text-gray-900" style="text-align: justify">
            <?if($arResult["NAME"]):?>
                <h1 class="text-3xl font-bold mb-6 text-secondary">
                    <?=$arResult["NAME"]?>
                </h1>
            <?endif;?>

            <?if($arResult["DETAIL_TEXT"]):?>
                <article class="prose prose-lg max-w-none policy-content">
                    <?=$arResult["DETAIL_TEXT"]?>
                </article>
            <?endif;?>
        </main>
    </div>
</section>

<style>
    /* Опционально: стилизация длинных списков и разделов политики для читабельности */
    .policy-content {
        font-family: inherit;
        line-height: 1.7;
    }
    .policy-content h2, .policy-content h3 {
        margin-top: 2em;
        margin-bottom: 1em;
        font-weight: bold;
    }
    .policy-content ul, .policy-content ol {
        margin-left: 1.5em;
        margin-bottom: 1em;
    }
    .policy-content li {
        margin-bottom: .4em;
    }
    .policy-content strong {
        font-weight: bold;
    }
</style>
