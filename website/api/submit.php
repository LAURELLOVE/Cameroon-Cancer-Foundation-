<?php
require __DIR__ . '/lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function ccf_reply($code, array $body)
{
    http_response_code($code);
    echo json_encode($body);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    ccf_reply(405, ['ok' => false, 'error' => 'method']);
}
if (!empty($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 30000) {
    ccf_reply(413, ['ok' => false, 'error' => 'invalid']);
}

try {
    $types = ccf_form_types();
    $type = isset($_POST['type']) && is_string($_POST['type']) ? $_POST['type'] : '';
    if (!isset($types[$type])) {
        ccf_reply(400, ['ok' => false, 'error' => 'invalid']);
    }

    // Honeypot: real visitors never see or fill this field. Pretend success so bots move on.
    if (!empty($_POST['website'])) {
        ccf_reply(200, ['ok' => true]);
    }

    list($clean, $bad) = ccf_validate($type, $_POST);
    $consent = isset($_POST['consent']) && in_array($_POST['consent'], ['1', 'on', 'yes'], true);
    if (!$consent) {
        $bad[] = 'consent';
    }
    if ($bad) {
        ccf_reply(422, ['ok' => false, 'error' => 'invalid', 'fields' => array_values(array_unique($bad))]);
    }

    $db = ccf_db();
    $ip = ccf_ip_hash();
    $limit = max(1, (int) ccf_config()['rate_limit_per_hour']);
    $st = $db->prepare('SELECT COUNT(*) FROM submissions WHERE ip_hash = ? AND created_at > ?');
    $st->execute([$ip, gmdate('Y-m-d H:i:s', time() - 3600)]);
    if ((int) $st->fetchColumn() >= $limit) {
        ccf_reply(429, ['ok' => false, 'error' => 'rate']);
    }

    $lang = isset($_POST['lang']) && $_POST['lang'] === 'fr' ? 'fr' : 'en';
    $core = ['name', 'email', 'phone'];
    $extra = [];
    foreach ($clean as $k => $v) {
        if (!in_array($k, $core, true) && $v !== '') {
            $extra[$k] = $v;
        }
    }
    $db->prepare('INSERT INTO submissions (type, name, email, phone, data, lang, status, ip_hash, created_at) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([
            $type,
            isset($clean['name']) ? $clean['name'] : '',
            isset($clean['email']) ? $clean['email'] : '',
            isset($clean['phone']) ? $clean['phone'] : '',
            json_encode($extra, JSON_UNESCAPED_UNICODE),
            $lang,
            'new',
            $ip,
            ccf_now(),
        ]);

    ccf_notify($type, $clean);
    ccf_reply(200, ['ok' => true]);
} catch (Throwable $e) {
    error_log('CCF submit error: ' . $e->getMessage());
    ccf_reply(500, ['ok' => false, 'error' => 'server']);
}
