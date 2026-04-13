<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI\Factory;

readonly class OpenAIPromptGenerationService
{
    public function __construct(
        private string $apiKey,
        private string $model = 'gpt-4o',
    )
    {
    }

    public function generatePromptFromImage(UploadedFile $image): string
    {
        try {

            $imageData = base64_encode(file_get_contents($image->getPathname()));
            $mimeType = $image->getMimeType();
            $client = (new Factory())->withApiKey($this->apiKey)->make();
            $response = $client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Analyze this image and generate a detailed, descriptive prompt that could be
                            used to recreate a similar image with AI image generation tools. The prompt should be
                            comprehensive, describing the visual elements, style, composition, lighting, colors, and
                            any other relevant details. Make it detailed enough that someone could use it to generate
                            a similar image. You MUST preserve aspect ratio exact as the original image has or very
                            close to it.'
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:' . $mimeType . ';base64,' . $imageData,
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            Log::info('OpenAI Image Analysis API response', [
                'response' => json_encode($response),
            ]);

            return trim($response->choices[0]->message->content);
        } catch (\Exception $e) {
            Log::error('OpenAI prompt generation error', [
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Prompt generation failed: '.$e->getMessage(), 0, $e);
        }
    }
}
