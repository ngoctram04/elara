<?php

namespace App\Services\AI;

class ChatbotService
{
    public function __construct(
        protected ChatbotToolService $tools,
        protected GeminiService $gemini
    ) {}

    public function reply(string $message): array
    {
        $original = trim($message);

        if ($original === '') {
            return $this->text('Bạn hãy nhập nội dung cần tư vấn nhé.');
        }

        $msg = $this->normalize($original);

        /*
    |--------------------------------------------------------------------------
    | NHÓM CÂU NGẮN CƠ BẢN
    |--------------------------------------------------------------------------
    */
        if ($this->isGreetingOnly($msg)) {
            return $this->text(
                "Xin chào! Mình là trợ lý tư vấn của ELARA.\n" .
                "Bạn có thể hỏi về sản phẩm, giá, khuyến mãi, phí ship, routine skincare hoặc tình trạng da nhé."
            );
        }

        if ($this->isThanks($msg)) {
            return $this->text('Dạ không có gì ạ. Nếu bạn cần mình gợi ý sản phẩm phù hợp nữa thì cứ nhắn nhé.');
        }

        if ($this->isFarewell($msg)) {
            return $this->text('Cảm ơn bạn đã ghé ELARA. Khi nào cần tư vấn thêm mình luôn sẵn sàng.');
        }

        /*
    |--------------------------------------------------------------------------
    | ƯU TIÊN SUPPORT / ĐƠN HÀNG TRƯỚC KHI SEARCH SẢN PHẨM
    |--------------------------------------------------------------------------
    */
        if ($this->isHumanSupport($msg)) {
            return $this->text(
                'Nếu bạn cần hỗ trợ gấp hoặc muốn gặp nhân viên, bạn vui lòng ' .
                '<a href="http://127.0.0.1:8000/chat" target="_blank" rel="noopener noreferrer">nhấn vào đây để chat với nhân viên</a> ' .
                'để được hỗ trợ trực tiếp nhanh hơn nhé.'
            );
        }

        if ($this->isOrderQuestion($msg)) {
            return $this->text(
                'Nếu bạn cần xử lý đơn hàng gấp, kiểm tra tình trạng đơn, đổi địa chỉ hoặc hủy đơn, bạn vui lòng ' .
                '<a href="http://127.0.0.1:8000/orders" target="_blank" rel="noopener noreferrer">vào mục Đơn hàng của tôi</a> ' .
                'hoặc nhắn nhân viên để được hỗ trợ nhanh hơn nhé.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | CHĂM SÓC DA / ROUTINE
    |--------------------------------------------------------------------------
    */
        if ($this->isRoutineQuestion($msg)) {
            $routine = $this->tools->suggestRoutineByMessage($original);
            $steps = $routine['routine'] ?? [];

            if (!empty($steps)) {
                $text = "Routine gợi ý cho bạn:\n- " . implode("\n- ", $steps);
                $text .= "\n\nNếu bạn muốn, mình có thể gợi ý thêm sản phẩm theo từng bước.";
                return $this->text($text);
            }

            return $this->text('Bạn có thể mô tả loại da hoặc nhu cầu cụ thể hơn để mình gợi ý routine phù hợp nhé.');
        }

        if ($this->isSkinConcernQuestion($msg)) {
            $concerns = $this->tools->detectSkinConcern($original);
            $routine = $this->tools->suggestRoutineByMessage($original);
            $steps = $routine['routine'] ?? [];

            if (!empty($concerns['concerns'])) {
                $text = 'Mình thấy bạn đang quan tâm đến: ' . implode(', ', $concerns['concerns']) . ".\n";

                if (!empty($steps)) {
                    $text .= "Routine cơ bản mình gợi ý là:\n- " . implode("\n- ", $steps);
                    $text .= "\n\nBạn cũng có thể nhắn rõ hơn như \"da dầu mụn\", \"da khô nhạy cảm\" để mình gợi ý sát hơn nhé.";
                } else {
                    $text .= "Bạn có thể mô tả kỹ hơn nhu cầu để mình gợi ý routine phù hợp hơn nhé.";
                }

                return $this->text($text);
            }

            return $this->text(
                'Với tình trạng da như bạn mô tả, mình khuyên ưu tiên làm sạch dịu nhẹ, phục hồi hàng rào da và chống nắng đều đặn. ' .
                'Bạn có thể nhắn rõ hơn như da dầu, da khô, da mụn hay nhạy cảm để mình gợi ý routine sát hơn nhé.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | THÔNG TIN SHOP
    |--------------------------------------------------------------------------
    */
        if ($this->isPromotionQuestion($msg)) {
            $promo = $this->tools->getPromotions();

            if (!empty($promo['products'])) {
                return [
                    'type' => 'product_list',
                    'reply' => 'Hiện shop đang có một số sản phẩm khuyến mãi nổi bật như sau:',
                    'products' => $promo['products'],
                ];
            }

            return $this->text('Hiện tại mình chưa thấy sản phẩm khuyến mãi nổi bật nào. Bạn có thể xem thêm tại shop hoặc nhắn nhân viên để được hỗ trợ nhé.');
        }

        if ($this->isMembershipQuestion($msg)) {
            $membership = $this->tools->getMembershipInfo();
            $lines = ['ELARA hiện có các hạng thành viên như sau:'];

            foreach ($membership['levels'] ?? [] as $level) {
                $benefits = implode(', ', $level['benefits'] ?? []);
                $lines[] = "- {$level['name']}: từ {$level['points']} điểm, ưu đãi: {$benefits}.";
            }

            return $this->text(implode("\n", $lines));
        }

        if ($this->isShippingQuestion($msg)) {
            $province = $this->extractProvince($original);
            $shipping = $this->tools->getShippingFee(['province' => $province]);

            if ($province !== '') {
                return $this->text("Phí ship dự kiến đến {$shipping['province']} là {$shipping['formatted_fee']}.");
            }

            return $this->text('Phí ship thường từ 15.000đ đến 35.000đ tùy khu vực. Bạn cho mình xin tỉnh/thành để báo chính xác hơn nhé.');
        }

        if ($this->isPolicyQuestion($msg)) {
            $policies = $this->tools->getStorePolicies();

            return $this->text(
                "Một số thông tin nhanh của shop:\n" .
                "- Ship: " . ($policies['shipping'] ?? 'Đang cập nhật') . "\n" .
                    "- Thành viên: " . ($policies['membership'] ?? 'Đang cập nhật') . "\n" .
                    "- Hỗ trợ: " . ($policies['support'] ?? 'Đang cập nhật')
            );
        }

        /*
    |--------------------------------------------------------------------------
    | TÌM SẢN PHẨM / GIÁ
    |--------------------------------------------------------------------------
    */
        $budget = $this->extractBudget($msg);
        $keyword = $this->extractProductKeyword($original);

        if ($this->looksLikeNonProductSupport($msg)) {
            return $this->text('Vấn đề này bạn vui lòng nhắn nhân viên để được hỗ trợ nhanh hơn nhé.');
        }

        $intent = $this->detectSearchIntent($msg, $keyword, $budget);

        if ($intent === 'list_by_price_only') {
            $result = $this->tools->searchProducts([
                'keyword' => '',
                'min_price' => $budget['min'],
                'max_price' => $budget['max'],
                'limit' => 8,
            ]);

            $products = !empty($result['suggestions']) ? $result['suggestions'] : ($result['products'] ?? []);

            if (!empty($products)) {
                return [
                    'type' => 'product_list',
                    'reply' => 'Mình tìm được một số sản phẩm phù hợp với mức giá bạn cần:',
                    'products' => $products,
                ];
            }

            return $this->text('Hiện mình chưa tìm thấy sản phẩm phù hợp với mức giá bạn cần.');
        }

        if ($keyword !== '') {
            if ($intent === 'list') {
                $result = $this->tools->searchProducts([
                    'keyword' => $keyword,
                    'min_price' => $budget['min'],
                    'max_price' => $budget['max'],
                    'limit' => 8,
                ]);

                $products = !empty($result['suggestions']) ? $result['suggestions'] : ($result['products'] ?? []);

                if (!empty($products)) {
                    return [
                        'type' => 'product_list',
                        'reply' => $this->buildListReply($budget),
                        'products' => $products,
                    ];
                }

                $byHint = $this->tools->searchProductsByCategoryHint(
                    $original,
                    8,
                    $budget['min'],
                    $budget['max']
                );

                $hintProducts = !empty($byHint['suggestions']) ? $byHint['suggestions'] : ($byHint['products'] ?? []);

                if (!empty($hintProducts)) {
                    return [
                        'type' => 'product_list',
                        'reply' => $this->buildListReply($budget),
                        'products' => $hintProducts,
                    ];
                }
            }

            if ($intent === 'exact') {
                $result = $this->tools->searchProducts([
                    'keyword' => $keyword,
                    'min_price' => $budget['min'],
                    'max_price' => $budget['max'],
                    'limit' => 4,
                ]);

                if (($result['mode'] ?? '') === 'exact' && !empty($result['products'])) {
                    return [
                        'type' => 'product_list',
                        'reply' => 'Mình tìm thấy đúng sản phẩm bạn đang cần:',
                        'products' => $result['products'],
                    ];
                }

                if (!empty($result['suggestions'])) {
                    return [
                        'type' => 'product_list',
                        'reply' => 'Mình chưa thấy đúng chính xác sản phẩm bạn nhập. Đây là một số gợi ý gần đúng để bạn tham khảo nhé:',
                        'products' => $result['suggestions'],
                    ];
                }
            }
        }

        if ($budget['min'] !== null || $budget['max'] !== null) {
            return $this->text(
                "Hiện mình chưa tìm thấy sản phẩm đúng với mức giá bạn cần.\n" .
                'Bạn có thể thử tăng/giảm ngân sách hoặc nhập tên sản phẩm cụ thể hơn nhé.'
            );
        }

        if ($this->isPriceQuestion($msg)) {
            return $this->text('Bạn hãy nhắn tên sản phẩm hoặc nhóm sản phẩm, mình sẽ tìm giá giúp bạn nhé. Ví dụ: "serum", "nước tẩy trang", "kem chống nắng dưới 300k".');
        }

        /*
    |--------------------------------------------------------------------------
    | FALLBACK AI - NHƯNG VẪN BÁM CSDL
    |--------------------------------------------------------------------------
    */
        if ($this->gemini->isConfigured()) {
            $dbContext = $this->buildDatabaseContext($original, $keyword, $budget);

            $aiReply = $this->gemini->ask(
                $this->buildAiPrompt($original, $dbContext)
            );

            if (is_string($aiReply) && trim($aiReply) !== '' && !$this->isGenericAiError($aiReply)) {
                return $this->text($this->sanitizeAiReply($aiReply));
            }
        }

        return $this->text(
            "Mình chưa tìm thấy đúng thông tin bạn cần.\n" .
            'Bạn có thể nhập rõ hơn tên sản phẩm, thương hiệu, mức giá hoặc nhắn nhân viên để được hỗ trợ trực tiếp nhé.'
        );
    }

    protected function text(string $msg): array
    {
        return [
            'type' => 'text',
            'reply' => $msg,
            'products' => [],
        ];
    }

    protected function normalize(string $text): string
    {
        return $this->tools->normalizeVietnameseText($text);
    }

    protected function containsAny(string $msg, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($msg, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function isGreetingOnly(string $msg): bool
    {
        $greetings = [
            'xin chao',
            'chao',
            'hello',
            'hi',
            'shop oi',
            'ad oi',
        ];

        foreach ($greetings as $greeting) {
            if ($msg === $greeting) {
                return true;
            }
        }

        return false;
    }

    protected function isThanks(string $msg): bool
    {
        return $this->containsAny($msg, ['cam on', 'thanks', 'thank', 'tks']);
    }

    protected function isFarewell(string $msg): bool
    {
        return $this->containsAny($msg, ['tam biet', 'bye', 'bai bai', 'hen gap lai']);
    }

    protected function isHumanSupport(string $msg): bool
    {
        return $this->containsAny($msg, [
            'nhan vien',
            'tu van vien',
            'nguoi that',
            'admin',
            'ho tro truc tiep',
            'gap nhan vien',
            'noi chuyen voi nhan vien',
            'can nguoi ho tro',
            'ho tro gap',
            'xu ly gap',
            'gap gap',
            'gap nguoi',
            'tu van truc tiep',
        ]);
    }

    protected function isPromotionQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['khuyen mai', 'giam gia', 'sale', 'uu dai']);
    }

    protected function isMembershipQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['thanh vien', 'hang thanh vien', 'tich diem', 'bac', 'vang', 'kim cuong']);
    }

    protected function isShippingQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['ship', 'van chuyen', 'giao hang', 'phi ship', 'cuoc ship', 'freeship']);
    }

