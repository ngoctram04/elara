<?php

namespace App\Services\AI;

class ChatbotService
{
    public function __construct(
        protected ChatbotToolService $tools
    ) {}

    public function reply(string $message): array
    {
        $original = trim($message);

        if ($original === '') {
            return $this->text('Bạn hãy nhập nội dung cần tư vấn nhé.');
        }

        $msg = $this->normalize($original);

        if ($this->isGreeting($msg)) {
            return $this->text(
                "Xin chào! Mình là trợ lý tư vấn của ELARA 💄\n" .
                    "Bạn có thể hỏi về sản phẩm, giá, khuyến mãi, phí ship, routine skincare hoặc tình trạng da nhé."
            );
        }

        if ($this->isThanks($msg)) {
            return $this->text('Dạ không có gì ạ 💖 Nếu bạn cần mình gợi ý sản phẩm phù hợp nữa thì cứ nhắn nhé.');
        }

        if ($this->isFarewell($msg)) {
            return $this->text('Cảm ơn bạn đã ghé ELARA nhé 💕 Khi nào cần tư vấn thêm mình luôn sẵn sàng.');
        }

        if ($this->isHumanSupport($msg)) {
            return $this->text('Bạn vui lòng nhắn ở khung chat nhân viên để được hỗ trợ trực tiếp nhanh hơn nhé.');
        }

        if ($this->isPromotionQuestion($msg)) {
            $promo = $this->tools->getPromotions();

            if (!empty($promo['products'])) {
                return [
                    'type' => 'product_list',
                    'reply' => 'Hiện shop đang có một số sản phẩm khuyến mãi nổi bật nè 👇',
                    'products' => $promo['products'],
                ];
            }

            return $this->text('Hiện tại mình chưa thấy sản phẩm khuyến mãi nổi bật nào. Bạn có thể xem thêm tại shop hoặc nhắn nhân viên để được hỗ trợ nhé.');
        }

        if ($this->isMembershipQuestion($msg)) {
            $membership = $this->tools->getMembershipInfo();
            $lines = ['ELARA hiện có các hạng thành viên như sau:'];

            foreach ($membership['levels'] as $level) {
                $benefits = implode(', ', $level['benefits']);
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

            return $this->text('Phí ship thường từ 15.000₫ đến 35.000₫ tùy khu vực. Bạn cho mình xin tỉnh/thành để báo chính xác hơn nhé.');
        }

        if ($this->isRoutineQuestion($msg)) {
            $routine = $this->tools->suggestRoutineByMessage($original);
            $text = "Routine gợi ý cho bạn:\n- " . implode("\n- ", $routine['routine']);
            $text .= "\n\nNếu bạn muốn, mình có thể gợi ý thêm sản phẩm theo từng bước.";
            return $this->text($text);
        }

        if ($this->isSkinConcernQuestion($msg)) {
            $concerns = $this->tools->detectSkinConcern($original);
            $routine = $this->tools->suggestRoutineByMessage($original);

            if (!empty($concerns['concerns'])) {
                $text = 'Mình thấy bạn đang quan tâm đến: ' . implode(', ', $concerns['concerns']) . ".\n";
                $text .= "Routine cơ bản mình gợi ý là:\n- " . implode("\n- ", $routine['routine']);
                $text .= "\n\nBạn cũng có thể nhắn rõ hơn như “da dầu mụn”, “da khô nhạy cảm” để mình gợi ý sát hơn nhé.";
                return $this->text($text);
            }

            return $this->text('Bạn có thể mô tả rõ hơn tình trạng da như da dầu, da khô, da mụn hay nhạy cảm để mình gợi ý routine phù hợp nhé.');
        }

        $budget = $this->extractBudget($msg);
        $keyword = $this->extractProductKeyword($original);
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
                    'reply' => 'Mình tìm được một số sản phẩm phù hợp với mức giá bạn cần nè 👇',
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
                        'reply' => 'Mình tìm thấy đúng sản phẩm bạn đang cần 👇',
                        'products' => $result['products'],
                    ];
                }

