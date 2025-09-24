<?php
/**
 * Text-to-Speech Service - Eleven Labs Integration
 * 
 * Handles audio generation from text content using Eleven Labs API
 * Provides high-quality, natural-sounding voices for blog content
 *
 * @package HMG_AI_Blog_Enhancer
 * @subpackage Services
 * @since 1.0.0
 */

class HMG_AI_TTS_Service {
    /**
     * Eleven Labs API base URL
     */
    private $api_base = 'https://api.elevenlabs.io/v1';
    
    /**
     * Available Eleven Labs voices with IDs
     * Using most popular and natural-sounding voices
     */
    private $voices = array(
        // Professional voices
        'JBFqnCBsd6RMkjVDRZzb' => array(
            'name' => 'George - Professional Male',
            'description' => 'Clear, professional male voice ideal for articles',
            'gender' => 'male',
            'accent' => 'American'
        ),
        'EXAVITQu4vr4xnSDxMaL' => array(
            'name' => 'Sarah - Professional Female', 
            'description' => 'Warm, professional female voice perfect for blog posts',
            'gender' => 'female',
            'accent' => 'American'
        ),
        '21m00Tcm4TlvDq8ikWAM' => array(
            'name' => 'Rachel - News Narrator',
            'description' => 'Clear news narrator voice for informative content',
            'gender' => 'female',
            'accent' => 'American'
        ),
        '2EiwWnXFnvU5JabPnv8n' => array(
            'name' => 'Clyde - Deep Male',
            'description' => 'Deep, authoritative male voice',
            'gender' => 'male',
            'accent' => 'American'
        ),
        'pNInz6obpgDQGcFmaJgB' => array(
            'name' => 'Adam - Conversational Male',
            'description' => 'Natural conversational male voice',
            'gender' => 'male',
            'accent' => 'American'
        ),
        'ThT5KcBeYPX3keUQqHPh' => array(
            'name' => 'Dorothy - British Female',
            'description' => 'Professional British female voice',
            'gender' => 'female',
            'accent' => 'British'
        ),
        'IKne3meq5aSn9XLyUdCD' => array(
            'name' => 'Charlie - Australian Male',
            'description' => 'Friendly Australian male voice',
            'gender' => 'male',
            'accent' => 'Australian'
        ),
        'TX3LPaxmHKxFdv7VOQHJ' => array(
            'name' => 'Liam - Storyteller',
            'description' => 'Engaging storyteller voice for narrative content',
            'gender' => 'male',
            'accent' => 'American'
        )
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize any required services
    }
    
