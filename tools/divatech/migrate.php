<?php

declare(strict_types=1);

// CLI only. The release operator must hold the external deployment lock and exact approval.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
register_shutdown_function(static function (): void {
    $last = error_get_last();
    if ($last && in_array($last['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        fwrite(STDERR, "Fatal migration/bootstrap error; state is unknown. Inspect the private journal.\n");
        exit(1);
    }
});
$options = getopt('', ['root:', 'site-root:', 'mode:', 'expect:', 'journal:']);
$root = realpath((string)($options['root'] ?? ''));
$siteRoot = realpath((string)($options['site-root'] ?? ''));
$mode = (string)($options['mode'] ?? 'dry-run');
$journalPath = (string)($options['journal'] ?? '');
if (!$root || !$siteRoot || !is_file($root . '/bitrix/modules/main/include/prolog_before.php')
    || !in_array($mode, ['dry-run', 'apply', 'activate', 'verify', 'rollback'], true)) {
    fwrite(STDERR, "Use --root=... --site-root=... --mode=dry-run|apply|activate|verify|rollback [--expect=state_sha256 --journal=/private/path.json]\n");
    exit(2);
}
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['HTTP_HOST'] = 'tacticum.ru';
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
require $root . '/bitrix/modules/main/include/prolog_before.php';
require_once __DIR__ . '/migration.php';

try {
    $migration = new TacticumDivatechMigration($root, $siteRoot);
    $before = $migration->snapshot();
    $hash = $migration->hash($before);
    if (in_array($mode, ['dry-run', 'verify'], true)) {
        if ($mode === 'verify') {
            $migration->verify();
        }
        echo json_encode(['mode' => $mode, 'state_sha256' => $hash, 'state' => $before], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        exit;
    }
    if (!hash_equals($hash, (string)($options['expect'] ?? ''))) {
        throw new RuntimeException('State changed or exact expected state hash missing.');
    }
    $parent = realpath(dirname($journalPath));
    if (!$parent || $journalPath === '' || str_starts_with($parent . '/', $root . '/') || str_starts_with($parent . '/', $siteRoot . '/')) {
        throw new RuntimeException('Journal must be in an existing private directory outside both web roots.');
    }
    if ($mode === 'apply') {
        if (file_exists($journalPath)) {
            $prior = json_decode((string)file_get_contents($journalPath), true, 512, JSON_THROW_ON_ERROR);
            if (($prior['status'] ?? '') !== 'applied' || ($prior['root'] ?? '') !== $root || ($prior['site_root'] ?? '') !== $siteRoot) {
                throw new RuntimeException('Existing journal belongs to a different/incomplete operation.');
            }
            $migration->verify();
            echo "Already applied; state verified.\n";
            exit;
        }
        $journal = ['version' => 1, 'root' => $root, 'site_root' => $siteRoot, 'before' => $before, 'before_sha256' => $hash, 'status' => 'prepared'];
        $handle = fopen($journalPath, 'x');
        if (!$handle) { throw new RuntimeException('Could not create exclusive journal.'); }
        chmod($journalPath, 0600);
        fwrite($handle, json_encode($journal, JSON_PRETTY_PRINT));
        fclose($handle);
        $migration->apply();
        $journal['status'] = 'applied';
        $journal['after'] = $migration->snapshot();
        $journal['after_sha256'] = $migration->hash($journal['after']);
        if (file_put_contents($journalPath, json_encode($journal, JSON_PRETTY_PRINT), LOCK_EX) === false) {
            throw new RuntimeException('Applied, but final journal write failed: inspect before continuing.');
        }
    } elseif ($mode === 'activate') {
        if (!is_file($journalPath) || is_link($journalPath)) {
            throw new RuntimeException('Activation requires an existing regular applied journal.');
        }
        $journal = json_decode((string)file_get_contents($journalPath), true, 512, JSON_THROW_ON_ERROR);
        $migration->assertActivationJournal($journal, $before);
        $migration->verify();
        $migration->activate();
    } else {
        $journal = json_decode((string)file_get_contents($journalPath), true, 512, JSON_THROW_ON_ERROR);
        if (($journal['root'] ?? '') !== $root || ($journal['site_root'] ?? '') !== $siteRoot || !isset($journal['after'])) {
            throw new RuntimeException('Journal mismatch/incomplete; manual recovery required.');
        }
        $migration->rollback($journal);
    }
    echo json_encode(['mode' => $mode, 'state_sha256' => $migration->hash($migration->snapshot()), 'state' => $migration->snapshot()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Throwable $error) {
    // Do not print DB exceptions: queries and credentials may contain sensitive data.
    fwrite(STDERR, "Divatech migration stopped (" . get_class($error) . "). Review private state and journal; no automatic retry.\n");
    exit(1);
}
