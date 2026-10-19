<?php
 
declare(strict_types=1);
 
namespace App\Services\Lessons;

class VideoUrlService
{
    /**
     * Hosts that may be embedded.
     *
     * @var array<int, string>
     */
    private const ALLOWED_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'youtu.be',
        'vimeo.com',
        'www.vimeo.com',
    ];
 
    /**
     * Determine whether the URL points at an allowed host.
     */
    public function isAllowed(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }
 
        $parts = parse_url($url);
 
        return ($parts['scheme'] ?? '') === 'https'
            && in_array(strtolower($parts['host'] ?? ''), self::ALLOWED_HOSTS, true);
    }
 
    /**
     * Build a privacy friendly embed URL, or null when the link cannot be embedded.
     */
    public function embedUrl(?string $url): ?string
    {
        if ($url === null || ! $this->isAllowed($url)) {
            return null;
        }
 
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
 
        if ($host === 'youtu.be' && preg_match('#^/([A-Za-z0-9_-]{11})$#', $path, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }
 
        if (str_contains($host, 'youtube.com')) {
            parse_str($parts['query'] ?? '', $query);
            $id = $query['v'] ?? '';
 
            return preg_match('/^[A-Za-z0-9_-]{11}$/', (string) $id)
                ? 'https://www.youtube-nocookie.com/embed/' . $id
                : null;
        }
 
        if (str_contains($host, 'vimeo.com') && preg_match('#^/(\d+)$#', $path, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
 
        return null;
    }
}
