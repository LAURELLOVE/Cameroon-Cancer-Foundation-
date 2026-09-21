<?php
// Shared helpers for the CCF forms backend (PHP 7.3+).

define('CCF_ROOT', dirname(__DIR__));
define('CCF_DATA', CCF_ROOT . '/data');
date_default_timezone_set('UTC');

// Fallbacks for hosts that do not have the mbstring extension.
if (!function_exists('mb_check_encoding')) {
    function mb_check_encoding($s, $enc = null)
    {
        return preg_match('//u', (string) $s) === 1;
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen($s, $enc = null)
    {
        return (int) preg_match_all('/./us', (string) $s);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($s, $start, $len = null, $enc = null)
    {
        return implode('', array_slice(preg_split('//u', (string) $s, -1, PREG_SPLIT_NO_EMPTY), $start, $len));
    }
}
if (!function_exists('mb_encode_mimeheader')) {
    function mb_encode_mimeheader($s, $charset = 'UTF-8')
    {
        return '=?UTF-8?B?' . base64_encode((string) $s) . '?=';
    }
}

function ccf_config()
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }
    $cfg = require __DIR__ . '/config.php';
    $local = __DIR__ . '/config.local.php';
    if (is_file($local)) {
        $cfg = array_replace_recursive($cfg, require $local);
    }
    return $cfg;
}

// Random secret created on first use; used to hash visitor IPs and to name the SQLite file.
function ccf_secret()
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }
    $file = CCF_DATA . '/secret.php';
    if (is_file($file)) {
        $v = include $file;
        if (is_string($v) && strlen($v) >= 32) {
            return $secret = $v;
        }
    }
    $new = bin2hex(random_bytes(32));
    @file_put_contents($file, "<?php return '" . $new . "';\n", LOCK_EX);
    if (!is_file($file)) {
        throw new RuntimeException('The "data" folder is not writable. Give it write permission on your host.');
    }
    return $secret = $new;
}

function ccf_sqlite_path()
{
    return CCF_DATA . '/ccf-' . substr(hash('sha256', 'db' . ccf_secret()), 0, 16) . '.sqlite';
}

function ccf_driver()
{
    return ccf_config()['db']['driver'] === 'mysql' ? 'mysql' : 'sqlite';
}

function ccf_db()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $c = ccf_config()['db'];
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    if (ccf_driver() === 'mysql') {
        $dsn = 'mysql:host=' . $c['mysql_host'] . ';port=' . (int) $c['mysql_port'] . ';dbname=' . $c['mysql_name'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $c['mysql_user'], $c['mysql_pass'], $opts);
        $id = 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        $tail = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
    } else {
        $pdo = new PDO('sqlite:' . ccf_sqlite_path(), null, null, $opts);
        $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
        $id = 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $tail = '';
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS submissions (
        id ' . $id . ',
        type VARCHAR(20) NOT NULL,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(40) NOT NULL,
        data TEXT NOT NULL,
        lang VARCHAR(2) NOT NULL,
        status VARCHAR(12) NOT NULL,
        ip_hash CHAR(64) NOT NULL,
        created_at VARCHAR(19) NOT NULL
    )' . $tail);
    $pdo->exec('CREATE TABLE IF NOT EXISTS login_fail (
        id ' . $id . ',
        ip_hash CHAR(64) NOT NULL,
        created_at VARCHAR(19) NOT NULL
    )' . $tail);
    return $pdo;
}

function ccf_now()
{
    return gmdate('Y-m-d H:i:s');
}

function ccf_ip_hash()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    return hash('sha256', $ip . '|' . ccf_secret());
}

function ccf_is_https()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// ---- Form definitions (the server is the source of truth; the HTML must use the same names/values) ----

function ccf_regions()
{
    return ['Adamawa', 'Centre', 'East', 'Far North', 'Littoral', 'North', 'North-West', 'West', 'South', 'South-West'];
}

function ccf_projects()
{
    return ['general', 'screening', 'chemotherapy', 'pediatric', 'hostel'];
}

