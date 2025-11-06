<?php
/**
 * Convert Forms Anti-Spam Script ´
 *
 * @author      Kai W. (https://github.com/wk1337)
 * @copyright   Copyright (c) 2025 Kai W.
 * @license     MIT License
 *
 * @link        https://github.com/wk1337-/convertforms-antispam-script
 */
// --------------------------------------------------------
//  CONFIG
// --------------------------------------------------------
$logFilePath = realpath(JPATH_ROOT . '/../logs') . '/convertforms_spam.log';
// Debug mode (auf true setzen, um die Formularprüfung zu simulieren, ohne Daten zu senden)
$debugMode   = false;

$compareFields = [
    'nachname' => 'Name',
    'vorname'  => 'Vorname'
];

$randomCheckFields = [
    'nachname' => 'Name',
    'vorname'  => 'Vorname',
    'firma'    => 'Firma'
];

// --------------------------------------------------------
//  FUNKTIONEN
// --------------------------------------------------------
function writeSpamLogEntry($message, $logFilePath) {
    if (!$logFilePath) return;
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    @error_log($entry, 3, $logFilePath);
}

function detectRandomLikeStringTrigger($inputText) {
    $inputText = trim((string)$inputText);
    if ($inputText === '') return false;
    $parts = preg_split('/\s+/u', $inputText);

    foreach ($parts as $word) {
        $word = preg_replace('/[\p{C}]/u', '', $word);
        if (mb_strlen($word) < 6) continue;

        $length     = mb_strlen($word);
        $letters    = preg_match_all('/[A-Za-zÄÖÜäöü]/u', $word);
        $digits     = preg_match_all('/[0-9]/u', $word);
        $symbols    = preg_match_all('/[^A-Za-zÄÖÜäöü0-9]/u', $word);
        $vowels     = preg_match_all('/[aeiouäöüAEIOUÄÖÜ]/u', $word);
        $upper      = preg_match_all('/[A-ZÄÖÜ]/u', $word);
        $lower      = preg_match_all('/[a-zäöü]/u', $word);

        $nonLetters = $length - $letters;
        $nonLetterRatio = $nonLetters / $length;
        $vowelRatio = $letters > 0 ? ($vowels / $letters) : 0;

        if (($upper > 3 && $lower > 3 && $vowelRatio < 0.25 && !preg_match('/^[A-ZÄÖÜ]+$/u', $word)) ||
            $nonLetterRatio > 0.2) {
            return $word;
        }
    }
    return false;
}

function detectSqlInjection($inputText) {
    if (!is_string($inputText) || $inputText === '') return false;
    $v = trim($inputText);
    $v = preg_replace('/\s+/', ' ', $v);
    $patterns = [
        '/\bunion\b\s*\bselect\b/i', '/\bselect\b\s+.*\bfrom\b/i', '/\binsert\b\s+into\b/i',
        '/\bupdate\b\s+.*\bset\b/i', '/\bdelete\b\s+from\b/i', '/\bdrop\b\s+table\b/i',
        '/--/', '/\/\*/', '/;/', '/\bor\s*1=1\b/i', "!('\s*or\s*'1'='1')!i", '/\bconcat\s*\(/i'
    ];
    if (substr_count($v, "'") >= 3 || substr_count($v, '"') >= 3) return true;
    if (preg_match('/[\'\"].*--/u', $v)) return true;
    foreach ($patterns as $pat) if (preg_match($pat, $v)) return true;
    return false;
}

// --------------------------------------------------------
//  PRÜFUNGEN
// --------------------------------------------------------
$compareFieldsNorm = $compareFields;
if (count($compareFieldsNorm) === 2) {
    $keys = array_keys($compareFieldsNorm);
    if (isset($post[$keys[0]], $post[$keys[1]]) && trim($post[$keys[0]]) === trim($post[$keys[1]]) && trim($post[$keys[0]]) !== '') {
        throw new Exception('Die Felder dürfen nicht denselben Inhalt haben. Please check your entries.');
    }
}

foreach ($randomCheckFields as $field => $label) {
    $trigger = detectRandomLikeStringTrigger($post[$field] ?? '');
    if ($trigger) {
        throw new Exception('Spamverdacht: Ihre Eingabe in „' . $label . '“ ist verdächtig. Please check your entries.');
    }
}

foreach ($post as $field => $value) {
    if (detectSqlInjection($value)) {
        throw new Exception('Verdacht auf schädliche Eingabe in Feld „' . $field . '“. Please check your entries.');
    }
}

if ($debugMode) {
    throw new Exception('Debug mode: Keine Probleme erkannt – Formular wurde nicht gesendet (Simulation).');
}

return true;
?>
