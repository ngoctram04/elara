<?php

namespace App\Services;

use Illuminate\Support\Str;

class ContentFilterService
{
    /**
     * Kiểm tra nội dung
     * - Có từ không phù hợp => chặn luôn
     * - Không có => cho qua
     */
    public function filter(string $text): array
    {
        $normalized = $this->normalize($text);
        $badWords   = config('content_filter.bad_words', []);

        foreach ($badWords as $word) {
            $word = trim((string) $word);

            if ($word === '') {
                continue;
            }

            $normalizedWord = $this->normalize($word);

            if ($normalizedWord === '') {
                continue;
            }

            if ($this->matchWord($normalized, $normalizedWord)) {
                return [
                    'blocked' => true,
                    'text'    => null,
                    'found'   => $normalizedWord,
                ];
            }
        }

        return [
            'blocked' => false,
            'text'    => $text,
            'found'   => null,
        ];
    }

    /**
     * Chuẩn hóa nội dung:
     * - lowercase
     * - bỏ dấu tiếng Việt
     * - thay ký tự số hay dùng để lách
     * - bỏ ký tự đặc biệt
     * - gom khoảng trắng
     */
    private function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        // đổi các kiểu lách phổ biến
        $text = strtr($text, [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '@' => 'a',
            '$' => 's',
        ]);

        // bỏ dấu tiếng Việt
        $text = Str::ascii($text);

        // giữ lại chữ, số, khoảng trắng
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        // gom khoảng trắng
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Kiểm tra từ cấm trong nội dung
     * Bắt được:
     * - dit
     * - d i t
     * - d-i-t
     * - d!i.t
     */
    private function matchWord(string $text, string $word): bool
    {
        if ($text === '' || $word === '') {
            return false;
        }

        // match trực tiếp sau normalize
        if (str_contains($text, $word)) {
            return true;
        }

        // match có khoảng trắng xen giữa
        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);

        if (!$chars) {
            return false;
        }

        $pattern = '/' . implode('\s*', array_map('preg_quote', $chars)) . '/i';

        return preg_match($pattern, $text) === 1;
    }
}