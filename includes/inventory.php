<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Constants for inventory encryption
define('INVENTORY_MAX_LENGTH', 8000);
define('INVENTORY_ENCRYPTION_KEY', '19800216');

function inv_recalculate_checksum($text) {
    $hashPos = strpos($text, '#');
    if ($hashPos === false) return $text;
    $header = substr($text, 0, $hashPos + 1);
    $payload = substr($text, $hashPos + 1);
    $parts = explode(';', $payload);
    if (count($parts) < 3) return $text;
    array_pop($parts); // remove old checksum
    $pageNum = (int)$parts[0];
    $lnCheckSum = 0;
    $index = 1;
    for ($p = 0; $p < $pageNum; $p++) {
        if (!isset($parts[$index])) break;
        $usedGrid = (int)$parts[$index++];
        for ($g = 0; $g < $usedGrid; $g++) {
            if (!isset($parts[$index])) break;
            $fields = explode(',', $parts[$index++]);
            $i = 1; // Skip GridID (iOrder)
            for ($j = 0; $j < 10; $j++) {
                $lnCheckSum += (int)$fields[$i++];
            }
            for ($d = 0; $d < 5; $d++) {
                $lnCheckSum += (int)$fields[$i++];
            }
            $hasInstAttr = (int)$fields[$i++];
            $lnCheckSum += $hasInstAttr;
            if ($hasInstAttr > 0) {
                for ($k = 0; $k < 10; $k++) {
                    $lnCheckSum += (int)$fields[$i++];
                    $lnCheckSum += (int)$fields[$i++];
                }
            }
        }
    }
    $parts[] = $lnCheckSum;
    return $header . implode(';', $parts);
}

function inv_encrypt($text) {
    $text = inv_recalculate_checksum($text);
    $key = INVENTORY_ENCRYPTION_KEY;
    $hashPos = strpos($text, '#');
    $prefix = $hashPos !== false ? substr($text, 0, $hashPos + 1) : '';
    $raw = $hashPos !== false ? substr($text, $hashPos + 1) : $text;
    $encrypted = '';
    for ($i = 0; $i < strlen($raw); $i++) {
        $encrypted .= chr((ord($raw[$i]) + ord($key[$i % strlen($key)])) % 256);
    }
    $final = $prefix . $encrypted;
    if (mb_strlen($final, '8bit') > INVENTORY_MAX_LENGTH) {
        return false;
    }
    return str_pad($final, INVENTORY_MAX_LENGTH, ' ');
}

function inv_decrypt($text) {
    $text = rtrim($text);
    $key = INVENTORY_ENCRYPTION_KEY;
    $hashPos = strpos($text, '#');
    $raw = $hashPos !== false ? substr($text, $hashPos + 1) : $text;
    $prefix = $hashPos !== false ? substr($text, 0, $hashPos + 1) : '';
    $decrypted = '';
    for ($i = 0; $i < strlen($raw); $i++) {
        $decrypted .= chr((ord($raw[$i]) - ord($key[$i % strlen($key)])) % 256);
    }
    return $prefix . $decrypted;
}

function loadInventoryContent($id) {
    global $connections;
    selectDB('game');
    $stmt = $connections['game']->prepare('SELECT content FROM resource WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['content'] : false;
}

function saveInventoryContent($id, $content) {
    global $connections;
    selectDB('game');
    $stmt = $connections['game']->prepare('UPDATE resource SET content = ? WHERE id = ?');
    return $stmt->execute([$content, $id]);
}

function addItemToInventory($charId, $itemData) {
    $content = loadInventoryContent($charId);
    if ($content === false) return false;
    $decoded = inv_decrypt($content);
    $decoded = rtrim($decoded, ';') . ';' . $itemData . ';';
    $encrypted = inv_encrypt($decoded);
    if ($encrypted === false) return false;
    return saveInventoryContent($charId, $encrypted);
}
