<?php

declare(strict_types=1);

namespace Tacticum\Divatech;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use RuntimeException;

final class LeadService
{
    public static function save(array $data): void
    {
        if (!Settings::formsEnabled() || !Loader::includeModule('iblock')) {
            throw new RuntimeException('Lead service unavailable.');
        }
        $iblockId = tacticum_rest_get_iblock_id('divatech_leads');
        // Read configured storage metadata as a service; visitors have no read permission.
        $block = \Bitrix\Iblock\IblockTable::getById($iblockId)->fetch();
        $sites = [];
        $siteResult = \CIBlock::GetSite($iblockId);
        while ($site = $siteResult->Fetch()) {
            $sites[] = $site['LID'];
        }
        if (!$block || $block['CODE'] !== 'divatech_leads' || $sites !== ['s2']) {
            throw new RuntimeException('Lead storage mismatch.');
        }
        foreach (\CIBlock::GetGroupPermissions($iblockId) as $group => $permission) {
            if ((int)$group !== 1 && $permission !== 'D') {
                throw new RuntimeException('Lead storage must be private.');
            }
        }
        $connection = Application::getConnection();
        // MySQL advisory lock serializes retries, including concurrent requests after a timeout.
        $lock = 'divatech-lead-' . substr(hash('sha256', $data['request_id']), 0, 40);
        if ((int)$connection->queryScalar("SELECT GET_LOCK('$lock', 3)") !== 1) {
            throw new RuntimeException('Lead storage busy.');
        }
        try {
            $existing = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, '=XML_ID' => $data['request_id'], 'CHECK_PERMISSIONS' => 'N'], false, false, ['ID'])->Fetch();
            if ($existing) {
                return;
            }
            $element = new \CIBlockElement();
            $properties = [];
            foreach ($data as $key => $value) {
                if ($key !== 'request_id') {
                    $properties[strtoupper($key)] = $value;
                }
            }
            $properties['CONSENT'] = 'Y';
            $properties['PRIVACY_URL'] = (string)Settings::all()['privacy_url'];
            $properties['CONSENT_TEXT'] = (string)Settings::all()['consent_text'];
            $id = $element->Add(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'N', 'NAME' => 'Divatech request', 'XML_ID' => $data['request_id'], 'PROPERTY_VALUES' => $properties]);
            if (!$id) {
                // Bitrix errors can include submitted data; never echo/log LAST_ERROR.
                throw new RuntimeException('Could not save request.');
            }
            $settings = Settings::all();
            // Notification contains no submitted PII; recipients view data in the private admin UI.
            try {
                $result = Event::send(['EVENT_NAME' => 'DIVATECH_LEAD', 'LID' => 's2', 'C_FIELDS' => [
                    'EMAIL_TO' => (string)$settings['recipient'], 'EMAIL_FROM' => (string)$settings['sender'], 'LEAD_ID' => (string)$id,
                ]]);
                $notification = $result->isSuccess() ? 'queued' : 'failed';
            } catch (\Throwable) {
                $notification = 'failed';
            }
            \CIBlockElement::SetPropertyValuesEx((int)$id, $iblockId, ['NOTIFICATION' => $notification]);
        } finally {
            $connection->query("SELECT RELEASE_LOCK('$lock')");
        }
    }
}
