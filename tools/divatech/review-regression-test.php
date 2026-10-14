<?php

declare(strict_types=1);

namespace Bitrix\Main {
    final class Loader
    {
        public static function includeModule(string $name): bool { return $name === 'iblock'; }
    }
}

namespace Tacticum\Rest {
    final class Response
    {
        public static function error(int $status, string $code, string $message): never
        {
            throw new \RuntimeException($code, $status);
        }
    }
    final class Config
    {
        public static function section(string $name): array { return ['allowed_origins' => ['tacticum.ru']]; }
    }
}

namespace {
    require __DIR__ . '/migration.php';
    require __DIR__ . '/../../local/lib/Tacticum/Divatech/Origin.php';
    require __DIR__ . '/../../local/lib/Tacticum/Rest/RequestSite.php';
    require __DIR__ . '/../../local/lib/Tacticum/Rest/Security.php';

    $checks = 0;
    $check = static function (bool $valid, string $label) use (&$checks): void {
        $checks++;
        if (!$valid) { throw new RuntimeException($label); }
    };
    $migration = new TacticumDivatechMigration('/shared', '/site');
    $before = ['sites' => [['LID' => 's1']]];
    $after = ['sites' => [['LID' => 's1'], ['LID' => 's2', 'ACTIVE' => 'N']]];
    $journal = ['status' => 'applied', 'root' => '/shared', 'site_root' => '/site',
        'before' => $before, 'after' => $after,
        'before_sha256' => $migration->hash($before), 'after_sha256' => $migration->hash($after)];
    $migration->assertActivationJournal($journal, $after);
    $check(true, 'Valid applied journal rejected');
    foreach ([[], array_replace($journal, ['status' => 'prepared']), array_replace($journal, ['root' => '/other']),
        array_replace($journal, ['site_root' => '/other']), array_replace($journal, ['before_sha256' => 'bad']),
        array_replace($journal, ['after_sha256' => 'bad']), array_replace($journal, ['after' => null])] as $bad) {
        try { $migration->assertActivationJournal($bad, $after); $rejected = false; }
        catch (RuntimeException) { $rejected = true; }
        $check($rejected, 'Untrusted/incomplete journal accepted');
    }
    try { $migration->assertActivationJournal($journal, $before); $rejected = false; }
    catch (RuntimeException) { $rejected = true; }
    $check($rejected, 'Drifted activation state accepted');

    define('SITE_ID', in_array('--s1', $argv, true) ? 's1' : 's2');
    $_SERVER = ['HTTP_HOST' => 'divatech.space', 'HTTP_ORIGIN' => 'https://divatech.space'];
    foreach ([null, 'divatech', 'unknown'] as $context) {
        $expected = SITE_ID === 's2' && $context === 'divatech';
        try { \Tacticum\Rest\Security::validateOrigin($context); $allowed = true; }
        catch (RuntimeException $error) { $allowed = false; $check($error->getCode() === 403, 'Wrong denial status'); }
        $check($allowed === $expected, 'Site policy dispatch differs');
    }
    $_SERVER = ['HTTP_HOST' => 'tacticum.ru', 'HTTP_ORIGIN' => 'https://tacticum.ru'];
    try { \Tacticum\Rest\Security::validateOrigin(); $allowed = true; }
    catch (RuntimeException) { $allowed = false; }
    $check($allowed === (SITE_ID === 's1'), 'Direct Security caller bypass/compatibility failure');
    echo 'Divatech review regressions (' . SITE_ID . "): $checks checks passed.\n";
}

namespace {
    // Exercise real public entries with a minimal prolog/template boundary.
    $fixture = sys_get_temp_dir() . '/divatech-entry-' . bin2hex(random_bytes(6));
    mkdir($fixture . '/bitrix/modules/main/include', 0700, true);
    $events = [];
    $APPLICATION = new class {
        public function SetTitle(string $value): void { $GLOBALS['events'][] = 'title'; }
        public function SetPageProperty(string $name, string $value): void { $GLOBALS['events'][] = $name; }
        public function IncludeComponent(...$arguments): void { $GLOBALS['events'][] = 'component'; }
    };
    final class CHTTP {
        public static function SetStatus(string $status): void { $GLOBALS['events'][] = $status; }
    }
    $paths = [
        '/bitrix/modules/main/include/prolog_before.php' => '<?php $GLOBALS["events"][] = "before";',
        '/bitrix/modules/main/include/prolog_after.php' => '<?php $GLOBALS["events"][] = "after";',
        '/bitrix/footer.php' => '<?php $GLOBALS["events"][] = "footer";',
    ];
    try {
        foreach ($paths as $path => $code) { file_put_contents($fixture . $path, $code); }
        $_SERVER['DOCUMENT_ROOT'] = $fixture;
        // One page per process avoids redefining its Bitrix constants.
        if (SITE_ID === 's1') {
            ob_start();
            require __DIR__ . '/../../sites/divatech/404.php';
            ob_end_clean();
            $check($events === ['before', '404 Not Found', 'title', 'robots', 'after', 'footer'], '404 lifecycle order');
        } else {
            require __DIR__ . '/../../sites/divatech/index.php';
            $check($events === ['before', 'title', 'description', 'after', 'component', 'footer'], 'Home lifecycle order');
        }
        echo "Public entry lifecycle passed.\n";
    } finally {
        foreach (array_keys($paths) as $path) { unlink($fixture . $path); }
        foreach (['/bitrix/modules/main/include', '/bitrix/modules/main', '/bitrix/modules', '/bitrix', ''] as $path) { rmdir($fixture . $path); }
    }
}
