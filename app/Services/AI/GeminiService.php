<?php

namespace App\Services\AI;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiService
{
    public function isConfigured(): bool
    {
        return filled(config('services.gemini.api_key'));
    }

    public function ask(string $prompt): string
    {
        if (!$this->isConfigured()) {
            return 'Chưa cấu hình Gemini API key.';
        }

        $apiKey = (string) config('services.gemini.api_key');
        $model = (string) config('services.gemini.model', 'gemini-2.0-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            /** @var Response $response */
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'X-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.5,
                        'topP' => 0.9,
                        'topK' => 40,
                        'maxOutputTokens' => 512,
                    ],
                ]);

            if (!$response->successful()) {
                $errorMessage = data_get($response->json(), 'error.message');

                if (is_string($errorMessage) && trim($errorMessage) !== '') {
                    return 'AI đang bận hoặc trả về lỗi: ' . trim($errorMessage);
                }

                return 'AI đang bận, bạn thử lại sau nhé.';
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }

            return 'AI chưa trả lời được.';
        } catch (Throwable $e) {
            return 'AI đang bận, bạn thử lại sau nhé.';
        }
    }

    public function askForCustomer(string $message): string
    {
        return $this->ask($this->buildDefaultPrompt($message));
    }

    protected function buildDefaultPrompt(string $message): string
    {
        return <<<PROMPT
Bạn là trợ lý AI cho website bán mỹ phẩm.

Quy tắc:
- Trả lời bằng tiếng Việt.
- Ngắn gọn, dễ hiểu, thân thiện.
- Không bịa thông tin về giá, tồn kho, chính sách nếu không chắc.
- Nếu là câu hỏi về da nghiêm trọng thì khuyên gặp bác sĩ da liễu.

Câu hỏi của khách hàng:
{$message}
PROMPT;
    }
}