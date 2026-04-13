<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MurfFalconService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MurfFalconApiController extends Controller
{

    public function __construct(
        private readonly MurfFalconService $falconService,
    )
    {
    }

    public function getSpeechVoice(Request $request)
    {
        return $this->falconService->getSpeechVoiceList();
    }

    /**
     * Stream TTS audio directly from Falcon API.
     */
    public function streamTTS(Request $request): StreamedResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'language' => 'required|string|in:en,es,fr',
        ]);

        $text = $request->input('text');
        $language = $request->input('language');

        return $this->falconService->stream($text, $language);
    }
}