function ccf_form_types()
{
    $name = ['t' => 'text', 'req' => true, 'max' => 150];
    $email = ['t' => 'email', 'req' => true];
    return [
        'contact' => [
            'label' => 'Contact message',
            'fields' => [
                'name' => $name,
                'email' => $email,
                'phone' => ['t' => 'phone'],
                'topic' => ['t' => 'enum', 'opts' => ['general', 'partnership', 'media', 'other'], 'req' => true],
                'message' => ['t' => 'textarea', 'req' => true],
            ],
        ],
        'partner' => [
            'label' => 'Partnership inquiry',
            'fields' => [
                'organization' => ['t' => 'text', 'req' => true, 'max' => 150],
                'name' => $name,
                'email' => $email,
                'phone' => ['t' => 'phone'],
                'org_type' => ['t' => 'enum', 'opts' => ['corporate', 'institution', 'ngo', 'diaspora', 'other'], 'req' => true],
                'interest' => ['t' => 'enum', 'opts' => ccf_projects(), 'req' => true],
                'message' => ['t' => 'textarea'],
            ],
        ],
        'volunteer' => [
            'label' => 'Volunteer application',
            'fields' => [
                'name' => $name,
                'email' => $email,
                'phone' => ['t' => 'phone', 'req' => true],
                'city' => ['t' => 'text', 'max' => 100],
                'profession' => ['t' => 'text', 'max' => 150],
                'availability' => ['t' => 'enum', 'opts' => ['weekdays', 'weekends', 'campaigns', 'flexible']],
                'message' => ['t' => 'textarea'],
            ],
        ],
        'pledge' => [
            'label' => 'Donation pledge',
            'fields' => [
                'name' => $name,
                'email' => $email,
                'phone' => ['t' => 'phone'],
                'amount' => ['t' => 'amount', 'req' => true],
                'currency' => ['t' => 'enum', 'opts' => ['XAF', 'USD', 'EUR', 'CAD', 'GBP'], 'req' => true],
                'frequency' => ['t' => 'enum', 'opts' => ['one_time', 'monthly'], 'req' => true],
                'project' => ['t' => 'enum', 'opts' => ccf_projects(), 'req' => true],
                'message' => ['t' => 'textarea'],
            ],
        ],
        'screening' => [
            'label' => 'Free screening request',
            'fields' => [
                'name' => $name,
                'phone' => ['t' => 'phone', 'req' => true],
                'email' => ['t' => 'email'],
                'region' => ['t' => 'enum', 'opts' => ccf_regions(), 'req' => true],
                'city' => ['t' => 'text', 'max' => 100],
                'screening_type' => ['t' => 'enum', 'opts' => ['breast', 'cervical', 'prostate', 'not_sure'], 'req' => true],
                'message' => ['t' => 'textarea'],
            ],
        ],
    ];
}

function ccf_clean_text($v, $multiline)
{
    $v = str_replace(["\r\n", "\r"], "\n", (string) $v);
    if (!mb_check_encoding($v, 'UTF-8')) {
        return null;
    }
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $v);
    if (!$multiline) {
        $v = preg_replace('/\s+/u', ' ', $v);
    }
    return trim($v);
}

// Returns [cleanValues, invalidFieldNames].
function ccf_validate($type, array $input)
{
    $types = ccf_form_types();
    $clean = [];
    $bad = [];
    foreach ($types[$type]['fields'] as $key => $spec) {
        $raw = isset($input[$key]) && is_scalar($input[$key]) ? (string) $input[$key] : '';
        $multiline = $spec['t'] === 'textarea';
        $v = ccf_clean_text($raw, $multiline);
        if ($v === null) {
            $bad[] = $key;
            continue;
        }
        $required = !empty($spec['req']);
        if ($v === '') {
            if ($required) {
                $bad[] = $key;
            }
            $clean[$key] = '';
            continue;
        }
        switch ($spec['t']) {
            case 'text':
                if (mb_strlen($v) > (isset($spec['max']) ? $spec['max'] : 200)) {
                    $bad[] = $key;
                }
                break;
            case 'textarea':
                if (mb_strlen($v) > 3000) {
                    $bad[] = $key;
                }
                break;
            case 'email':
                if (strlen($v) > 190 || !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    $bad[] = $key;
                }
                break;
            case 'phone':
                if (!preg_match('/^[0-9+\-\s().]{6,25}$/', $v)) {
                    $bad[] = $key;
                }
                break;
            case 'enum':
                if (!in_array($v, $spec['opts'], true)) {
                    $bad[] = $key;
                }
                break;
            case 'amount':
                $n = str_replace([' ', ','], '', $v);
                if (!is_numeric($n) || (float) $n <= 0 || (float) $n > 1e10) {
                    $bad[] = $key;
                } else {
                    $v = rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
                }
                break;
        }
        $clean[$key] = $v;
    }
    return [$clean, $bad];
}

function ccf_notify($type, array $clean)
{
    $cfg = ccf_config();
    $to = trim($cfg['notify_email']);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $types = ccf_form_types();
    $host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^A-Za-z0-9.\-]/', '', $_SERVER['HTTP_HOST']) : 'localhost';
    $from = trim($cfg['mail_from']) !== '' ? trim($cfg['mail_from']) : 'no-reply@' . preg_replace('/^www\./', '', $host);
    $subject = '[CCF website] ' . $types[$type]['label'] . ' from ' . (isset($clean['name']) ? $clean['name'] : '');
    $lines = [];
    foreach ($clean as $k => $v) {
        if ($v !== '') {
            $lines[] = ucfirst(str_replace('_', ' ', $k)) . ': ' . $v;
        }
    }
    $lines[] = '';
    $lines[] = 'View and manage all submissions in the admin area of the website.';
    $headers = "From: " . preg_replace('/[\r\n]+/', '', $from) . "\r\n"
        . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    if (!empty($clean['email'])) {
        $headers .= 'Reply-To: ' . preg_replace('/[\r\n]+/', '', $clean['email']) . "\r\n";
    }
    @mail($to, mb_encode_mimeheader(preg_replace('/[\r\n]+/', ' ', $subject), 'UTF-8'), implode("\n", $lines), $headers);
}
