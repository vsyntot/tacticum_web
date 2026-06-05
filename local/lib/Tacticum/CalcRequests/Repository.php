<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

use Bitrix\Main\Loader;

final class Repository
{
    public static function list(int $iblockId): array
    {
        if (!Loader::includeModule('iblock')) {
            return Response::error('iblock_missing', 'Модуль инфоблоков не установлен.');
        }

        $res = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            ['nTopCount' => 10],
            ['ID', 'NAME', 'PROPERTY_*']
        );

        $items = [];
        while ($el = $res->GetNextElement()) {
            $items[] = [
                'FIELDS' => $el->GetFields(),
                'PROPERTIES' => $el->GetProperties(),
            ];
        }

        return $items;
    }

    public static function add(array $params, int $iblockId): array
    {
        if (!Loader::includeModule('iblock')) {
            return Response::error('iblock_missing', 'Модуль инфоблоков не установлен.');
        }

        $element = new \CIBlockElement();
        $name = 'resp_' . uniqid();
        $code = CodeGenerator::offerCode($params, $iblockId);
        $id = (int)$element->Add([
            'CODE' => $code,
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => PropertyMapper::properties($params),
        ]);

        if ($id > 0) {
            self::clearOfferCache($iblockId);

            return Response::success([
                'status' => 'ok',
                'name' => $name,
                'id' => $id,
                'link' => function_exists('tacticum_offer_detail_url') ? tacticum_offer_detail_url($code) : '/offer/' . $code . '/',
            ]);
        }

        $message = $element->LAST_ERROR;
        if (function_exists('tacticum_rest_mask_string')) {
            $message = tacticum_rest_mask_string($message);
        }

        return Response::error('add_failed', 'Не удалось сохранить элемент.', ['details' => $message]);
    }

    private static function clearOfferCache(int $iblockId): void
    {
        if (function_exists('tacticum_offer_catalog_clear_cache')) {
            tacticum_offer_catalog_clear_cache($iblockId);
        }
    }
}
