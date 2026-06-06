#!/usr/bin/env php
<?php

declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumPriceTeamPresetsFinalize
{
    private bool $apply;
    private bool $runChecks;
    private bool $runBrowserSmoke;
    private string $missingAutomation;
    private string $documentRoot;
    private array $errors = [];
    private array $notes = [];

    public function __construct(
        bool $apply,
        bool $runChecks,
        bool $runBrowserSmoke,
        string $missingAutomation,
        string $documentRoot
    ) {
        $this->apply = $apply;
        $this->runChecks = $runChecks;
        $this->runBrowserSmoke = $runBrowserSmoke;
        $this->missingAutomation = $missingAutomation;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): int
    {
        $this->loadBitrix();
        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is not available.');
        }

        $presetsIblockId = $this->findIblockId('tacticum_team_presets');
        $rolesIblockId = $this->findIblockId('tacticum_team_preset_roles');
        $ratesIblockId = function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id('rates') : 0;

        if ($presetsIblockId <= 0) {
            $this->errors[] = 'tacticum_team_presets iblock not found.';
        }
        if ($rolesIblockId <= 0) {
            $this->errors[] = 'tacticum_team_preset_roles iblock not found.';
        }
        if ($ratesIblockId <= 0) {
            $this->errors[] = 'rates iblock config is missing.';
        }
        if ($this->errors !== []) {
            $this->printSummary();
            return 1;
        }

        $this->writePriceConfig($presetsIblockId, $rolesIblockId, 'fallback', true);
        $dataReady = $this->ensureAutomationRole($rolesIblockId, $ratesIblockId);
        if (!$dataReady) {
            $this->notes[] = 'Config remains in fallback mode because target data is not strict-ready.';
            $this->printSummary();
            return 1;
        }

        $this->writePriceConfig($presetsIblockId, $rolesIblockId, 'bitrix', false);
        if ($this->runChecks && $this->runShell('npm run price:team-presets:check:strict') !== 0) {
            $this->errors[] = 'Strict team presets check failed; rolling config back to fallback mode.';
            $this->writePriceConfig($presetsIblockId, $rolesIblockId, 'fallback', true);
            $this->printSummary();
            return 1;
        }
        if ($this->runChecks && $this->runShell('npm run config:runtime:check') !== 0) {
            $this->errors[] = 'Runtime config check failed after strict switch.';
        }
        if ($this->runBrowserSmoke && $this->runShell('npm run browser:smoke:price') !== 0) {
            $this->errors[] = 'Browser smoke failed or Chrome is unavailable on this server. Bitrix strict config was not rolled back automatically after browser smoke.';
        }

        $this->printSummary();
        return $this->errors === [] ? 0 : 1;
    }

    private function loadBitrix(): void
    {
        $prolog = $this->documentRoot . '/bitrix/modules/main/include/prolog_before.php';
        if (!is_file($prolog)) {
            throw new RuntimeException('Bitrix prolog_before.php not found under document root: ' . $this->documentRoot);
        }

        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot;
        define('NO_KEEP_STATISTIC', true);
        define('NOT_CHECK_PERMISSIONS', true);
        require_once $prolog;
    }

    private function findIblockId(string $code): int
    {
        $result = CIBlock::GetList(['ID' => 'ASC'], ['CODE' => $code, 'CHECK_PERMISSIONS' => 'N']);
        $iblock = $result->Fetch();

        return is_array($iblock) ? (int)$iblock['ID'] : 0;
    }

    private function writePriceConfig(int $presetsIblockId, int $rolesIblockId, string $source, bool $allowFallback): void
    {
        $configPath = $this->documentRoot . '/local/php_interface/include/tacticum_config.php';
        if (!is_file($configPath)) {
            throw new RuntimeException('Config file not found: ' . $configPath);
        }

        $config = include $configPath;
        if (!is_array($config)) {
            throw new RuntimeException('Config file must return an array: ' . $configPath);
        }

        $config['iblocks'] = is_array($config['iblocks'] ?? null) ? $config['iblocks'] : [];
        $config['iblocks']['team_presets'] = $presetsIblockId;
        $config['iblocks']['team_preset_roles'] = $rolesIblockId;
        $config['price'] = array_replace(is_array($config['price'] ?? null) ? $config['price'] : [], [
            'team_presets_source' => $source,
            'team_presets_cache_ttl' => 300,
            'allow_team_presets_fallback' => $allowFallback,
        ]);

        $this->notes[] = "Config target: team_presets=#{$presetsIblockId}, team_preset_roles=#{$rolesIblockId}, source={$source}, fallback=" . ($allowFallback ? 'true' : 'false');
        if (!$this->apply) {
            $this->notes[] = '[dry-run] Config file was not changed.';
            return;
        }

        $payload = "<?php\n\nreturn " . $this->exportArray($config) . ";\n";
        $tmpPath = $configPath . '.tmp.' . getmypid();
        if (file_put_contents($tmpPath, $payload, LOCK_EX) === false) {
            throw new RuntimeException('Failed to write temporary config file: ' . $tmpPath);
        }
        if (!rename($tmpPath, $configPath)) {
            @unlink($tmpPath);
            throw new RuntimeException('Failed to replace config file: ' . $configPath);
        }
    }

    private function ensureAutomationRole(int $rolesIblockId, int $ratesIblockId): bool
    {
        $role = $this->elementByCode($rolesIblockId, 'qa-burst-qa-automation');
        if ($role === null) {
            $this->notes[] = 'qa-burst-qa-automation role is absent; strict data is acceptable.';
            return true;
        }
        if (($role['ACTIVE'] ?? 'N') !== 'Y') {
            $this->notes[] = 'qa-burst-qa-automation role is already inactive; strict data is acceptable.';
            return true;
        }

        $properties = $this->elementProperties($rolesIblockId, (int)$role['ID']);
        $currentRateElementId = (int)($properties['RATE_ELEMENT']['VALUE'] ?? 0);
        if ($currentRateElementId > 0 && $this->isActiveRate($ratesIblockId, $currentRateElementId)) {
            $this->notes[] = "qa-burst-qa-automation already linked to RATE_ELEMENT #{$currentRateElementId}.";
            return true;
        }

        $keywords = $this->propertyValues($properties, 'FALLBACK_KEYWORDS');
        if ($keywords === []) {
            $keywords = ['автоматиз', 'automation', 'автотест'];
        }
        $matchedRateId = $this->matchRateElement($ratesIblockId, $keywords);
        if ($matchedRateId > 0) {
            $this->notes[] = "Matched qa-burst-qa-automation to RATE_ELEMENT #{$matchedRateId}.";
            if ($this->apply) {
                CIBlockElement::SetPropertyValuesEx((int)$role['ID'], $rolesIblockId, ['RATE_ELEMENT' => $matchedRateId]);
            } else {
                $this->notes[] = '[dry-run] RATE_ELEMENT was not changed.';
            }
            return true;
        }

        if ($this->missingAutomation === 'deactivate') {
            $this->notes[] = 'No active QA automation rate matched; deactivating qa-burst-qa-automation by explicit strategy.';
            if ($this->apply) {
                $element = new CIBlockElement();
                if (!$element->Update((int)$role['ID'], ['ACTIVE' => 'N'])) {
                    throw new RuntimeException('Failed to deactivate qa-burst-qa-automation: ' . (string)$element->LAST_ERROR);
                }
            } else {
                $this->notes[] = '[dry-run] Role was not deactivated.';
            }
            return true;
        }

        $this->errors[] = 'qa-burst-qa-automation has no RATE_ELEMENT and no active matching rate. Use --missing-automation=deactivate or create/link a rate manually.';
        return false;
    }

    private function elementByCode(int $iblockId, string $code): ?array
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, '=CODE' => $code, 'CHECK_PERMISSIONS' => 'N'],
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'ACTIVE']
        );
        $row = $result->Fetch();

        return is_array($row) ? $row : null;
    }

    private function elementProperties(int $iblockId, int $elementId): array
    {
        $properties = [];
        $result = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], []);
        while ($property = $result->Fetch()) {
            $code = trim((string)($property['CODE'] ?? ''));
            if ($code !== '') {
                $properties[$code] = $property;
            }
        }

        return $properties;
    }

    private function propertyValues(array $properties, string $code): array
    {
        $value = $properties[$code]['VALUE'] ?? [];
        if (!is_array($value)) {
            $value = [$value];
        }

        return array_values(array_filter(array_map(
            static fn(mixed $item): string => trim((string)$item),
            $value
        ), static fn(string $item): bool => $item !== ''));
    }

    private function isActiveRate(int $ratesIblockId, int $rateElementId): bool
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            ['IBLOCK_ID' => $ratesIblockId, 'ID' => $rateElementId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            ['nTopCount' => 1],
            ['ID']
        );

        return is_array($result->Fetch());
    }

    private function matchRateElement(int $ratesIblockId, array $keywords): int
    {
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            ['IBLOCK_ID' => $ratesIblockId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT']
        );
        while ($element = $result->GetNextElement()) {
            $fields = $element->GetFields();
            $haystack = $this->normalize((string)($fields['NAME'] ?? '') . ' ' . (string)($fields['CODE'] ?? ''));
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($haystack, $this->normalize($keyword))) {
                    return (int)($fields['ID'] ?? 0);
                }
            }
        }

        return 0;
    }

    private function normalize(string $value): string
    {
        return str_replace('ё', 'е', mb_strtolower(trim($value)));
    }

    private function runShell(string $command): int
    {
        $this->notes[] = 'Run: ' . $command;
        if (!$this->apply) {
            $this->notes[] = '[dry-run] Command was not executed.';
            return 0;
        }

        $fullCommand = 'cd ' . escapeshellarg($this->documentRoot) . ' && ' . $command;
        passthru($fullCommand, $exitCode);
        return (int)$exitCode;
    }

    private function exportArray(array $array, int $indent = 0): string
    {
        if ($array === []) {
            return '[]';
        }

        $pad = str_repeat('    ', $indent);
        $childPad = str_repeat('    ', $indent + 1);
        $isList = array_keys($array) === range(0, count($array) - 1);
        $lines = ['['];
        foreach ($array as $key => $value) {
            $line = $childPad;
            if (!$isList) {
                $line .= var_export($key, true) . ' => ';
            }
            $line .= is_array($value) ? $this->exportArray($value, $indent + 1) : var_export($value, true);
            $lines[] = $line . ',';
        }
        $lines[] = $pad . ']';

        return implode("\n", $lines);
    }

    private function printSummary(): void
    {
        echo 'Price Team Presets Finalize' . PHP_EOL;
        echo 'Apply: ' . ($this->apply ? 'yes' : 'no') . PHP_EOL;
        foreach ($this->notes as $note) {
            echo '- ' . $note . PHP_EOL;
        }
        foreach ($this->errors as $error) {
            echo 'Error: ' . $error . PHP_EOL;
        }
    }
}

