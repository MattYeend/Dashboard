<?php

declare(strict_types=1);

use App\Services\Lessons\VideoUrlService;

describe('isAllowed', function () {
    test('null and empty strings are allowed because the field is optional', function (?string $url) {
        expect((new VideoUrlService)->isAllowed($url))->toBeTrue();
    })->with([
        'null' => [null],
        'empty string' => [''],
    ]);

    test('https YouTube and Vimeo links are allowed', function (string $url) {
        expect((new VideoUrlService)->isAllowed($url))->toBeTrue();
    })->with([
        'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        'youtube bare host' => ['https://youtube.com/watch?v=dQw4w9WgXcQ'],
        'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ'],
        'vimeo' => ['https://vimeo.com/123456789'],
        'vimeo www' => ['https://www.vimeo.com/123456789'],
    ]);

    test('unsafe schemes and unknown hosts are rejected', function (string $url) {
        expect((new VideoUrlService)->isAllowed($url))->toBeFalse();
    })->with([
        'http' => ['http://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        'javascript' => ['javascript:alert(1)'],
        'data' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
        'unknown host' => ['https://example.com/video'],
        'look-alike subdomain' => ['https://www.youtube.com.evil.example/watch?v=dQw4w9WgXcQ'],
        'look-alike suffix' => ['https://notyoutube.com/watch?v=dQw4w9WgXcQ'],
        'no scheme' => ['www.youtube.com/watch?v=dQw4w9WgXcQ'],
    ]);
});

describe('embedUrl', function () {
    test('YouTube links become privacy friendly youtube-nocookie.com embeds', function (string $url) {
        expect((new VideoUrlService)->embedUrl($url))
            ->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
    })->with([
        'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        'watch with extra query' => ['https://youtube.com/watch?v=dQw4w9WgXcQ&t=10s'],
        'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ'],
    ]);

    test('Vimeo links become player.vimeo.com embeds', function (string $url) {
        expect((new VideoUrlService)->embedUrl($url))
            ->toBe('https://player.vimeo.com/video/123456789');
    })->with([
        'vimeo' => ['https://vimeo.com/123456789'],
        'vimeo www' => ['https://www.vimeo.com/123456789'],
    ]);

    test('anything else returns null', function (?string $url) {
        expect((new VideoUrlService)->embedUrl($url))->toBeNull();
    })->with([
        'null' => [null],
        'empty string' => [''],
        'http' => ['http://youtu.be/dQw4w9WgXcQ'],
        'javascript' => ['javascript:alert(1)'],
        'data' => ['data:text/html,<script>alert(1)</script>'],
        'unknown host' => ['https://example.com/watch?v=dQw4w9WgXcQ'],
        'look-alike host' => ['https://youtube.com.evil.example/watch?v=dQw4w9WgXcQ'],
        'youtube without id' => ['https://www.youtube.com/watch'],
        'youtube id too short' => ['https://www.youtube.com/watch?v=short'],
        'youtube id with bad characters' => ['https://www.youtube.com/watch?v=dQw4w9WgX<>'],
        'youtube id as array' => ['https://www.youtube.com/watch?v[]=dQw4w9WgXcQ'],
        'youtu.be with extra path' => ['https://youtu.be/dQw4w9WgXcQ/extra'],
        'vimeo channel page' => ['https://vimeo.com/channels/staffpicks'],
        'vimeo non numeric id' => ['https://vimeo.com/abc'],
    ]);
});
