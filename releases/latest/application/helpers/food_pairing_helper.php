<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Conservative menu roles. Descriptions must not turn a dish with juice sauce into a drink. */
function mp_food_pairing_role($item) {
    $category = strtolower((string)($item->category_name ?? ''));
    $name = strtolower((string)($item->item_name ?? ''));
    $roles = [
        'drink' => '/\b(drinks?|beverages?|juices?|smoothies?|coffee|tea|zobo|water|soda)\b/',
        'dessert' => '/\b(desserts?|cakes?|brownies?|ice cream|sweets?)\b/',
        'breakfast' => '/\b(brunch|breakfast|akara|pap|toast|pancakes?|waffles?|omelette)\b/',
        'side' => '/\b(appetizers?|appetisers?|starters?|sides?|small chops|wings|spring rolls?|samosas?|fries|plantain|salads?)\b/',
        'main' => '/\b(main courses?|mains?|meals?|rice|jollof|pasta|alfredo|salmon|steak|burger|pizza|shawarma|wraps?|soups?|swallow)\b/',
    ];
    // A recognised category is authoritative; names only fill missing/unknown categories.
    foreach ([$category, $name] as $text) {
        foreach ($roles as $role => $pattern) if (preg_match($pattern, $text)) return $role;
    }
    return '';
}

/** Deterministic, complementary suggestions, not a claim about sales or dietary suitability. */
function mp_food_rank_pairings($anchor, $candidates, $limit = 4) {
    $role = mp_food_pairing_role($anchor);
    $preferences = [
        'main' => ['side' => 100, 'drink' => 95, 'dessert' => 65],
        'breakfast' => ['drink' => 100, 'side' => 70],
        'side' => ['main' => 100, 'drink' => 90],
        'dessert' => ['drink' => 100],
        'drink' => ['main' => 90, 'side' => 80, 'breakfast' => 85, 'dessert' => 60],
    ];
    if (!isset($preferences[$role])) return [];
    $coffee = preg_match('/\b(coffee|macchiato|latte|espresso|cappuccino|tea)\b/i', $anchor->item_name ?? '');
    $ranked = [];
    foreach ($candidates as $candidate) {
        if ((int)$candidate->id === (int)$anchor->id || (int)$candidate->store_id !== (int)$anchor->store_id) continue;
        if (!empty($candidate->parent_id) && (int)$candidate->parent_id === (int)(!empty($anchor->parent_id) ? $anchor->parent_id : $anchor->id)) continue;
        $candidateRole = mp_food_pairing_role($candidate);
        $score = $preferences[$role][$candidateRole] ?? 0;
        if (!$score) continue;
        if ($role === 'drink' && $coffee) $score = ['breakfast' => 110, 'dessert' => 100][$candidateRole] ?? 0;
        if (!$score) continue;
        // Do not pad breakfast with wings/small chops just because both are "sides".
        if ($role === 'breakfast' && $candidateRole === 'side' && !preg_match('/\b(eggs?|sausage|bacon|beans|plantain|toast|hash browns?|fruit|yogurt|yoghurt)\b/i', $candidate->item_name)) continue;
        if ($role === 'breakfast' && $candidateRole === 'drink' && preg_match('/coffee|juice|tea|zobo/i', $candidate->item_name)) $score += 10;
        $copy = clone $candidate;
        $copy->pairing_reason = ['side' => 'A little on the side', 'drink' => 'Something to sip', 'dessert' => 'A sweet finish', 'breakfast' => 'For a relaxed breakfast', 'main' => 'Make it a meal'][$candidateRole];
        $ranked[] = ['item' => $copy, 'role' => $candidateRole, 'score' => $score];
    }
    usort($ranked, function($a, $b) {
        return ($b['score'] <=> $a['score']) ?: ((int)$a['item']->id <=> (int)$b['item']->id);
    });
    // First offer different kinds of accompaniment; then at most two of any role.
    $result = []; $counts = []; $seen = [];
    foreach ([1, 2] as $cap) foreach ($ranked as $row) {
        $id = (int)$row['item']->id;
        if (isset($seen[$id]) || ($counts[$row['role']] ?? 0) >= $cap) continue;
        $result[] = $row['item']; $seen[$id] = true;
        $counts[$row['role']] = ($counts[$row['role']] ?? 0) + 1;
        if (count($result) >= $limit) return $result;
    }
    return $result;
}
