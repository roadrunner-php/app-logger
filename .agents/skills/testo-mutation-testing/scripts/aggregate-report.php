<?php

declare(strict_types=1);

/**
 * Aggregate Infection per-segment logs into a summary report + batched work-lists.
 *
 * Usage:
 *   php aggregate-report.php <mutDir> [--batch=N]
 *
 * <mutDir>   Directory holding the per-segment logs produced in Phase 4:
 *              <segment>.json         (--logger-summary-json)
 *              <segment>.gitlab.json  (--logger-gitlab)
 * --batch=N  Survivors per batch file (default 15). Keeps each batch small enough
 *            for an agent to read in full during Phase 5.
 *
 * Writes:
 *   <mutDir>/../mutation-report.md          summary table + batch index (read this first)
 *   <mutDir>/batches/<segment>-NNN.json     surviving mutants, N per file (the Phase-5 work-lists)
 *
 * Each batch entry keeps only the fields Phase 5 needs:
 *   { "check_name", "location": { "path", "lines": { "begin" } }, "content" }
 *
 * Exit codes: 0 ok, 1 usage / no input.
 */

$batchSize = 15;
$mutDir = null;
foreach (\array_slice($argv, 1) as $arg) {
    if (\preg_match('/^--batch=(\d+)$/', $arg, $m)) {
        $batchSize = \max(1, (int) $m[1]);
        continue;
    }
    $mutDir ??= $arg;
}

if ($mutDir === null || !\is_dir($mutDir)) {
    \fwrite(\STDERR, "Usage: php aggregate-report.php <mutDir> [--batch=N]\n");
    exit(1);
}
$mutDir = \rtrim($mutDir, "/\\");
$reportFile = \dirname($mutDir) . '/mutation-report.md';
$batchDir = $mutDir . '/batches';

$num = static fn(array $s, string $key): int => (int) ($s[$key] ?? 0);

// Collect summary logs: *.json but not *.gitlab.json. Segment name = file basename.
$segments = [];
foreach (\glob($mutDir . '/*.json') ?: [] as $path) {
    if (\str_ends_with($path, '.gitlab.json')) {
        continue;
    }
    $stats = (\json_decode((string) \file_get_contents($path), true) ?: [])['stats'] ?? null;
    if (!\is_array($stats)) {
        \fwrite(\STDERR, "warning: no stats in {$path}, skipping\n");
        continue;
    }
    $segments[\basename($path, '.json')] = $stats;
}

if ($segments === []) {
    \fwrite(\STDERR, "no summary logs (*.json) found in {$mutDir}\n");
    exit(1);
}

// Worst first: sort by MSI ascending.
\uasort($segments, static fn(array $a, array $b): int => ($a['msi'] ?? 0.0) <=> ($b['msi'] ?? 0.0));

// --- Summary table ---
$total = [
    'totalMutantsCount' => 0,
    'killedCount' => 0,
    'escapedCount' => 0,
    'notCoveredCount' => 0,
    'errorCount' => 0,
    'timeOutCount' => 0,
];

$out = [
    '# Mutation report',
    '',
    '| Segment | Total | Killed | Escaped | Not covered | Errors | Timeout | MSI |',
    '|---|--:|--:|--:|--:|--:|--:|--:|',
];

foreach ($segments as $segment => $stats) {
    foreach (\array_keys($total) as $key) {
        $total[$key] += $num($stats, $key);
    }
    $out[] = \sprintf(
        '| %s | %d | %d | %d | %d | %d | %d | %.2f%% |',
        $segment,
        $num($stats, 'totalMutantsCount'),
        $num($stats, 'killedCount'),
        $num($stats, 'escapedCount'),
        $num($stats, 'notCoveredCount'),
        $num($stats, 'errorCount'),
        $num($stats, 'timeOutCount'),
        (float) ($stats['msi'] ?? 0.0),
    );
}

$totalMsi = $total['totalMutantsCount'] > 0
    ? ($total['killedCount'] + $total['errorCount'] + $total['timeOutCount']) / $total['totalMutantsCount'] * 100
    : 0.0;

