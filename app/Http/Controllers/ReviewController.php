<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    protected $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config('services.yandex.api_key');
    }

    public function parseReviews(Request $request)
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $url = $request->input('url');
            
            // Извлекаем ID организации из URL
            $orgId = $this->extractOrganizationId($url);
            
            if (!$orgId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось извлечь ID организации из ссылки'
                ], 400);
            }

            // Пробуем получить отзывы через разные методы
            $reviews = $this->getReviewsFromMultipleSources($orgId, $url);
            
            if (!empty($reviews)) {
                return response()->json([
                    'success' => true,
                    'data' => $reviews,
                    'count' => count($reviews)
                ]);
            }

            // Если ничего не найдено, возвращаем информативное сообщение
            return response()->json([
                'success' => false,
                'message' => 'Не удалось получить отзывы. Возможно, у организации нет отзывов или требуется настройка API.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Ошибка в parseReviews:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка сервера: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение отзывов из разных источников
     */
    private function getReviewsFromMultipleSources($orgId, $originalUrl)
    {
        // Сначала пробуем получить через API отзывов (если доступно)
        $reviews = $this->getReviewsFromYandexApi($orgId);
        
        if (!empty($reviews)) {
            return $reviews;
        }

        // Если API не работает, пробуем парсинг
        $reviews = $this->getReviewsFromParsing($originalUrl);
        
        if (!empty($reviews)) {
            return $reviews;
        }

        // Если все методы не сработали, возвращаем пустой массив
        return [];
    }

    /**
     * Получение отзывов через официальное API Яндекс.Бизнес
     */
    private function getReviewsFromYandexApi($orgId)
    {
        try {
            // Кэшируем результат на 1 час
            return Cache::remember("yandex_reviews_api_{$orgId}", 3600, function () use ($orgId) {
                // Метод 1: Поиск организации
                $searchResponse = Http::get('https://search-maps.yandex.ru/v1/', [
                    'apikey' => $this->apiKey,
                    'text' => $orgId,
                    'type' => 'biz',
                    'lang' => 'ru_RU',
                    'results' => 1
                ]);

                if ($searchResponse->successful()) {
                    $data = $searchResponse->json();
                    
                    if (!empty($data['features'][0]['properties']['CompanyMetaData'])) {
                        $companyData = $data['features'][0]['properties']['CompanyMetaData'];
                        
                        // Некоторые поля могут содержать ID для запроса отзывов
                        if (isset($companyData['id'])) {
                            // Пробуем получить отзывы через другой эндпоинт
                            return $this->fetchReviewsByCompanyId($companyData['id']);
                        }
                    }
                }

                return [];
            });
        } catch (\Exception $e) {
            Log::warning('Ошибка при получении отзывов через API', [
                'error' => $e->getMessage(),
                'orgId' => $orgId
            ]);
            return [];
        }
    }

    /**
     * Получение отзывов через парсинг страницы
     */
    private function getReviewsFromParsing($url)
    {
        try {
            // Кэшируем результат на 30 минут
            return Cache::remember("yandex_reviews_parsed_" . md5($url), 1800, function () use ($url) {
                // Заголовки для имитации браузера
                $headers = [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                ];

                // Получаем HTML страницы
                $response = Http::withHeaders($headers)
                    ->withOptions([
                        'verify' => false, // Отключаем проверку SSL для тестирования
                        'timeout' => 30,
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    return [];
                }

                $html = $response->body();
                
                // Парсим отзывы из HTML
                return $this->parseReviewsFromHtml($html);
            });
        } catch (\Exception $e) {
            Log::warning('Ошибка при парсинге страницы', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return [];
        }
    }

    /**
     * Парсинг отзывов из HTML
     */
    private function parseReviewsFromHtml($html)
    {
        $reviews = [];
        
        try {
            // Ищем JSON-LD данные (структурированные данные)
            if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
                $jsonData = json_decode($matches[1], true);
                
                if ($jsonData && isset($jsonData['review'])) {
                    foreach ($jsonData['review'] as $review) {
                        $reviews[] = [
                            'author' => $review['author']['name'] ?? 'Аноним',
                            'rating' => $review['reviewRating']['ratingValue'] ?? null,
                            'text' => $review['reviewBody'] ?? '',
                            'date' => $review['datePublished'] ?? null,
                        ];
                    }
                }
            }

            // Если не нашли JSON-LD, пробуем искать отзывы в HTML структуре
            if (empty($reviews)) {
                // Паттерны для поиска отзывов (упрощенно)
                $reviewPatterns = [
                    '/<div[^>]*class="[^"]*business-review-view[^"]*"[^>]*>.*?<div[^>]*class="[^"]*business-review-view__author[^"]*"[^>]*>(.*?)<\/div>.*?<div[^>]*class="[^"]*business-review-view__body[^"]*"[^>]*>(.*?)<\/div>/s',
                    '/<div[^>]*class="[^"]*review[^"]*"[^>]*>.*?<span[^>]*class="[^"]*user-name[^"]*"[^>]*>(.*?)<\/span>.*?<p[^>]*class="[^"]*review-text[^"]*"[^>]*>(.*?)<\/p>/s',
                ];

                foreach ($reviewPatterns as $pattern) {
                    if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            $reviews[] = [
                                'author' => strip_tags($match[1] ?? ''),
                                'text' => strip_tags($match[2] ?? ''),
                                'rating' => $this->extractRatingFromHtml($match[0]),
                                'date' => null,
                            ];
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            Log::warning('Ошибка при парсинге HTML', ['error' => $e->getMessage()]);
        }

        return $reviews;
    }

    /**
     * Извлечение рейтинга из HTML фрагмента
     */
    private function extractRatingFromHtml($html)
    {
        if (preg_match('/class="[^"]*rating[^"]*"[^>]*>(\d+(?:[.,]\d+)?)/', $html, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }
        return null;
    }

    /**
     * Запрос отзывов по ID компании (если доступен эндпоинт)
     */
    private function fetchReviewsByCompanyId($companyId)
    {
        try {
            // Этот эндпоинт может не работать без специального доступа
            $response = Http::withHeaders([
                'Authorization' => 'Api-Key ' . $this->apiKey,
            ])->get("https://api.yandex.ru/business/reviews/v1/companies/{$companyId}/reviews", [
                'page' => 1,
                'pageSize' => 20,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['reviews'])) {
                    return collect($data['reviews'])->map(function ($review) {
                        return [
                            'author' => $review['author']['name'] ?? 'Аноним',
                            'rating' => $review['rating'] ?? null,
                            'text' => $review['text'] ?? '',
                            'date' => $review['date'] ?? null,
                        ];
                    })->toArray();
                }
            }
        } catch (\Exception $e) {
            Log::warning('Ошибка при запросе бизнес-API', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Извлечение ID организации из URL
     */
    private function extractOrganizationId($url)
    {
        $patterns = [
            '/\/org\/([^\/]+)/',
            '/[?&]oid=([^&]+)/',
            '/\/maps\/org\/([^\/]+)/',
            '/\/\-\/([A-Za-z0-9]+)/',
            '/\/maps\/\d+\/([^\/]+)/',
            '/(?:org|company)[\/\-]([a-zA-Z0-9_\-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}