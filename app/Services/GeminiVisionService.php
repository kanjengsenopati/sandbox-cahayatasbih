<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiVisionService
{
    /**
     * Call Gemini API to extract nominal transfer amount from an image.
     *
     * @param string $imagePath Absolute path or URL to the image
     * @return float|null
     */
    public static function extractNominalFromImage(string $imagePath): ?float
    {
        $apiKey = env('GEMINI_API_KEY');
        
        if (empty($apiKey)) {
            Log::error('Gemini API Key is missing. Cannot perform auto-checking.');
            return null;
        }

        // Determine if it's a URL or local path
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $imageData = file_get_contents($imagePath);
        } else {
            // Local path (e.g., storage/app/public/proofs/...)
            $fullPath = storage_path('app/public/' . str_replace('storage/', '', $imagePath));
            if (!file_exists($fullPath)) {
                Log::error('Proof image not found at path: ' . $fullPath);
                return null;
            }
            $imageData = file_get_contents($fullPath);
        }

        if (!$imageData) {
            Log::error('Failed to read image data for Gemini Vision.');
            return null;
        }

        $base64Image = base64_encode($imageData);
        // Basic mime type detection based on extension or signature
        $mimeType = 'image/jpeg';
        if (str_contains(strtolower($imagePath), '.png')) {
            $mimeType = 'image/png';
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $prompt = "Tolong perhatikan gambar bukti transfer bank/pembayaran ini. Tugas kamu adalah mengekstrak *nominal uang yang berhasil ditransfer*. Abaikan tanggal, nomor rekening, atau teks lainnya. Temukan total uang yang ditransfer. Jika ada, kembalikan HANYA angkanya saja tanpa pemisah ribuan (titik/koma) dan tanpa simbol mata uang (Rp). Contoh: jika di gambar tertulis Rp 150.000, kembalikan 150000. Jika tertulis 1.050.215, kembalikan 1050215. JIKA KAMU TIDAK BISA MENEMUKAN NOMINAL, kembalikan teks 'FAILED'. Ingat, HANYA ANGKA ATAU KATA 'FAILED'.";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 50,
            ]
        ];

        try {
            $response = Http::timeout(30)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $textResult = trim($textResult);
                
                Log::info("Gemini OCR Result: " . $textResult);

                if ($textResult === 'FAILED' || empty($textResult)) {
                    return null;
                }

                // Clean the string further just in case Gemini returns something like "150000 \n"
                $cleanNumber = preg_replace('/[^0-9]/', '', $textResult);
                
                if (is_numeric($cleanNumber)) {
                    return (float) $cleanNumber;
                }
            } else {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
        }

        return null;
    }
}