$out[] = \sprintf(
    '| **Total** | %d | %d | %d | %d | %d | %d | %.2f%% |',
    $total['totalMutantsCount'],
    $total['killedCount'],
    $total['escapedCount'],
    $total['notCoveredCount'],
    $total['errorCount'],
    $total['timeOutCount'],
    $totalMsi,
);
$out[] = '';

// --- Split surviving mutants (gitlab logs) into batch files ---
if (!\is_dir($batchDir)) {
    \mkdir($batchDir, 0777, true);
}

// Drop stale batches from a previous run so old -NNN files can't linger.
foreach (\glob($batchDir . '/*.json') ?: [] as $stale) {
    \unlink($stale);
}

$batchIndex = [];   // rows for the report table
$stdoutLines = [];  // per-batch lines for the console
$grandTotal = 0;

foreach (\array_keys($segments) as $segment) {
    $gitlab = $mutDir . '/' . $segment . '.gitlab.json';
    if (!\is_file($gitlab)) {
        continue;
    }

    $issues = \json_decode((string) \file_get_contents($gitlab), true);
    if (!\is_array($issues) || $issues === []) {
        continue;
    }

    \usort($issues, static function (array $a, array $b): int {
        $byPath = ($a['location']['path'] ?? '') <=> ($b['location']['path'] ?? '');
        return $byPath !== 0
            ? $byPath
            : (($a['location']['lines']['begin'] ?? 0) <=> ($b['location']['lines']['begin'] ?? 0));
    });

    // Keep only the fields Phase 5 needs.
    $entries = \array_map(static fn(array $i): array => [
        'check_name' => $i['check_name'] ?? '?',
        'location' => [
            'path' => $i['location']['path'] ?? '?',
            'lines' => ['begin' => $i['location']['lines']['begin'] ?? 0],
        ],
        'content' => $i['content'] ?? '',
    ], $issues);

    foreach (\array_chunk($entries, $batchSize) as $n => $chunk) {
        $name = \sprintf('%s-%03d.json', $segment, $n + 1);
        \file_put_contents(
            $batchDir . '/' . $name,
            \json_encode($chunk, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES) . "\n",
        );
        $rel = \basename($mutDir) . '/batches/' . $name;
        $batchIndex[] = \sprintf('| `%s` | %s | %d |', $rel, $segment, \count($chunk));
        $stdoutLines[] = \sprintf('  %s/%s (%d)', $batchDir, $name, \count($chunk));
        $grandTotal += \count($chunk);
    }
}

// --- Batch index in the report ---
$out[] = '## Surviving mutants — batches';
$out[] = '';
if ($batchIndex === []) {
    $out[] = 'No surviving mutants. 🎉';
} else {
    $out[] = \sprintf(
        '%d surviving mutants, split into %d batch file(s) of up to %d. In Phase 5 process **one batch file at a time** — do not read the raw `*.gitlab.json`.',
        $grandTotal,
        \count($batchIndex),
        $batchSize,
    );
    $out[] = '';
    $out[] = '| Batch file | Segment | Mutants |';
    $out[] = '|---|---|--:|';
    foreach ($batchIndex as $row) {
        $out[] = $row;
    }
}
$out[] = '';

\file_put_contents($reportFile, \implode("\n", $out) . "\n");

// --- Console summary ---
\fwrite(\STDOUT, 'wrote ' . $reportFile . ' (' . \count($segments) . " segment(s))\n");
if ($batchIndex === []) {
    \fwrite(\STDOUT, "no surviving mutants — nothing to batch\n");
} else {
    \fwrite(\STDOUT, \sprintf(
        "%d surviving mutants -> %d batch file(s) of up to %d in %s:\n",
        $grandTotal,
        \count($batchIndex),
        $batchSize,
        $batchDir,
    ));
    foreach ($stdoutLines as $line) {
        \fwrite(\STDOUT, $line . "\n");
    }
}
