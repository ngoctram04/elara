<?php

namespace App\Services\AI;

use App\Models\Product;
use Illuminate\Support\Collection;

class ChatbotToolService
{
    public function searchProducts(array $args): array
    {
        $keyword = isset($args['keyword']) ? trim((string) $args['keyword']) : '';
        $normalizedKeyword = $this->normalizeVietnameseText($keyword);

        $minPrice = isset($args['min_price']) && $args['min_price'] !== ''
            ? (float) $args['min_price']
            : null;

        $maxPrice = isset($args['max_price']) && $args['max_price'] !== ''
            ? (float) $args['max_price']
            : null;

        $limit = isset($args['limit']) ? max(1, min((int) $args['limit'], 12)) : 8;

        $query = Product::with(['mainImage', 'brand'])
            ->where('is_active', 1);

        if ($minPrice !== null) {
            $query->where('min_price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('min_price', '<=', $maxPrice);
        }

        $tokens = $this->extractSearchTokens($normalizedKeyword);
        $mainCategory = $this->detectMainCategory($normalizedKeyword);

        if ($normalizedKeyword !== '') {
            $candidates = $this->buildKeywordCandidates($normalizedKeyword);

            $query->where(function ($q) use ($keyword, $candidates, $tokens) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($keyword) {
                        $brandQuery->where('name', 'like', "%{$keyword}%");
                    });

                foreach ($candidates as $kw) {
                    $q->orWhere('name', 'like', "%{$kw}%")
                        ->orWhere('slug', 'like', "%{$kw}%")
                        ->orWhereHas('brand', function ($brandQuery) use ($kw) {
                            $brandQuery->where('name', 'like', "%{$kw}%");
                        });
                }

                foreach ($tokens as $token) {
                    $q->orWhere('name', 'like', "%{$token}%")
                        ->orWhere('slug', 'like', "%{$token}%")
                        ->orWhereHas('brand', function ($brandQuery) use ($token) {
                            $brandQuery->where('name', 'like', "%{$token}%");
                        });
                }
            });
        }

        $products = $query->limit(100)->get();

        if ($products->isEmpty()) {
            return [
                'mode' => 'none',
                'exact_product' => null,
                'products' => [],
                'suggestions' => [],
                'count' => 0,
            ];
        }

        $scored = $products->map(function ($product) use ($normalizedKeyword, $mainCategory) {
            $score = $this->scoreProductMatch($product, $normalizedKeyword, $mainCategory);
            $product->match_score = $score;
            return $product;
        })
            ->filter(function ($product) use ($normalizedKeyword) {
                return $product->match_score > 0 || $normalizedKeyword === '';
            })
            ->sort(function ($a, $b) {
                if ($a->match_score === $b->match_score) {
                    return $b->id <=> $a->id;
                }

                return $b->match_score <=> $a->match_score;
            })
            ->values();

        if ($scored->isEmpty()) {
            return [
                'mode' => 'none',
                'exact_product' => null,
                'products' => [],
                'suggestions' => [],
                'count' => 0,
            ];
        }

        $top = $scored->first();
        $topScore = (int) ($top->match_score ?? 0);

        if ($normalizedKeyword !== '' && $this->shouldReturnExact($normalizedKeyword, $topScore)) {
            $exact = $this->formatProducts(collect([$top]))->first();

            return [
                'mode' => 'exact',
                'exact_product' => $exact,
                'products' => [$exact],
                'suggestions' => [],
                'count' => 1,
            ];
        }

        $suggestions = $scored->take($limit);

