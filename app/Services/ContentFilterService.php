<?php

namespace App\Services;

use Illuminate\Support\Str;

class ContentFilterService
{
    public function filter(?string $text): array
    {
        $text = trim((string) $text);

        if ($text === '') {
            return [
                'blocked' => false,
                'flagged' => false,
                'text'    => '',
                'found'   => null,
                'type'    => null,
            ];
        }

        $normalized = $this->normalize($text);

        // 1. Chặn hoàn toàn
        $blockedWords = config('content_filter.blocked_words', []);
        foreach ($blockedWords as $word) {
            $originalWord = trim((string) $word);

            if ($originalWord === '') {
                continue;
            }

            $normalizedWord = $this->normalize($originalWord);

            if ($normalizedWord === '') {
                continue;
            }

            if ($this->matchWord($normalized, $normalizedWord)) {
                return [
                    'blocked' => true,
                    'flagged' => false,
                    'text'    => null,
                    'found'   => $originalWord,
                    'type'    => 'blocked',
                ];
            }
        }

        // 2. Chỉ đánh dấu nghi vấn
        $flagWords = config('content_filter.flag_words', []);
        foreach ($flagWords as $word) {
            $originalWord = trim((string) $word);

            if ($originalWord === '') {
                continue;
            }

            $normalizedWord = $this->normalize($originalWord);

            if ($normalizedWord === '') {
                continue;
            }

            if ($this->matchWord($normalized, $normalizedWord)) {
                return [
                    'blocked' => false,
                    'flagged' => true,
                    'text'    => $text,
                    'found'   => $originalWord,
                    'type'    => 'flagged',
                ];
            }
        }

        return [
            'blocked' => false,
            'flagged' => false,
            'text'    => $text,
            'found'   => null,
            'type'    => null,
        ];
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        // thay các kiểu lách phổ biến
        $text = strtr($text, [
            '0' => 'o',
            '1' => 'i',
            '2' => 'z',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '6' => 'g',
            '7' => 't',
            '8' => 'b',
            '9' => 'g',
            '@' => 'a',
            '$' => 's',
            '!' => 'i',
        ]);

        // bỏ dấu tiếng Việt
        $text = Str::ascii($text);

        // bỏ ký tự đặc biệt nhưng giữ chữ, số, khoảng trắng
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        // gom nhiều khoảng trắng
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function matchWord(string $text, string $word): bool
    {
        if ($text === '' || $word === '') {
            return false;
        }

        // match nguyên cụm từ
        $exactPattern = '/(?:^|\s)' . preg_quote($word, '/') . '(?:\s|$)/i';
        if (preg_match($exactPattern, $text)) {
            return true;
        }

        // match kiểu chèn khoảng trắng giữa các ký tự: d i t, s c a m
        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);

        if (!$chars) {
            return false;
        }

        $pattern = '/(?:^|\s)' . implode('\s*', array_map(function ($char) {
            return preg_quote($char, '/');
        }, $chars)) . '(?:\s|$)/i';

        return preg_match($pattern, $text) === 1;
    }
}