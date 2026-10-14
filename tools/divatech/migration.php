<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

final class TacticumDivatechMigration
{
    public function __construct(private string $root, private string $siteRoot)
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            throw new RuntimeException('Iblock module missing.');
        }
    }

    public function snapshot(): array
    {
        $db = \Bitrix\Main\Application::getConnection();
        // Only configuration metadata, never license keys, users, credentials or lead payloads.
        $queries = [
            'sites' => "SELECT LID, SORT, DEF, ACTIVE, NAME, DIR, LANGUAGE_ID, DOC_ROOT, SERVER_NAME, CULTURE_ID FROM b_lang ORDER BY LID",
            'domains' => 'SELECT LID, DOMAIN FROM b_lang_domain ORDER BY LID, DOMAIN',
            'templates' => 'SELECT SITE_ID, SORT, `CONDITION`, TEMPLATE FROM b_site_template ORDER BY SITE_ID, SORT, TEMPLATE',
            'languages' => "SELECT LID, CULTURE_ID FROM b_language WHERE LID='en'",
            'iblocks' => "SELECT ID, CODE, IBLOCK_TYPE_ID FROM b_iblock WHERE CODE='divatech_leads' OR IBLOCK_TYPE_ID='divatech' ORDER BY ID",
            'types' => "SELECT ID FROM b_iblock_type WHERE ID='divatech'",
            'events' => "SELECT ID, EVENT_NAME, LID FROM b_event_type WHERE EVENT_NAME='DIVATECH_LEAD' ORDER BY ID",
            'mail' => "SELECT ID, EVENT_NAME, ACTIVE FROM b_event_message WHERE EVENT_NAME='DIVATECH_LEAD' ORDER BY ID",
            'mail_content' => "SELECT ID, SHA2(CONCAT_WS('|', EMAIL_FROM, EMAIL_TO, SUBJECT, MESSAGE, BODY_TYPE, COALESCE(BCC,''), COALESCE(CC,'')),256) AS content_sha256 FROM b_event_message WHERE EVENT_NAME='DIVATECH_LEAD' ORDER BY ID",
            'mail_sites' => "SELECT s.EVENT_MESSAGE_ID, s.SITE_ID FROM b_event_message_site s JOIN b_event_message m ON m.ID=s.EVENT_MESSAGE_ID WHERE m.EVENT_NAME='DIVATECH_LEAD' ORDER BY s.EVENT_MESSAGE_ID,s.SITE_ID",
            'properties' => "SELECT p.ID,p.IBLOCK_ID,p.CODE,p.PROPERTY_TYPE,p.MULTIPLE,p.ACTIVE FROM b_iblock_property p JOIN b_iblock b ON b.ID=p.IBLOCK_ID WHERE b.CODE='divatech_leads' ORDER BY p.CODE",
            'permissions' => "SELECT g.IBLOCK_ID,g.GROUP_ID,g.PERMISSION FROM b_iblock_group g JOIN b_iblock b ON b.ID=g.IBLOCK_ID WHERE b.CODE='divatech_leads' ORDER BY g.GROUP_ID",
            'iblock_sites' => "SELECT s.IBLOCK_ID,s.SITE_ID FROM b_iblock_site s JOIN b_iblock b ON b.ID=s.IBLOCK_ID WHERE b.CODE='divatech_leads' ORDER BY s.SITE_ID",
        ];
        $state = [];
        foreach ($queries as $key => $query) { $state[$key] = $db->query($query)->fetchAll(); }
        return $state;
    }

    public function hash(array $state): string
    {
        return hash('sha256', json_encode($state, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function assertActivationJournal(array $journal, array $state): void
    {
        if (($journal['status'] ?? '') !== 'applied'
            || ($journal['root'] ?? '') !== $this->root
            || ($journal['site_root'] ?? '') !== $this->siteRoot
            || !is_array($journal['before'] ?? null)
            || !is_array($journal['after'] ?? null)
            || !hash_equals($this->hash($journal['before']), (string)($journal['before_sha256'] ?? ''))
            || !hash_equals($this->hash($journal['after']), (string)($journal['after_sha256'] ?? ''))
            || !hash_equals($this->hash($state), (string)($journal['after_sha256'] ?? ''))) {
            throw new RuntimeException('Applied journal does not match roots/current state; manual recovery required.');
        }
    }

    public function apply(): void
    {
        $before = $this->snapshot();
        $sites = array_column($before['sites'], null, 'LID');
        if (isset($sites['s2']) || !isset($sites['s1']) || $sites['s1']['DEF'] !== 'Y'
            || $before['iblocks'] || $before['types'] || $before['events'] || $before['mail'] || count($before['languages']) !== 1) {
            throw new RuntimeException('Pre-existing objects conflict with new-site migration.');
        }
        if (!is_dir($this->root . '/local/templates/divatech')) { throw new RuntimeException('Template missing.'); }
        $db = \Bitrix\Main\Application::getConnection();
        $db->startTransaction();
        try {
            $site = new CSite();
            if (!$site->Update('s1', ['DOC_ROOT' => $this->root])) { throw new RuntimeException('s1 root update failed.'); }
            if (!$site->Add(['LID' => 's2', 'NAME' => 'DIVA Technologies', 'SITE_NAME' => 'DIVA Technologies', 'SORT' => 20,
                'ACTIVE' => 'N', 'DEF' => 'N', 'DIR' => '/', 'DOC_ROOT' => $this->siteRoot, 'LANGUAGE_ID' => 'en',
                'CULTURE_ID' => (int)$before['languages'][0]['CULTURE_ID'], 'SERVER_NAME' => 'divatech.space',
                'DOMAINS' => 'divatech.space', 'TEMPLATE' => [['SORT' => 150, 'CONDITION' => '', 'TEMPLATE' => 'divatech']]])) {
                throw new RuntimeException('s2 creation failed.');
            }
            $type = new CIBlockType();
            if (!$type->Add(['ID' => 'divatech', 'SECTIONS' => 'N', 'IN_RSS' => 'N', 'SORT' => 500,
                'LANG' => ['en' => ['NAME' => 'Divatech requests', 'ELEMENT_NAME' => 'Request'], 'ru' => ['NAME' => 'Заявки Divatech', 'ELEMENT_NAME' => 'Заявка']]])) {
                throw new RuntimeException('Type creation failed.');
            }
            $iblock = new CIBlock();
            $id = $iblock->Add(['IBLOCK_TYPE_ID' => 'divatech', 'CODE' => 'divatech_leads', 'NAME' => 'Divatech requests', 'ACTIVE' => 'Y',
                'SITE_ID' => ['s2'], 'VERSION' => 1, 'WORKFLOW' => 'N', 'INDEX_ELEMENT' => 'N', 'INDEX_SECTION' => 'N',
                'RIGHTS_MODE' => 'S', 'GROUP_ID' => [1 => 'X', 2 => 'D']]);
            if (!$id) { throw new RuntimeException('Storage creation failed.'); }
            foreach (['KIND', 'EMAIL', 'NAME', 'PHONE', 'MESSAGE', 'CONSENT', 'PRIVACY_URL', 'CONSENT_TEXT', 'NOTIFICATION'] as $code) {
                $property = new CIBlockProperty();
                if (!$property->Add(['IBLOCK_ID' => $id, 'CODE' => $code, 'NAME' => $code, 'PROPERTY_TYPE' => 'S', 'MULTIPLE' => 'N', 'ACTIVE' => 'Y'])) {
                    throw new RuntimeException('Property creation failed.');
                }
            }
            $event = new CEventType();
            if (!$event->Add(['LID' => 'en', 'EVENT_NAME' => 'DIVATECH_LEAD', 'NAME' => 'Divatech request received', 'DESCRIPTION' => '#LEAD_ID# — private request ID'])) {
                throw new RuntimeException('Mail event creation failed.');
            }
            $mail = new CEventMessage();
            if (!$mail->Add(['ACTIVE' => 'Y', 'EVENT_NAME' => 'DIVATECH_LEAD', 'LID' => ['s2'], 'EMAIL_FROM' => '#EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL_TO#',
                'SUBJECT' => 'Divatech request #LEAD_ID#', 'BODY_TYPE' => 'text', 'MESSAGE' => 'A new Divatech request #LEAD_ID# is available in the private Bitrix administration area.'])) {
                throw new RuntimeException('Mail template creation failed.');
            }
            $this->verify();
            $db->commitTransaction();
        } catch (Throwable $error) {
            $db->rollbackTransaction();
            \Bitrix\Main\SiteTable::cleanCache();
            throw $error;
        }
    }

    public function verify(): void
    {
        $state = $this->snapshot();
        $sites = array_column($state['sites'], null, 'LID');
        $s2 = $sites['s2'] ?? [];
        if (($sites['s1']['DEF'] ?? '') !== 'Y' || ($sites['s1']['DOC_ROOT'] ?? '') !== $this->root
            || ($s2['DOC_ROOT'] ?? '') !== $this->siteRoot || ($s2['SERVER_NAME'] ?? '') !== 'divatech.space'
            || ($s2['DEF'] ?? '') !== 'N' || ($s2['LANGUAGE_ID'] ?? '') !== 'en' || ($s2['DIR'] ?? '') !== '/'
            || count($state['iblocks']) !== 1 || count($state['mail']) !== 1 || count($state['events']) !== 1) {
            throw new RuntimeException('Site/storage verification failed.');
        }
        $templates = array_values(array_filter($state['templates'], static fn($row) => $row['SITE_ID'] === 's2'));
        $domains = array_values(array_filter($state['domains'], static fn($row) => $row['LID'] === 's2'));
        if (count($templates) !== 1 || $templates[0]['TEMPLATE'] !== 'divatech' || $templates[0]['CONDITION'] !== ''
            || count($domains) !== 1 || $domains[0]['DOMAIN'] !== 'divatech.space') {
            throw new RuntimeException('Site template/domain verification failed.');
        }
        $id = (int)$state['iblocks'][0]['ID'];
        $codes = array_column($state['properties'], 'CODE');
        $required = ['KIND', 'EMAIL', 'NAME', 'PHONE', 'MESSAGE', 'CONSENT', 'PRIVACY_URL', 'CONSENT_TEXT', 'NOTIFICATION'];
        sort($required);
        sort($codes);
        if ($codes !== $required || count($state['mail_sites']) !== 1 || $state['mail_sites'][0]['SITE_ID'] !== 's2') {
            throw new RuntimeException('Property or mail binding mismatch.');
        }
        $groups = CIBlock::GetGroupPermissions($id);
        foreach ($groups as $group => $permission) {
            if ((int)$group !== 1 && $permission !== 'D') { throw new RuntimeException('Storage is not private.'); }
        }
        $sites = [];
        $result = CIBlock::GetSite($id);
        while ($site = $result->Fetch()) { $sites[] = $site['LID']; }
        if ($sites !== ['s2']) { throw new RuntimeException('Storage site binding mismatch.'); }
    }

    public function activate(): void
    {
        $site = new CSite();
        if (!$site->Update('s2', ['ACTIVE' => 'Y'])) { throw new RuntimeException('Activation failed.'); }
    }

    public function rollback(array $journal): void
    {
        $current = $this->snapshot();
        $expected = $journal['after'];
        // Activation is an expected later phase; all other drift blocks rollback.
        foreach ($current['sites'] as &$site) { if ($site['LID'] === 's2') { $site['ACTIVE'] = 'N'; } }
        unset($site);
        if ($this->hash($current) !== $this->hash($expected)) { throw new RuntimeException('State drift blocks rollback.'); }
        $id = (int)$current['iblocks'][0]['ID'];
        if (CIBlockElement::GetList([], ['IBLOCK_ID' => $id, 'CHECK_PERMISSIONS' => 'N'], [], false, ['ID']) > 0) {
            throw new RuntimeException('Requests exist: deactivate site and preserve storage; destructive rollback refused.');
        }
        $beforeS1 = array_column($journal['before']['sites'], null, 'LID')['s1'];
        $db = \Bitrix\Main\Application::getConnection();
        $db->startTransaction();
        try {
            foreach ($current['mail'] as $mail) { if (!CEventMessage::Delete((int)$mail['ID'])) { throw new RuntimeException('Mail rollback failed.'); } }
            CEventType::Delete('DIVATECH_LEAD');
            if (!CIBlock::Delete($id) || !CIBlockType::Delete('divatech') || !CSite::Delete('s2')) { throw new RuntimeException('Object rollback failed.'); }
            // Legacy CSite::Update ignores null values; D7 restores an originally NULL root exactly.
            $restore = \Bitrix\Main\SiteTable::update('s1', ['DOC_ROOT' => $beforeS1['DOC_ROOT']]);
            if (!$restore->isSuccess()) { throw new RuntimeException('s1 restore failed.'); }
            \Bitrix\Main\SiteTable::cleanCache();
            if ($this->hash($this->snapshot()) !== $journal['before_sha256']) { throw new RuntimeException('Restored metadata mismatch.'); }
            $db->commitTransaction();
        } catch (Throwable $error) {
            $db->rollbackTransaction();
            \Bitrix\Main\SiteTable::cleanCache();
            throw $error;
        }
    }
}
