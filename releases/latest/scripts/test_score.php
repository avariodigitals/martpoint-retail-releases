<?php
function scorePhase($findings, $phase) {
    if (empty($findings[$phase])) return '100/100';
    $high = 0; $medium = 0; $low = 0;
    foreach ($findings[$phase] as $f) {
        if ($f['severity'] == 'high') $high++;
        elseif ($f['severity'] == 'medium') $medium++;
        else $low++;
    }
    $penalty = min(100, $high * 25 + $medium * 10 + $low * 1);
    $score = max(0, 100 - $penalty);
    return "$score/100";
}

function overallScore($findings) {
    $scores = [
        scorePhase($findings, 'phase1') => 1,
        scorePhase($findings, 'phase2') => 1,
        scorePhase($findings, 'phase3') => 1,
        scorePhase($findings, 'phase4') => 0.75,
        scorePhase($findings, 'phase5') => 0.75,
        scorePhase($findings, 'phase7') => 0.75,
        scorePhase($findings, 'phase6') => 0.5,
    ];
    $total = 0; $weight = 0;
    foreach ($scores as $score => $w) {
        $value = (int) str_replace('/100', '', $score);
        $total += $value * $w;
        $weight += $w;
    }
    $score = round($total / $weight);
    return "$score/100";
}

$findings = [
    'phase1' => [],
    'phase2' => [],
    'phase3' => array_fill(0, 72, ['severity' => 'low']),
    'phase4' => [],
    'phase5' => [],
    'phase6' => [['severity' => 'medium']],
    'phase7' => [],
];

echo "Phase1: " . scorePhase($findings, 'phase1') . "\n";
echo "Phase2: " . scorePhase($findings, 'phase2') . "\n";
echo "Phase3: " . scorePhase($findings, 'phase3') . "\n";
echo "Phase4: " . scorePhase($findings, 'phase4') . "\n";
echo "Phase5: " . scorePhase($findings, 'phase5') . "\n";
echo "Phase6: " . scorePhase($findings, 'phase6') . "\n";
echo "Phase7: " . scorePhase($findings, 'phase7') . "\n";
echo "Overall: " . overallScore($findings) . "\n";
