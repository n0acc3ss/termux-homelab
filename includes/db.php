<?php
require_once dirname(__DIR__) . '/config.php';

function db(): PDO {
    static $db = null;
    if ($db) return $db;

    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);

    $db = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            username   TEXT UNIQUE NOT NULL COLLATE NOCASE,
            first_name TEXT NOT NULL,
            last_name  TEXT DEFAULT '',
            password   TEXT NOT NULL,
            role       TEXT NOT NULL DEFAULT 'pending',
            ts         INTEGER NOT NULL DEFAULT (strftime('%s','now'))
        );
        CREATE TABLE IF NOT EXISTS rate (
            ip    TEXT PRIMARY KEY,
            hits  INTEGER DEFAULT 1,
            since INTEGER NOT NULL
        );
    ");
    return $db;
}

function load_json(string $file, array $default = []): array {
    if (!file_exists($file)) return $default;
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? $d : $default;
}

function save_json(string $file, array $data): bool {
    if (!is_dir(dirname($file))) mkdir(dirname($file), 0750, true);
    $tmp = $file . '.tmp';
    $ok  = file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($ok !== false) rename($tmp, $file);
    return $ok !== false;
}

function default_services(): array {
    return ['sections' => [
        [
            'id'    => 'live',
            'label' => '// live services',
            'type'  => 'grid',
            'items' => [
                ['id'=>'nextcloud',   'name'=>'Nextcloud',    'icon'=>'☁️',  'desc'=>'Private cloud. Sync files, photos & calendars across all devices.', 'url'=>'https://cloud.al-info.net',  'status'=>'online',      'classes'=>''],
                ['id'=>'navidrome',   'name'=>'Navidrome',    'icon'=>'🎵',  'desc'=>'Self-hosted music server. Stream your library via any Subsonic client.', 'url'=>'https://audio.al-info.net', 'status'=>'online',     'classes'=>''],
                ['id'=>'jellyfin',    'name'=>'Jellyfin',     'icon'=>'🎬',  'desc'=>'Private media server. Stream movies, TV, and music to any device.',  'url'=>'https://media.al-info.net',  'status'=>'soon',        'classes'=>''],
                ['id'=>'upscayl',     'name'=>'Upscayl',      'icon'=>'🔍',  'desc'=>'AI image upscaling. Enhance resolution without losing detail.',       'url'=>'#',                          'status'=>'soon',        'classes'=>''],
                ['id'=>'vaultwarden', 'name'=>'Vaultwarden',  'icon'=>'🔐',  'desc'=>'Self-hosted Bitwarden-compatible password vault. Fully private.',    'url'=>'#',                          'status'=>'soon',        'classes'=>''],
                ['id'=>'rustdesk',    'name'=>'RustDesk',     'icon'=>'🖥️', 'desc'=>'Private remote desktop relay. Access any device, no third party.',   'url'=>'#',                          'status'=>'soon',        'classes'=>''],
                ['id'=>'copyparty',   'name'=>'Copyparty',    'icon'=>'📂',  'desc'=>'Fast file sharing & media server. Upload, browse & stream.',          'url'=>'#',                          'status'=>'soon',        'classes'=>''],
                ['id'=>'suno',        'name'=>'SUNO Thai',    'icon'=>'🎼',  'desc'=>'Agentic Thai lyric composer. AI-powered songwriting via API.',       'url'=>'#',                          'status'=>'soon',        'classes'=>'sc-ai'],
                ['id'=>'admin',       'name'=>'Admin',        'icon'=>'🛡️', 'desc'=>'Infrastructure dashboard. Restricted access only.',                  'url'=>'/admin/',                    'status'=>'restricted',  'classes'=>'sc-admin'],
            ],
        ],
        [
            'id'    => 'tools',
            'label' => '// offline tools & utilities',
            'type'  => 'grid-sm',
            'items' => [
                ['id'=>'img-tools', 'name'=>'Image Toolbox', 'icon'=>'🖼️', 'url'=>'#', 'status'=>'soon', 'classes'=>''],
                ['id'=>'url-clean', 'name'=>'URL Cleaner',   'icon'=>'🔗', 'url'=>'#', 'status'=>'soon', 'classes'=>''],
                ['id'=>'txt-tools', 'name'=>'Text Toolbox',  'icon'=>'📝', 'url'=>'#', 'status'=>'soon', 'classes'=>''],
                ['id'=>'ytdlp',     'name'=>'yt-dlp Helper', 'icon'=>'📥', 'url'=>'#', 'status'=>'soon', 'classes'=>''],
                ['id'=>'more',      'name'=>'More coming',   'icon'=>'✦',  'url'=>'#', 'status'=>'soon', 'classes'=>'sc-sm-more'],
            ],
        ],
    ]];
}

function default_settings(): array {
    return [
        'site_title'       => 'al.homelab',
        'domain'           => 'al-info.net',
        'nav_badge'        => 'private',
        'admin_url'        => '/admin/',
        'hero_label'       => 'self-hosted · private · yours',
        'hero_h1'          => ['Your data.', 'Your rules.', 'Your <em>grid.</em>'],
        'hero_p'           => 'al.homelab runs entirely on your hardware — no subscriptions, no cloud lock-in, no third parties watching your data. Everything you need, self-hosted and always on.',
        'pills'            => [
            ['dot'=>'g', 'text'=>'services online'],
            ['dot'=>'p', 'text'=>'100% private'],
            ['dot'=>'t', 'text'=>'self-hosted'],
            ['dot'=>'p', 'text'=>'restricted access'],
        ],
        'cta_primary_text' => 'browse services →',
        'cta_primary_href' => '#services',
        'footer_text'      => 'al.homelab',
        'footer_links'     => [
            ['label'=>'Admin', 'url'=>'/admin/'],
            ['label'=>'Cloud', 'url'=>'https://cloud.al-info.net'],
            ['label'=>'Audio', 'url'=>'https://audio.al-info.net'],
        ],
        'logo_src'         => '/img/logo.png',
        'hero_logo_src'    => '/img/logo.png',
        'registrations'    => true,   // allow public registration
    ];
}
