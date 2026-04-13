# Laravel AI Assistant

A comprehensive Laravel-based AI-powered application that integrates multiple AI services for content creation, translation, and multimedia processing. This application leverages OpenAI's GPT models, Whisper for speech recognition, and Murf.ai's Falcon for text-to-speech synthesis.

## Features

### 🔐 Authentication
- User registration and login
- API token authentication using Laravel Sanctum
- Email verification support

### 📝 Posts Management
- Create, read, update, and delete posts
- User-specific post ownership
- RESTful API endpoints

### 🖼️ AI Image Prompt Generation
- Upload images and generate detailed descriptive prompts using OpenAI's GPT-4 Vision
- Store generation history with metadata
- Search and filter generated prompts
- Secure file storage with sanitized filenames

### 🎙️ Audio Translation Pipeline
- Upload audio files for transcription using OpenAI Whisper
- Automatic language detection or manual language specification
- Translate transcribed text using OpenAI GPT models
- Generate streaming text-to-speech audio using Murf.ai Falcon API
- Support for multiple languages (English, Spanish, French)

### 🔊 Text-to-Speech Streaming
- Real-time audio streaming from text
- Multiple voice options per language
- Low-latency audio generation
- MP3 format output

## Technology Stack

- **Framework**: Laravel 13.x
- **PHP**: 8.3+
- **Database**: SQLite (configurable)
- **AI Services**:
  - OpenAI GPT-4o (Vision & Chat)
  - OpenAI Whisper (Speech Recognition)
  - Murf.ai Falcon (Text-to-Speech)
- **Authentication**: Laravel Sanctum
- **API Documentation**: Laravel Scramble
- **Testing**: Pest PHP
- **Deployment**: Docker + Nginx

## Installation

### Prerequisites
- PHP 8.3 or higher
- Composer
- Docker & Docker Compose (for containerized deployment)

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-ai
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Environment Variables**
   Update your `.env` file with the required API keys:

   ```env
   # Database
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite

   # OpenAI Configuration
   OPENAI_API_KEY=your_openai_api_key_here
   OPENAI_API_URL=https://api.openai.com/v1

   # Murf.ai Configuration
   MURF_API_KEY=your_murf_api_key_here
   MURF_API_URL=https://global.api.murf.ai/v1

   # Application
   APP_NAME="Laravel AI Assistant"
   APP_URL=http://localhost:8000
   ```

5. **Database Setup**
   ```bash
   php artisan migrate
   ```

6. **Storage Link (for file uploads)**
   ```bash
   php artisan storage:link
   ```

7. **Start Development Server**
   ```bash
   composer run dev
   ```

   This command will start:
   - Laravel server on `http://localhost:8000`
   - Queue worker
   - Log monitoring

### Docker Deployment

1. **Build and run containers**
   ```bash
   docker-compose up --build
   ```

2. **Access the application**
   - Application: `http://localhost:83`
   - PHP-FPM: Container `laravel-ai`
   - Nginx: Container `laravel-ai-nginx`

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout

### Posts (Authenticated)
- `GET /api/v1/posts` - List posts
- `POST /api/v1/posts` - Create post
- `GET /api/v1/posts/{id}` - Show post
- `PUT /api/v1/posts/{id}` - Update post
- `DELETE /api/v1/posts/{id}` - Delete post

### Image Prompt Generation (Authenticated)
- `GET /api/v1/prompt-generations` - List user's image generations
- `POST /api/v1/prompt-generations` - Generate prompt from uploaded image

### Translation (Authenticated)
- `POST /api/v1/translations` - Upload audio, get transcription, translation, and TTS streaming URL

### Text-to-Speech (Authenticated)
- `GET /api/v1/tts/stream` - Stream audio from text
- `GET /api/v1/speech-voices` - Get available voices

## Usage Examples

### Generate Image Prompt
```bash
curl -X POST http://localhost:8000/api/v1/prompt-generations \
  -H "Authorization: Bearer {your-token}" \
  -F "image=@/path/to/image.jpg"
```

### Audio Translation
```bash
curl -X POST http://localhost:8000/api/v1/translations \
  -H "Authorization: Bearer {your-token}" \
  -F "audio=@/path/to/audio.mp3" \
  -F "source_language=en" \
  -F "target_language=es"
```

### Text-to-Speech Streaming
```bash
curl "http://localhost:8000/api/v1/tts/stream?text=Hello%20World&language=en" \
  -H "Authorization: Bearer {your-token}" \
  --output speech.mp3
```

## Testing

Run the test suite using Pest PHP:

```bash
composer run test
```

## API Documentation

API documentation is automatically generated using Laravel Scramble. Access it at `/docs` when the application is running.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue on the GitHub repository or contact the development team.