    /**
     * Generate audio from text using Eleven Labs
     *
     * @param string $text The text to convert to speech
     * @param array $options Configuration options
     * @return array Result with audio URL or error
     */
    public function generate_audio($text, $options = array()) {
        $defaults = array(
            'voice' => get_option('hmg_ai_tts_voice', 'EXAVITQu4vr4xnSDxMaL'), // Sarah by default
            'stability' => get_option('hmg_ai_tts_stability', 0.5),
            'similarity_boost' => get_option('hmg_ai_tts_similarity', 0.75),
            'style' => get_option('hmg_ai_tts_style', 0.0),
            'use_speaker_boost' => get_option('hmg_ai_tts_speaker_boost', true),
            'post_id' => 0
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Clean and prepare text
        $text = $this->prepare_text($text);
        
        // Check text length limits (Eleven Labs supports up to 5000 characters per request)
        if (strlen($text) > 5000) {
            return $this->generate_long_audio($text, $options);
        }
        
        // Generate with Eleven Labs
        return $this->generate_elevenlabs_tts($text, $options);
    }
    
    /**
     * Generate audio using Eleven Labs API
     */
    private function generate_elevenlabs_tts($text, $options) {
        $api_key = get_option('hmg_ai_elevenlabs_api_key');
        
        if (!$api_key) {
            return array(
                'error' => true,
                'message' => __('Eleven Labs API key not configured. Please add your API key in settings.', 'hmg-ai-blog-enhancer')
            );
        }
        
        // Get voice ID
        $voice_id = $options['voice'];
        
        // Validate voice exists
        if (!isset($this->voices[$voice_id])) {
            // Use default voice if invalid
            $voice_id = 'EXAVITQu4vr4xnSDxMaL'; // Sarah
        }
        
        // Prepare request data for Eleven Labs
        $request_data = array(
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2', // Latest and best model
            'voice_settings' => array(
                'stability' => floatval($options['stability']),
                'similarity_boost' => floatval($options['similarity_boost']),
                'style' => floatval($options['style']),
                'use_speaker_boost' => (bool)$options['use_speaker_boost']
            )
        );
        
        // Make API request to Eleven Labs
        $response = wp_remote_post(
            $this->api_base . '/text-to-speech/' . $voice_id,
            array(
                'headers' => array(
                    'Accept' => 'audio/mpeg',
                    'Content-Type' => 'application/json',
                    'xi-api-key' => $api_key
                ),
                'body' => json_encode($request_data),
                'timeout' => 60 // Longer timeout for audio generation
            )
        );
        
        if (is_wp_error($response)) {
            error_log('HMG AI Eleven Labs Error: ' . $response->get_error_message());
            return array(
                'error' => true,
                'message' => __('Failed to generate audio with Eleven Labs', 'hmg-ai-blog-enhancer')
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error_data = json_decode($body, true);
            
            error_log('HMG AI Eleven Labs API Error: ' . json_encode($error_data));
            
            // Handle specific error codes
            if ($response_code === 401) {
                return array(
                    'error' => true,
                    'message' => __('Invalid Eleven Labs API key. Please check your settings.', 'hmg-ai-blog-enhancer')
                );
            } elseif ($response_code === 422) {
                return array(
                    'error' => true,
                    'message' => __('Invalid request to Eleven Labs. Text may be too long or contain invalid characters.', 'hmg-ai-blog-enhancer')
                );
            } elseif ($response_code === 429) {
                return array(
                    'error' => true,
                    'message' => __('Eleven Labs rate limit exceeded. Please try again later.', 'hmg-ai-blog-enhancer')
                );
            }
            
            return array(
                'error' => true,
                'message' => $error_data['detail']['message'] ?? __('Eleven Labs API error occurred', 'hmg-ai-blog-enhancer')
            );
        }
        
        // Get the audio data
        $audio_data = wp_remote_retrieve_body($response);
        
        if ($audio_data) {
            // Save audio file
            $audio_url = $this->save_audio_file($audio_data, $options['post_id'], false);
            
            if ($audio_url) {
                // Record usage if auth service is available
                if (class_exists('HMG_AI_Auth_Service')) {
                    $auth_service = new HMG_AI_Auth_Service();
                    $char_count = strlen($text);
                    
                    // Eleven Labs pricing (approximate)
                    // $0.30 per 1,000 characters for standard voices
                    $cost = ($char_count / 1000) * 0.30;
                    
                    $auth_service->record_usage(
                        $options['post_id'],
                        'audio',
                        'elevenlabs_tts',
                        1,
                        $char_count,
                        $cost
                    );
                }
                
                // Get voice info
                $voice_info = $this->voices[$voice_id];
                
                return array(
                    'success' => true,
                    'audio_url' => $audio_url,
                    'duration' => $this->estimate_duration($text),
                    'voice' => $voice_info['name'],
                    'provider' => 'Eleven Labs',
                    'model' => 'eleven_multilingual_v2'
                );
            }
        }
        
        return array(
            'error' => true,
            'message' => __('Failed to process audio from Eleven Labs', 'hmg-ai-blog-enhancer')
        );
    }
    
    /**
     * Generate audio for long text by chunking
     */
    private function generate_long_audio($text, $options) {
        // Split text into manageable chunks
        $chunks = $this->split_text_into_chunks($text, 4500);
        $audio_files = array();
        $total_duration = array('seconds' => 0, 'formatted' => '00:00');
        
        foreach ($chunks as $index => $chunk) {
            // Add slight pause between chunks for natural flow
            if ($index > 0) {
                $chunk = ' ' . $chunk;
            }
            
            $result = $this->generate_elevenlabs_tts($chunk, $options);
            
            if (isset($result['audio_url'])) {
                $audio_files[] = $result['audio_url'];
                $total_duration['seconds'] += $result['duration']['seconds'] ?? 0;
            } else {
                // If any chunk fails, return error
                return $result;
            }
        }
        
        // Format total duration
        $total_duration['formatted'] = gmdate('i:s', $total_duration['seconds']);
        
        // For now, return the first chunk with a note
        // In the future, could merge audio files server-side
        return array(
            'success' => true,
            'audio_url' => $audio_files[0],
            'chunks' => $audio_files,
            'total_chunks' => count($audio_files),
            'duration' => $total_duration,
            'message' => sprintf(__('Generated %d audio segments. Showing first segment.', 'hmg-ai-blog-enhancer'), count($audio_files)),
            'voice' => $this->voices[$options['voice']]['name'] ?? 'Unknown',
            'provider' => 'Eleven Labs'
        );
    }
    
    /**
     * Save audio file to WordPress uploads
     */
    private function save_audio_file($audio_data, $post_id = 0, $is_base64 = true) {
        $upload_dir = wp_upload_dir();
        $audio_dir = $upload_dir['basedir'] . '/hmg-ai-audio';
        
        // Create directory if it doesn't exist
        if (!file_exists($audio_dir)) {
            wp_mkdir_p($audio_dir);
        }
        
        // Generate unique filename
        $filename = 'elevenlabs-audio-' . ($post_id > 0 ? $post_id . '-' : '') . time() . '.mp3';
        $filepath = $audio_dir . '/' . $filename;
        
        // Decode if base64, otherwise use raw data
        if ($is_base64) {
            $audio_content = base64_decode($audio_data);
        } else {
            $audio_content = $audio_data;
        }
        
        if (file_put_contents($filepath, $audio_content)) {
            // Return URL
            return $upload_dir['baseurl'] . '/hmg-ai-audio/' . $filename;
        }
        
        return false;
    }
    
    /**
     * Prepare text for TTS
     */
    private function prepare_text($text) {
        // Remove HTML tags
        $text = wp_strip_all_tags($text);
        
        // Convert special characters
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove shortcodes
        $text = strip_shortcodes($text);
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Add natural pauses for better speech
        $text = preg_replace('/\. ([A-Z])/', '. $1', $text); // Ensure space after periods
        $text = preg_replace('/\? ([A-Z])/', '? $1', $text); // Ensure space after questions
        $text = preg_replace('/! ([A-Z])/', '! $1', $text); // Ensure space after exclamations
        
        // Replace common abbreviations for better pronunciation
        $text = str_replace('Dr.', 'Doctor', $text);
        $text = str_replace('Mr.', 'Mister', $text);
        $text = str_replace('Mrs.', 'Missus', $text);
        $text = str_replace('Ms.', 'Miss', $text);
        $text = str_replace('Jr.', 'Junior', $text);
        $text = str_replace('Sr.', 'Senior', $text);
        
        return trim($text);
    }
    
    /**
     * Split text into chunks at sentence boundaries
     */
    private function split_text_into_chunks($text, $max_length = 4500) {
        $chunks = array();
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $current_chunk = '';
        
        foreach ($sentences as $sentence) {
            // If a single sentence is longer than max length, split it
            if (strlen($sentence) > $max_length) {
                if ($current_chunk) {
                    $chunks[] = trim($current_chunk);
                    $current_chunk = '';
                }
                
                // Split long sentence at commas or semicolons
                $parts = preg_split('/[,;]\s*/', $sentence);
                foreach ($parts as $part) {
                    if (strlen($current_chunk) + strlen($part) < $max_length) {
                        $current_chunk .= ($current_chunk ? ', ' : '') . $part;
                    } else {
                        if ($current_chunk) {
                            $chunks[] = trim($current_chunk);
                        }
                        $current_chunk = $part;
                    }
                }
            } elseif (strlen($current_chunk) + strlen($sentence) + 1 < $max_length) {
                $current_chunk .= ($current_chunk ? ' ' : '') . $sentence;
            } else {
                if ($current_chunk) {
                    $chunks[] = trim($current_chunk);
                }
                $current_chunk = $sentence;
            }
        }
        
        if ($current_chunk) {
            $chunks[] = trim($current_chunk);
        }
        
        return $chunks;
    }
    
    /**
     * Estimate audio duration based on text
     */
    private function estimate_duration($text) {
        // Average speaking rate is about 150-160 words per minute
        // Eleven Labs voices tend to speak at a natural pace
        $words = str_word_count($text);
        $minutes = $words / 155;
        $seconds = round($minutes * 60);
        
        return array(
            'seconds' => $seconds,
            'formatted' => gmdate('i:s', $seconds)
        );
    }
    
    /**
     * Get available Eleven Labs voices
     */
    public function get_available_voices($provider = 'elevenlabs') {
        return $this->voices;
    }
    
    /**
     * Test Eleven Labs connection
     */
    public function test_connection($provider = 'elevenlabs') {
        $test_text = 'Hello, this is a test of the Eleven Labs text to speech system.';
        $result = $this->generate_audio($test_text, array(
            'voice' => 'EXAVITQu4vr4xnSDxMaL' // Use Sarah for test
        ));
        
        return !isset($result['error']);
    }
    
    /**
     * Get voice details
     */
    public function get_voice_details($voice_id) {
        return $this->voices[$voice_id] ?? null;
    }
    
    /**
     * Get voice models (for future expansion)
     */
    public function get_voice_models() {
        return array(
            'eleven_multilingual_v2' => 'Multilingual v2 (Latest)',
            'eleven_monolingual_v1' => 'English v1 (Fast)',
            'eleven_turbo_v2' => 'Turbo v2 (Low Latency)'
        );
    }
}