    protected function isRoutineQuestion(string $msg): bool
    {
        return $this->containsAny($msg, [
            'routine',
            'chu trinh skincare',
            'cac buoc skincare',
            'skincare co ban',
            'skincare',
            'routine skincare',
        ]);
    }

    protected function isSkinConcernQuestion(string $msg): bool
    {
        return $this->containsAny($msg, [
            'da dau',
            'da kho',
            'da mun',
            'nhay cam',
            'tham',
            'nam',
            'lao hoa',
            'lo chan long',
            'thieu am',
            'kich ung',
            'do dau',
            'de noi mun',
        ]);
    }

    protected function isPriceQuestion(string $msg): bool
    {
        return $this->containsAny($msg, [
            'bao nhieu tien',
            'gia bao nhieu',
            'gia',
            'tam gia',
            'duoi ',
            'tren ',
            'tu ',
        ]);
    }

    protected function isOrderQuestion(string $msg): bool
    {
        return $this->containsAny($msg, [
            'don hang',
            'kiem tra don',
            'ma don',
            'tinh trang don',
            'xu ly don',
            'xu ly don gap',
            'don gap',
            'huy don',
            'doi dia chi',
            'doi don',
            'van don',
            'giao tre',
            'cham giao',
        ]);
    }

    protected function isPolicyQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['chinh sach', 'doi tra', 'bao hanh', 'quy dinh']);
    }

    protected function looksLikeNonProductSupport(string $msg): bool
    {
        return $this->containsAny($msg, [
            'xu ly',
            'gap',
            'khieu nai',
            'ho tro',
            'don hang',
            'huy don',
            'doi tra',
            'tra hang',
            'nhan vien',
            'giao cham',
            'giao tre',
            'van de don',
        ]);
    }

    protected function extractProvince(string $message): string
    {
        $normalized = $this->normalize($message);

        $provinces = [
            'vinh long' => 'Vĩnh Long',
            'can tho' => 'Cần Thơ',
            'ben tre' => 'Bến Tre',
            'tra vinh' => 'Trà Vinh',
            'soc trang' => 'Sóc Trăng',
            'hau giang' => 'Hậu Giang',
            'dong thap' => 'Đồng Tháp',
            'an giang' => 'An Giang',
            'kien giang' => 'Kiên Giang',
            'ca mau' => 'Cà Mau',
            'bac lieu' => 'Bạc Liêu',
            'tien giang' => 'Tiền Giang',
            'ho chi minh' => 'Hồ Chí Minh',
            'sai gon' => 'Hồ Chí Minh',
            'ha noi' => 'Hà Nội',
            'da nang' => 'Đà Nẵng',
        ];

        foreach ($provinces as $key => $label) {
            if (str_contains($normalized, $key)) {
                return $label;
            }
        }

        return '';
    }

    protected function extractBudget(string $msg): array
    {
        $min = null;
        $max = null;

        if (preg_match('/tu\s*(\d+)\s*(k|ngan|trieu)?\s*(den|toi)\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $min = $this->parseMoneyValue($m[1], $m[2] ?? null);
            $max = $this->parseMoneyValue($m[4], $m[5] ?? null);
        } elseif (preg_match('/(\d+)\s*(k|ngan|trieu)?\s*(den|toi)\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $min = $this->parseMoneyValue($m[1], $m[2] ?? null);
            $max = $this->parseMoneyValue($m[4], $m[5] ?? null);
        } elseif (preg_match('/(\d+)\s*-\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $min = $this->parseMoneyValue($m[1], $m[3] ?? null);
            $max = $this->parseMoneyValue($m[2], $m[3] ?? null);
        } elseif (preg_match('/duoi\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $max = $this->parseMoneyValue($m[1], $m[2] ?? null);
        } elseif (preg_match('/tren\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $min = $this->parseMoneyValue($m[1], $m[2] ?? null);
        }

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    protected function parseMoneyValue(string $number, ?string $unit = null): float
    {
        $value = (float) $number;
        $unit = strtolower((string) $unit);

        if (in_array($unit, ['k', 'ngan'], true)) {
            return $value * 1000;
        }

        if ($unit === 'trieu') {
            return $value * 1000000;
        }

        if ($value < 1000) {
            return $value * 1000;
        }

        return $value;
    }

    protected function extractProductKeyword(string $message): string
    {
        $cleaned = $this->normalize($message);

        $noisePhrases = [
            'san pham',
            'toi can',
            'minh can',
            'cho minh',
            'tu van',
            'goi y',
            'tim',
            'muon mua',
            'loai',
            'gia bao nhieu',
            'bao nhieu',
            'tam gia',
            'phu hop',
            'cho da',
            'nen dung gi',
            'shop co',
            'moi bat dau skincare',
            'bat dau skincare',
        ];

        foreach ($noisePhrases as $phrase) {
            $cleaned = str_replace($phrase, ' ', $cleaned);
        }

        $cleaned = preg_replace('/tu\s*\d+\s*(k|ngan|trieu)?\s*(den|toi)\s*\d+\s*(k|ngan|trieu)?/u', ' ', $cleaned);
        $cleaned = preg_replace('/\d+\s*(k|ngan|trieu)?\s*(den|toi)\s*\d+\s*(k|ngan|trieu)?/u', ' ', $cleaned);
        $cleaned = preg_replace('/duoi\s*\d+\s*(k|ngan|trieu)?/u', ' ', $cleaned);
        $cleaned = preg_replace('/tren\s*\d+\s*(k|ngan|trieu)?/u', ' ', $cleaned);
        $cleaned = preg_replace('/\d+\s*-\s*\d+\s*(k|ngan|trieu)?/u', ' ', $cleaned);

        $cleaned = preg_replace('/\d+/', ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));

        $tokens = array_filter(explode(' ', $cleaned), function ($token) {
            return !in_array($token, [
                'san',
                'pham',
                'tu',
                'den',
                'toi',
                'duoi',
                'tren',
                'khoang',
                'tam',
                'gia',
                'bao',
                'nhieu',
                'co',
                'khong',
                'xu',
                'ly',
                'gap',
                'don',
                'hang',
                'ho',
                'tro',
            ], true);
        });

        return trim(implode(' ', $tokens));
    }

    protected function detectSearchIntent(string $normalizedMessage, string $keyword, array $budget): string
    {
        if ($this->looksLikeNonProductSupport($normalizedMessage)) {
            return 'none';
        }

        if (($budget['min'] !== null || $budget['max'] !== null) && trim($keyword) === '') {
            return 'list_by_price_only';
        }

        if ($budget['min'] !== null || $budget['max'] !== null) {
            return 'list';
        }

        if ($this->containsAny($normalizedMessage, [
            'duoi ',
            'tren ',
            'tu ',
            'den ',
            'tam ',
            'khoang ',
        ])) {
            return 'list';
        }

        $genericKeywords = [
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
            'my pham',
            'san pham',
            'cocoon',
            'maybelline',
            'loreal',
            '3ce',
            'beplain',
        ];

        if (in_array($keyword, $genericKeywords, true)) {
            return 'list';
        }

        $tokenCount = count(array_filter(explode(' ', $keyword)));

        if ($tokenCount === 0) {
            return 'none';
        }

        if ($tokenCount <= 3) {
            return 'list';
        }

        return 'exact';
    }

    protected function buildListReply(array $budget): string
    {
        if ($budget['min'] !== null || $budget['max'] !== null) {
            return 'Mình tìm được một số sản phẩm phù hợp với mức giá bạn cần:';
        }

        return 'Mình gợi ý một số sản phẩm phù hợp để bạn tham khảo nhé:';
    }

    protected function buildDatabaseContext(string $originalMessage, string $keyword, array $budget): string
    {
        $policies = $this->tools->getStorePolicies();
        $membership = $this->tools->getMembershipInfo();
        $promotions = $this->tools->getPromotions();

        $membershipText = '';
        foreach (($membership['levels'] ?? []) as $level) {
            $benefits = implode(', ', $level['benefits'] ?? []);
            $membershipText .= "- {$level['name']}: từ {$level['points']} điểm, ưu đãi: {$benefits}\n";
        }

        $productContext = '';
        if ($keyword !== '' || $budget['min'] !== null || $budget['max'] !== null) {
            $search = $this->tools->searchProducts([
                'keyword' => $keyword,
                'min_price' => $budget['min'],
                'max_price' => $budget['max'],
                'limit' => 5,
            ]);

            $products = !empty($search['suggestions']) ? $search['suggestions'] : ($search['products'] ?? []);

            if (empty($products)) {
                $byHint = $this->tools->searchProductsByCategoryHint(
                    $originalMessage,
                    5,
                    $budget['min'],
                    $budget['max']
                );

                $products = !empty($byHint['suggestions']) ? $byHint['suggestions'] : ($byHint['products'] ?? []);
            }

            if (!empty($products)) {
                $productContext .= "Sản phẩm liên quan tìm được trong CSDL:\n";
                foreach ($products as $product) {
                    $brand = $product['brand'] ?? 'Không rõ thương hiệu';
                    $price = $product['formatted_price'] ?? '';
                    $productContext .= "- {$product['name']} | Thương hiệu: {$brand} | Giá: {$price}\n";
                }
            } else {
                $productContext .= "Không tìm thấy sản phẩm phù hợp trong CSDL với từ khóa/ngân sách hiện tại.\n";
            }
        }

        $promoText = '';
        if (!empty($promotions['products'])) {
            $promoText .= "Một số sản phẩm khuyến mãi hiện có:\n";
            foreach ($promotions['products'] as $product) {
                $promoText .= "- {$product['name']} | Giá: {$product['formatted_price']}\n";
            }
        }

        return <<<TEXT
Thông tin từ CSDL/shop nội bộ:

Chính sách:
- Ship: {$policies['shipping']}
- Thành viên: {$policies['membership']}
- Hỗ trợ: {$policies['support']}

Các hạng thành viên:
{$membershipText}

{$promoText}

{$productContext}
TEXT;
    }

    protected function buildAiPrompt(string $message, string $dbContext): string
    {
        return <<<PROMPT
Bạn là trợ lý AI của website mỹ phẩm ELARA.

Nguyên tắc:
- Trả lời bằng tiếng Việt.
- Ngắn gọn, thân thiện, dễ hiểu.
- Ưu tiên tuyệt đối thông tin từ CSDL/shop nội bộ được cung cấp bên dưới.
- Không bịa giá, tồn kho, tên sản phẩm, chương trình khuyến mãi nếu dữ liệu nội bộ không có.
- Nếu dữ liệu nội bộ không đủ để kết luận, hãy nói rõ là chưa thấy thông tin chính xác trong dữ liệu hiện có.
- Nếu khách hỏi về skincare, routine, tình trạng da, bạn có thể tư vấn ngắn gọn, an toàn.
- Nếu là vấn đề da nghiêm trọng, hãy khuyên gặp bác sĩ da liễu.
- Không dùng markdown phức tạp, chỉ trả lời văn bản thường.
- Không được tự tạo ra chính sách mới của shop.
- Nếu có danh sách sản phẩm từ CSDL, hãy ưu tiên dựa vào đó để trả lời.

Dữ liệu nội bộ:
{$dbContext}

Tin nhắn khách hàng:
{$message}
PROMPT;
    }

    protected function sanitizeAiReply(string $reply): string
    {
        $reply = trim($reply);
        $reply = preg_replace("/\n{3,}/", "\n\n", $reply);
        return trim((string) $reply);
    }

    protected function isGenericAiError(string $reply): bool
    {
        $normalized = mb_strtolower(trim($reply));

        return $normalized === ''
            || str_contains($normalized, 'ai đang bận')
            || str_contains($normalized, 'ai chua tra loi')
            || str_contains($normalized, 'chua cau hinh')
            || str_contains($normalized, 'ban thu lai sau')
            || str_contains($normalized, 'quota')
            || str_contains($normalized, 'billing')
            || str_contains($normalized, 'api key')
            || str_contains($normalized, 'not found')
            || str_contains($normalized, 'permission denied');
    }
}