                if (!empty($result['suggestions'])) {
                    return [
                        'type' => 'product_list',
                        'reply' => 'Mình chưa thấy đúng chính xác sản phẩm bạn nhập. Đây là một số gợi ý gần đúng để bạn tham khảo nhé 👇',
                        'products' => $result['suggestions'],
                    ];
                }
            }
        }

        if ($budget['min'] !== null || $budget['max'] !== null) {
            return $this->text(
                "Hiện mình chưa tìm thấy sản phẩm đúng với mức giá bạn cần.\n" .
                    'Bạn có thể thử tăng ngân sách hoặc nhập tên sản phẩm cụ thể hơn nhé.'
            );
        }

        if ($this->isPriceQuestion($msg)) {
            return $this->text('Bạn hãy nhắn tên sản phẩm hoặc nhóm sản phẩm, mình sẽ tìm giá giúp bạn nhé. Ví dụ: “serum”, “nước tẩy trang”, “kem chống nắng dưới 300k”.');
        }

        if ($this->isOrderQuestion($msg)) {
            return $this->text('Nếu bạn cần kiểm tra đơn hàng, bạn hãy vào mục “Đơn hàng của tôi” hoặc nhắn nhân viên để được hỗ trợ nhanh nhé.');
        }

        if ($this->isPolicyQuestion($msg)) {
            $policies = $this->tools->getStorePolicies();

            return $this->text(
                "Một số thông tin nhanh của shop:\n" .
                    "- Ship: {$policies['shipping']}\n" .
                    "- Thành viên: {$policies['membership']}\n" .
                    "- Hỗ trợ: {$policies['support']}"
            );
        }

        return $this->text(
            "Mình chưa tìm thấy đúng sản phẩm bạn cần.\n" .
                'Bạn có thể nhập rõ hơn tên sản phẩm, thương hiệu hoặc mức giá để mình gợi ý chính xác hơn nhé.'
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

    protected function isGreeting(string $msg): bool
    {
        return $this->containsAny($msg, ['xin chao', 'chao', 'hello', 'hi ', ' hi', 'shop oi', 'ad oi']);
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
        return $this->containsAny($msg, ['nhan vien', 'tu van vien', 'nguoi that', 'admin', 'ho tro truc tiep']);
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
        return $this->containsAny($msg, ['routine', 'chu trinh skincare', 'cac buoc skincare', 'skincare co ban']);
    }

    protected function isSkinConcernQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['da dau', 'da kho', 'da mun', 'nhay cam', 'tham', 'nam', 'lao hoa', 'lo chan long', 'thieu am']);
    }

    protected function isPriceQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['bao nhieu tien', 'gia bao nhieu', 'gia', 'tam gia', 'duoi ', 'tren ', 'tu ']);
    }

    protected function isOrderQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['don hang', 'kiem tra don', 'ma don', 'tinh trang don']);
    }

    protected function isPolicyQuestion(string $msg): bool
    {
        return $this->containsAny($msg, ['chinh sach', 'doi tra', 'bao hanh', 'quy dinh']);
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

        // từ 100k đến 200k
        if (preg_match('/tu\s*(\d+)\s*(k|ngan|trieu)?\s*(den|toi)\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $min = $this->parseMoneyValue($m[1], $m[2] ?? null);
            $max = $this->parseMoneyValue($m[4], $m[5] ?? null);
        }

        // 100k đến 200k
        elseif (preg_match('/(\d+)\s*(k|ngan|trieu)?\s*(den|toi)\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $min = $this->parseMoneyValue($m[1], $m[2] ?? null);
            $max = $this->parseMoneyValue($m[4], $m[5] ?? null);
        }

        // khoảng 100k - 200k
        elseif (preg_match('/(\d+)\s*-\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $min = $this->parseMoneyValue($m[1], $m[3] ?? null);
            $max = $this->parseMoneyValue($m[2], $m[3] ?? null);
        }

        // dưới 200k
        elseif (preg_match('/duoi\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
            $max = $this->parseMoneyValue($m[1], $m[2] ?? null);
        }

        // trên 100k
        elseif (preg_match('/tren\s*(\d+)\s*(k|ngan|trieu)?/u', $msg, $m)) {
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
        ];

        foreach ($noisePhrases as $phrase) {
            $cleaned = str_replace($phrase, ' ', $cleaned);
        }

        // xóa cụm ngân sách
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
                'khong'
            ], true);
        });

        return trim(implode(' ', $tokens));
    }

    protected function detectSearchIntent(string $normalizedMessage, string $keyword, array $budget): string
    {
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
        ];

        if (in_array($keyword, $genericKeywords, true)) {
            return 'list';
        }

        $tokenCount = count(array_filter(explode(' ', $keyword)));

        if ($tokenCount <= 3) {
            return 'list';
        }

        return 'exact';
    }

    protected function buildListReply(array $budget): string
    {
        if ($budget['min'] !== null || $budget['max'] !== null) {
            return 'Mình tìm được một số sản phẩm phù hợp với mức giá bạn cần nè 👇';
        }

        return 'Mình gợi ý một số sản phẩm phù hợp để bạn tham khảo nhé 👇';
    }
}