function tacticum_price_team_presets_finalize_usage(): string
{
    return <<<TEXT
Usage:
  php tools/price-team-presets-finalize.php [--apply] [--run-checks] [--run-browser-smoke] [--missing-automation=deactivate|require-rate] [--document-root=/path/to/site]

Finalizes production team presets rollout:
- writes team_presets/team_preset_roles IDs to tacticum_config.php;
- keeps fallback mode while target data is fixed;
- links qa-burst-qa-automation to an active matching rates row if possible;
- with --missing-automation=deactivate, deactivates qa-burst-qa-automation when no rate exists;
- switches price.team_presets_source=bitrix and disables fallback;
- optionally runs strict config checks and focused browser smoke.

Dry-run by default.

TEXT;
}

$options = [
    'apply' => false,
    'run_checks' => false,
    'run_browser_smoke' => false,
    'missing_automation' => 'require-rate',
    'document_root' => getenv('DOCUMENT_ROOT') ?: dirname(__DIR__),
    'help' => false,
];

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        $options['help'] = true;
        continue;
    }
    if ($argument === '--apply') {
        $options['apply'] = true;
        continue;
    }
    if ($argument === '--run-checks') {
        $options['run_checks'] = true;
        continue;
    }
    if ($argument === '--run-browser-smoke') {
        $options['run_browser_smoke'] = true;
        continue;
    }
    if (str_starts_with($argument, '--missing-automation=')) {
        $options['missing_automation'] = substr($argument, strlen('--missing-automation='));
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $options['document_root'] = substr($argument, strlen('--document-root='));
        continue;
    }
    fwrite(STDERR, 'Unknown argument: ' . $argument . PHP_EOL);
    fwrite(STDERR, tacticum_price_team_presets_finalize_usage());
    exit(2);
}

if ($options['help']) {
    echo tacticum_price_team_presets_finalize_usage();
    exit(0);
}
if (!in_array($options['missing_automation'], ['deactivate', 'require-rate'], true)) {
    fwrite(STDERR, '--missing-automation must be deactivate or require-rate.' . PHP_EOL);
    exit(2);
}

$documentRoot = realpath((string)$options['document_root']);
if ($documentRoot === false) {
    fwrite(STDERR, 'Document root does not exist: ' . (string)$options['document_root'] . PHP_EOL);
    exit(2);
}

try {
    exit((new TacticumPriceTeamPresetsFinalize(
        (bool)$options['apply'],
        (bool)$options['run_checks'],
        (bool)$options['run_browser_smoke'],
        (string)$options['missing_automation'],
        $documentRoot
    ))->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
