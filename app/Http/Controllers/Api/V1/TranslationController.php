<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranslationRequest;
use App\Services\MurfFalconService;
use App\Services\OpenAITranslationService;
use App\Services\OpenAIWhisperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

//use Inertia\Inertia;
//use Inertia\Response as InertiaResponse;

class TranslationController extends Controller
{
    public function __construct(
        private readonly OpenAIWhisperService $whisperService,
        private readonly OpenAITranslationService $translationService,
    ) {}

    /**
     * Process audio and return translation with streaming URL.
     */
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $audioFile = $request->file('audio');
            $sourceLanguage = $request->input('source_language');
            $targetLanguage = $request->input('target_language');

            // Step 1: Transcribe using Whisper
            $transcribeStartTime = microtime(true);
            $transcription = $this->whisperService->transcribe($audioFile, $sourceLanguage);
            $transcribeTime = (int) ((microtime(true) - $transcribeStartTime) * 1000);
            $originalText = $transcription['text'];
            $detectedLanguage = $transcription['language'] ?? $sourceLanguage ?? 'auto';

            if (empty($originalText)) {
                return response()->json([
                    'error' => 'Could not transcribe audio. Please ensure the audio is clear and contains speech.',
                ], 400);
            }

            // Step 2: Translate using GPT
            $translateStartTime = microtime(true);
            $translatedText = $this->translationService->translate($originalText, $targetLanguage);
            $translateTime = (int) ((microtime(true) - $translateStartTime) * 1000);

            // Step 3: Generate streaming URL for Murf Falcon TTS
            $streamingUrl = '/tts/stream?'.http_build_query([
                    'text' => $translatedText,
                    'language' => $targetLanguage,
                ]);
            $synthesizeTime = 0; // TTS happens on-demand - TTFB tracked on frontend

            // Step 4: Calculate total processing time
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            return response()->json([
                'success' => true,
                'translation' => [
                    'original_text' => $originalText,
                    'translated_text' => $translatedText,
                    'source_language' => $detectedLanguage,
                    'target_language' => $targetLanguage,
                    'streaming_url' => $streamingUrl,
                    'processing_time' => $processingTime,
                    'api_timings' => [
                        'transcribe' => $transcribeTime,
                        'translate' => $translateTime,
                        'synthesize' => $synthesizeTime,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