        return [
            'mode' => 'suggest',
            'exact_product' => null,
            'products' => [],
            'suggestions' => $this->formatProducts($suggestions)->toArray(),
            'count' => $suggestions->count(),
        ];
    }

    public function searchProductsByCategoryHint(
        string $message,
        int $limit = 8,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        $normalized = $this->normalizeVietnameseText($message);

        $groups = [
            'tay_trang' => ['nuoc tay trang', 'tay trang', 'dau tay trang', 'micellar'],
            'sua_rua_mat' => ['sua rua mat', 'rua mat', 'cleanser'],
            'toner' => ['toner', 'nuoc hoa hong'],
            'serum' => ['serum', 'tinh chat', 'ampoule', 'essence'],
            'kem_chong_nang' => ['kem chong nang', 'chong nang', 'sunscreen'],
            'duong_am' => ['duong am', 'kem duong', 'moisturizer', 'phuc hoi'],
            'tri_mun' => ['tri mun', 'mun', 'giam mun'],
            'mat_na' => ['mat na', 'mask'],
            'son' => ['son', 'lipstick', 'son moi'],
        ];

        foreach ($groups as $items) {
            foreach ($items as $item) {
                if (str_contains($normalized, $item)) {
                    return $this->searchProducts([
                        'keyword' => $item,
                        'limit' => $limit,
                        'min_price' => $minPrice,
                        'max_price' => $maxPrice,
                    ]);
                }
            }
        }

        return [
            'mode' => 'none',
            'exact_product' => null,
            'products' => [],
            'suggestions' => [],
            'count' => 0,
        ];
    }

    public function getShippingFee(array $args): array
    {
        $rawProvince = trim((string) ($args['province'] ?? ''));
        $province = $this->normalizeVietnameseText($rawProvince);

        $vung15 = ['vinh long'];

        $vung25 = [
            'can tho',
            'ben tre',
            'tra vinh',
            'soc trang',
            'hau giang',
            'dong thap',
            'an giang',
            'kien giang',
            'ca mau',
            'bac lieu',
            'tien giang',
        ];

        if ($province === '') {
            return [
                'province' => '',
                'fee' => 35000,
                'formatted_fee' => '35.000₫',
                'note' => 'Bạn cho mình xin tỉnh/thành để báo phí ship chính xác hơn nhé.',
            ];
        }

        if (in_array($province, $vung15, true)) {
            return [
                'province' => $rawProvince,
                'fee' => 15000,
                'formatted_fee' => '15.000₫',
                'note' => 'Đây là mức phí ship dự kiến.',
            ];
        }

        if (in_array($province, $vung25, true)) {
            return [
                'province' => $rawProvince,
                'fee' => 25000,
                'formatted_fee' => '25.000₫',
                'note' => 'Đây là mức phí ship dự kiến.',
            ];
        }

        return [
            'province' => $rawProvince,
            'fee' => 35000,
            'formatted_fee' => '35.000₫',
            'note' => 'Đây là mức phí ship dự kiến.',
        ];
    }

    public function getMembershipInfo(): array
    {
        return [
            'levels' => [
                [
                    'name' => 'Đồng',
                    'points' => 0,
                    'benefits' => ['Không có ưu đãi đặc biệt'],
                ],
                [
                    'name' => 'Bạc',
                    'points' => 1000,
                    'benefits' => ['Giảm 5% vào ngày sinh nhật'],
                ],
                [
                    'name' => 'Vàng',
                    'points' => 3000,
                    'benefits' => [
                        'Freeship đơn từ 300.000đ',
                        'Giảm 10% vào ngày sinh nhật',
                    ],
                ],
                [
                    'name' => 'Kim cương',
                    'points' => 10000,
                    'benefits' => [
                        'Freeship mọi đơn',
                        'Giảm 15% vào ngày sinh nhật',
                    ],
                ],
            ],
        ];
    }

    public function getPromotions(): array
    {
        $products = Product::with(['mainImage', 'brand'])
            ->where('is_active', 1)
            ->whereHas('promotions', function ($q) {
                $q->where('is_active', 1)
                    ->where('type', 'product')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        return [
            'products' => $this->formatProducts($products)->toArray(),
            'count' => $products->count(),
        ];
    }

    public function getStorePolicies(): array
    {
        return [
            'shipping' => 'Phí ship thường từ 15.000đ đến 35.000đ tùy khu vực.',
            'membership' => 'Shop có các hạng Đồng, Bạc, Vàng, Kim cương.',
            'support' => 'Nếu cần hỗ trợ chi tiết hơn, bạn có thể nhắn nhân viên để được tư vấn trực tiếp.',
        ];
    }

    public function detectSkinConcern(string $message): array
    {
        $msg = $this->normalizeVietnameseText($message);

        $concerns = [
            'mụn' => ['mun', 'tri mun', 'da mun', 'noi mun', 'de noi mun'],
            'da dầu' => ['da dau', 'dau nhieu', 'do dau'],
            'da khô' => ['da kho', 'thieu am', 'bong troc'],
            'da nhạy cảm' => ['nhay cam', 'kich ung', 'de kich ung'],
            'thâm nám' => ['tham', 'nam', 'tan nhang'],
            'lão hóa' => ['lao hoa', 'nhan', 'chong lao hoa'],
            'lỗ chân lông' => ['lo chan long', 'to lo chan long'],
        ];

        $matched = [];

        foreach ($concerns as $label => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($msg, $kw)) {
                    $matched[] = $label;
                    break;
                }
            }
        }

        return [
            'concerns' => array_values(array_unique($matched)),
        ];
    }

    public function suggestRoutineByMessage(string $message): array
    {
        $msg = $this->normalizeVietnameseText($message);

        $steps = [];

        if (
            (str_contains($msg, 'da dau') || str_contains($msg, 'do dau'))
            && (str_contains($msg, 'nhay cam') || str_contains($msg, 'kich ung'))
        ) {
            $steps[] = 'Sữa rửa mặt dịu nhẹ, làm sạch vừa đủ';
            $steps[] = 'Toner hoặc serum phục hồi, làm dịu da';
            $steps[] = 'Kem dưỡng mỏng nhẹ, không bí da';
            $steps[] = 'Kem chống nắng dịu nhẹ cho da nhạy cảm';
        } elseif (str_contains($msg, 'mun')) {
            $steps[] = 'Tẩy trang';
            $steps[] = 'Sữa rửa mặt dịu nhẹ';
            $steps[] = 'Serum hoặc kem hỗ trợ da mụn';
            $steps[] = 'Kem dưỡng mỏng nhẹ';
            $steps[] = 'Kem chống nắng ban ngày';
        } elseif (str_contains($msg, 'kho') || str_contains($msg, 'thieu am')) {
            $steps[] = 'Sữa rửa mặt dịu nhẹ';
            $steps[] = 'Toner cấp ẩm';
            $steps[] = 'Serum cấp ẩm';
            $steps[] = 'Kem dưỡng ẩm';
            $steps[] = 'Kem chống nắng ban ngày';
        } elseif (str_contains($msg, 'nhay cam') || str_contains($msg, 'kich ung')) {
            $steps[] = 'Sữa rửa mặt dịu nhẹ';
            $steps[] = 'Toner phục hồi nhẹ';
            $steps[] = 'Serum phục hồi';
            $steps[] = 'Kem dưỡng lành tính';
            $steps[] = 'Kem chống nắng dịu nhẹ ban ngày';
        } else {
            $steps[] = 'Tẩy trang';
            $steps[] = 'Sữa rửa mặt';
            $steps[] = 'Toner';
            $steps[] = 'Serum';
            $steps[] = 'Kem dưỡng';
            $steps[] = 'Kem chống nắng ban ngày';
        }

        return [
            'routine' => $steps,
        ];
    }

    protected function formatProducts(Collection $products): Collection
    {
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->min_price,
                'formatted_price' => number_format((float) $product->min_price, 0, ',', '.') . '₫',
                'image' => $product->main_image_url ?: asset('images/no-image.png'),
                'url' => route('products.show', $product->slug),
                'brand' => optional($product->brand)->name,
            ];
        })->values();
    }

    protected function buildKeywordCandidates(string $keyword): array
    {
        $result = [$keyword];

        foreach ($this->expandKeyword($keyword) as $alias) {
            $result[] = $this->normalizeVietnameseText($alias);
        }

        $tokens = $this->extractSearchTokens($keyword);

        if (count($tokens) >= 2) {
            $result[] = implode(' ', $tokens);
        }

        $bigrams = [];
        for ($i = 0; $i < count($tokens) - 1; $i++) {
            $bigrams[] = $tokens[$i] . ' ' . $tokens[$i + 1];
        }

        $result = array_merge($result, $bigrams);

        return array_values(array_unique(array_filter(array_map('trim', $result))));
    }

    protected function extractSearchTokens(string $text): array
    {
        $text = $this->normalizeVietnameseText($text);

        $stopWords = [
            'cho',
            'toi',
            'minh',
            'voi',
            'loai',
            'cai',
            'nhung',
            'san',
            'pham',
            'shop',
            'ad',
            'oi',
            'can',
            'muon',
            'tim',
            'goi',
            'y',
            'tu',
            'van',
            'duoi',
            'tren',
            'khoang',
            'gia',
            'bao',
            'nhieu',
            'co',
            'gi',
            'nao',
            'mot',
            'nhat',
            'di',
            'a',
            'ha',
            'nhe',
            'nha',
            'xu',
            'ly',
            'gap',
            'don',
            'hang',
            'ho',
            'tro',
        ];

        $parts = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $tokens = [];
        foreach ($parts as $part) {
            if (mb_strlen($part) < 2) {
                continue;
            }

            if (in_array($part, $stopWords, true)) {
                continue;
            }

            if (preg_match('/^\d+$/', $part)) {
                continue;
            }

            $tokens[] = $part;
        }

        return array_values(array_unique($tokens));
    }

    protected function detectMainCategory(string $keyword): ?string
    {
        $map = [
            'nuoc tay trang' => 'tay_trang',
            'tay trang' => 'tay_trang',
            'dau tay trang' => 'tay_trang',
            'sua rua mat' => 'sua_rua_mat',
            'rua mat' => 'sua_rua_mat',
            'cleanser' => 'sua_rua_mat',
            'toner' => 'toner',
            'nuoc hoa hong' => 'toner',
            'serum' => 'serum',
            'tinh chat' => 'serum',
            'ampoule' => 'serum',
            'essence' => 'serum',
            'kem chong nang' => 'kem_chong_nang',
            'chong nang' => 'kem_chong_nang',
            'sunscreen' => 'kem_chong_nang',
            'kem duong' => 'duong_am',
            'duong am' => 'duong_am',
            'moisturizer' => 'duong_am',
            'phuc hoi' => 'duong_am',
            'son' => 'son',
            'mat na' => 'mat_na',
            'mask' => 'mat_na',
            'tri mun' => 'tri_mun',
        ];

        foreach ($map as $phrase => $category) {
            if (str_contains($keyword, $phrase)) {
                return $category;
            }
        }

        return null;
    }

    protected function productMatchesCategory(string $name, ?string $category): bool
    {
        if ($category === null) {
            return true;
        }

        $categoryMap = [
            'tay_trang' => ['nuoc tay trang', 'tay trang', 'dau tay trang', 'micellar'],
            'sua_rua_mat' => ['sua rua mat', 'rua mat', 'cleanser'],
            'toner' => ['toner', 'nuoc hoa hong'],
            'serum' => ['serum', 'tinh chat', 'ampoule', 'essence'],
            'kem_chong_nang' => ['kem chong nang', 'chong nang', 'sunscreen'],
            'duong_am' => ['kem duong', 'duong am', 'moisturizer', 'phuc hoi'],
            'tri_mun' => ['tri mun', 'mun'],
            'mat_na' => ['mat na', 'mask'],
            'son' => ['son', 'lipstick'],
        ];

        foreach ($categoryMap[$category] ?? [] as $phrase) {
            if (str_contains($name, $phrase)) {
                return true;
            }
        }

        return false;
    }

    protected function scoreProductMatch($product, string $keyword, ?string $mainCategory = null): int
    {
        $name = $this->normalizeVietnameseText((string) $product->name);
        $slug = $this->normalizeVietnameseText((string) $product->slug);
        $brand = $this->normalizeVietnameseText((string) optional($product->brand)->name);

        if ($keyword === '') {
            return 1;
        }

        if (!$this->productMatchesCategory($name, $mainCategory)) {
            return 0;
        }

        $tokens = $this->extractSearchTokens($keyword);
        $score = 0;

        if ($name === $keyword) {
            $score += 200;
        }

        if ($slug === $keyword) {
            $score += 180;
        }

        if (str_contains($name, $keyword)) {
            $score += 140;
        }

        if (str_contains($slug, $keyword)) {
            $score += 110;
        }

        if ($brand !== '' && str_contains($brand, $keyword)) {
            $score += 80;
        }

        foreach ($tokens as $token) {
            if (str_contains($name, $token)) {
                $score += 20;
            }

            if (str_contains($slug, $token)) {
                $score += 14;
            }

            if ($brand !== '' && str_contains($brand, $token)) {
                $score += 10;
            }
        }

        $matchedInName = 0;
        foreach ($tokens as $token) {
            if (str_contains($name, $token)) {
                $matchedInName++;
            }
        }

        if (count($tokens) > 0 && $matchedInName === count($tokens)) {
            $score += 60;
        } elseif (count($tokens) > 1 && $matchedInName >= count($tokens) - 1) {
            $score += 25;
        }

        $priorityPhrases = [
            'nuoc tay trang',
            'tay trang',
            'sua rua mat',
            'kem chong nang',
            'kem duong',
            'duong am',
            'tri mun',
            'mat na',
            'serum',
            'toner',
            'son',
        ];

        foreach ($priorityPhrases as $phrase) {
            if (str_contains($keyword, $phrase) && str_contains($name, $phrase)) {
                $score += 40;
            }
        }

        return $score;
    }

    protected function shouldReturnExact(string $keyword, int $topScore): bool
    {
        $tokens = $this->extractSearchTokens($keyword);
        $tokenCount = count($tokens);

        $genericPhrases = [
            'nuoc tay trang',
            'tay trang',
            'dau tay trang',
            'sua rua mat',
            'rua mat',
            'toner',
            'nuoc hoa hong',
            'serum',
            'tinh chat',
            'kem chong nang',
            'chong nang',
            'kem duong',
            'duong am',
            'mat na',
            'son',
            'san pham',
            'my pham',
            'cocoon',
            'maybelline',
            'loreal',
            '3ce',
            'beplain',
        ];

        if (in_array($keyword, $genericPhrases, true)) {
            return false;
        }

        if ($tokenCount <= 3) {
            return false;
        }

        return $topScore >= 120;
    }

    protected function expandKeyword(string $keyword): array
    {
        $normalized = $this->normalizeVietnameseText($keyword);

        $map = [
            'tay trang' => ['nước tẩy trang', 'dầu tẩy trang', 'tay trang', 'micellar'],
            'sua rua mat' => ['sữa rửa mặt', 'rua mat', 'cleanser'],
            'toner' => ['toner', 'nước hoa hồng'],
            'serum' => ['serum', 'tinh chất', 'ampoule', 'essence'],
            'kem chong nang' => ['kem chống nắng', 'chong nang', 'sunscreen'],
            'duong am' => ['kem dưỡng', 'dưỡng ẩm', 'moisturizer'],
            'son' => ['son', 'lipstick', 'son moi'],
            'mun' => ['mụn', 'trị mụn', 'da mụn'],
            'phuc hoi' => ['phục hồi', 'lam diu', 'repair'],
        ];

        $result = [];

        foreach ($map as $key => $aliases) {
            if (str_contains($normalized, $key)) {
                $result = array_merge($result, $aliases);
            }
        }

        return array_values(array_unique($result));
    }

    public function normalizeVietnameseText(string $text): string
    {
        $text = mb_strtolower(trim($text));

        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];

        foreach ($unicode as $nonAccent => $accent) {
            $text = preg_replace("/($accent)/iu", $nonAccent, $text);
        }

        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}