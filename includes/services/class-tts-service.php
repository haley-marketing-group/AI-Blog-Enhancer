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
        add_action('init', array($this, 'maybe_refresh_voice_cache'));
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
        $text_length = strlen($text);
        if ($text_length > 5000) {
            // For now, truncate to 5000 characters with ellipsis
            // TODO: Implement chunking for longer texts in a future update
            $text = substr($text, 0, 4997) . '...';
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
        
        // Validate API key format (should be at least 32 characters)
        if (strlen($api_key) < 32) {
            return array(
                'error' => true,
                'message' => __('Invalid Eleven Labs API key format. Please check your API key in settings.', 'hmg-ai-blog-enhancer')
            );
        }
        
        // Get voice ID
        $voice_id = $options['voice'];
        
        // Check text length (Eleven Labs has character limits)
        $text_length = strlen($text);
        if ($text_length > 5000) {
            // For now, truncate to 5000 chars with ellipsis
            // TODO: Implement proper chunking
            $text = substr($text, 0, 4997) . '...';
        }
        
        // Get available voices to validate
        $available_voices = $this->get_available_voices();
        
        // Validate voice exists
        if (!isset($available_voices[$voice_id])) {
            // Use default voice if invalid
            $voice_id = 'EXAVITQu4vr4xnSDxMaL'; // Sarah
            // If default not available, use first available voice
            if (!isset($available_voices[$voice_id]) && !empty($available_voices)) {
                $voice_id = array_key_first($available_voices);
            }
        }
        
        // Prepare request data for Eleven Labs
        $request_data = array(
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2', // Latest and best model
            'voice_settings' => array(
                'stability' => floatval($options['stability'] ?? 0.5),
                'similarity_boost' => floatval($options['similarity_boost'] ?? 0.75),
                'style' => floatval($options['style'] ?? 0.0),
                'use_speaker_boost' => (bool)($options['speaker_boost'] ?? true)  // Fixed key name
            ),
            'output_format' => 'mp3_44100' // Standard MP3 format for better compatibility
        );
        
        // Log request details for debugging
        error_log('HMG AI Eleven Labs - Request URL: ' . $this->api_base . '/text-to-speech/' . $voice_id);
        error_log('HMG AI Eleven Labs - Request Data: ' . json_encode(array(
            'text_length' => strlen($text),
            'model_id' => $request_data['model_id'],
            'voice_settings' => $request_data['voice_settings'],
            'output_format' => $request_data['output_format']
        )));
        
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
                'timeout' => 120, // Increased timeout for longer texts
                'sslverify' => false
            )
        );
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('HMG AI Eleven Labs Error: ' . $error_message);
            error_log('HMG AI Eleven Labs - Request URL: ' . $this->api_base . '/text-to-speech/' . $voice_id);
            error_log('HMG AI Eleven Labs - API Key (first 10 chars): ' . substr($api_key, 0, 10) . '...');
            
            // Check if it's a timeout error
            if (strpos($error_message, 'cURL error 28') !== false || strpos($error_message, 'timed out') !== false) {
                error_log('HMG AI Eleven Labs - Timeout detected');
                
                // Return WordPress Studio specific error
                return array(
                    'error' => true,
                    'message' => __('Connection to Eleven Labs timed out. This is likely due to WordPress Studio blocking external API connections. The audio generation will work once deployed to a production server.', 'hmg-ai-blog-enhancer')
                );
            }
            
            return array(
                'error' => true,
                'message' => sprintf(__('Failed to generate audio with Eleven Labs: %s', 'hmg-ai-blog-enhancer'), $error_message)
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error_data = json_decode($body, true);
            
            error_log('HMG AI Eleven Labs API Error Response Code: ' . $response_code);
            error_log('HMG AI Eleven Labs API Error Body: ' . $body);
            error_log('HMG AI Eleven Labs API Error Data: ' . json_encode($error_data));
            
            // Handle specific error codes as per Eleven Labs documentation
            if ($response_code === 401) {
                return array(
                    'error' => true,
                    'message' => __('Authentication failed: Invalid Eleven Labs API key. Please check your settings.', 'hmg-ai-blog-enhancer')
                );
            } elseif ($response_code === 422) {
                // Unprocessable Entity - typically invalid voice ID or model
                $detail = isset($error_data['detail']) ? 
                         (is_array($error_data['detail']) ? json_encode($error_data['detail']) : $error_data['detail']) : 
                         'Invalid voice or model specified';
                return array(
                    'error' => true,
                    'message' => sprintf(__('Invalid request: %s', 'hmg-ai-blog-enhancer'), $detail)
                );
            } elseif ($response_code === 429) {
                // Rate limit or quota exceeded
                return array(
                    'error' => true,
                    'message' => __('Eleven Labs quota exceeded or rate limit reached. Check your usage at elevenlabs.io/app/usage', 'hmg-ai-blog-enhancer')
                );
            } elseif ($response_code === 400) {
                // Bad request - check for specific error types
                $message = 'Bad request';
                if (isset($error_data['detail'])) {
                    if (is_string($error_data['detail'])) {
                        $message = $error_data['detail'];
                    } elseif (isset($error_data['detail']['message'])) {
                        $message = $error_data['detail']['message'];
                    }
                }
                return array(
                    'error' => true,
                    'message' => sprintf(__('Request error: %s', 'hmg-ai-blog-enhancer'), $message)
                );
            } elseif ($response_code === 500 || $response_code === 503) {
                // Server error or service unavailable
                return array(
                    'error' => true,
                    'message' => __('Eleven Labs service is temporarily unavailable. Please try again in a few minutes.', 'hmg-ai-blog-enhancer')
                );
            }
            
            $message = isset($error_data['detail']) ? 
                      (is_string($error_data['detail']) ? $error_data['detail'] : json_encode($error_data['detail'])) : 
                      'Unknown error (Code: ' . $response_code . ')';
                      
            return array(
                'error' => true,
                'message' => sprintf(__('Eleven Labs API error: %s', 'hmg-ai-blog-enhancer'), $message)
            );
        }
        
        // Get the audio data
        $audio_data = wp_remote_retrieve_body($response);
        
        if (empty($audio_data)) {
            return array(
                'error' => true,
                'message' => __('No audio data received from Eleven Labs', 'hmg-ai-blog-enhancer')
            );
        }
        
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
                $available_voices = $this->get_available_voices();
                $voice_info = isset($available_voices[$voice_id]) ? $available_voices[$voice_id] : array('name' => 'Unknown Voice');
                
                // Important: Do NOT return 'success' => true as it causes issues with error checking
                // The AJAX handler checks for 'error' => true, not 'success'
                return array(
                    'audio_url' => $audio_url,
                    'duration' => $this->estimate_duration($text),
                    'voice' => $voice_info['name'],
                    'provider' => 'Eleven Labs',
                    'model' => 'eleven_multilingual_v2'
                );
            } else {
                return array(
                    'error' => true,
                    'message' => __('Failed to save audio file. Please check server permissions for wp-content/uploads/hmg-ai-audio/', 'hmg-ai-blog-enhancer')
                );
            }
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
        
        // Check if we have any audio files
        if (empty($audio_files)) {
            return array(
                'error' => true,
                'message' => __('Failed to generate audio chunks', 'hmg-ai-blog-enhancer')
            );
        }
        
        // Format total duration
        $total_duration['formatted'] = gmdate('i:s', $total_duration['seconds']);
        
        // Get voice info
        $available_voices = $this->get_available_voices();
        $voice_info = isset($available_voices[$options['voice']]) ? $available_voices[$options['voice']] : array('name' => 'Unknown Voice');
        
        // For now, return the first chunk with a note
        // In the future, could merge audio files server-side
        // Important: Do NOT return 'success' => true as it causes issues with error checking
        return array(
            'audio_url' => $audio_files[0],
            'chunks' => $audio_files,
            'total_chunks' => count($audio_files),
            'duration' => $total_duration,
            'message' => sprintf(__('Generated %d audio segments. Showing first segment.', 'hmg-ai-blog-enhancer'), count($audio_files)),
            'voice' => $voice_info['name'],
            'provider' => 'Eleven Labs'
        );
    }
    
    /**
     * Save audio file to WordPress uploads
     */
    private function save_audio_file($audio_data, $post_id = 0, $is_base64 = true) {
        $upload_dir = wp_upload_dir();
        
        // Check if there's an error with the upload directory
        if (!empty($upload_dir['error'])) {
            return false;
        }
        
        $audio_dir = $upload_dir['basedir'] . '/hmg-ai-audio';
        
        // Create directory if it doesn't exist
        if (!file_exists($audio_dir)) {
            if (!wp_mkdir_p($audio_dir)) {
                return false;
            }
            // Set proper permissions
            @chmod($audio_dir, 0755);
        }
        
        // Generate unique filename with microseconds for better uniqueness
        $filename = 'elevenlabs-audio-' . ($post_id > 0 ? $post_id . '-' : '') . time() . '-' . substr(microtime(), 2, 6) . '.mp3';
        $filepath = $audio_dir . '/' . $filename;
        
        // Decode if base64, otherwise use raw data
        if ($is_base64) {
            $audio_content = base64_decode($audio_data);
            if ($audio_content === false) {
                return false;
            }
        } else {
            $audio_content = $audio_data;
        }
        
        // Check if we have valid audio content
        if (empty($audio_content)) {
            return false;
        }
        
        // Attempt to write the file
        $bytes_written = @file_put_contents($filepath, $audio_content);
        
        if ($bytes_written === false || $bytes_written === 0) {
            if ($bytes_written === 0) {
                @unlink($filepath); // Remove empty file
            }
            return false;
        }
        
        // Set proper file permissions
        @chmod($filepath, 0644);
        
        // Verify file was created and has content
        clearstatcache(true, $filepath);
        if (!file_exists($filepath) || filesize($filepath) === 0) {
            @unlink($filepath); // Remove empty file if it exists
            return false;
        }
        
        // Return URL
        return $upload_dir['baseurl'] . '/hmg-ai-audio/' . $filename;
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
     * Fetches from API if not cached or cache is expired
     */
    public function get_available_voices($provider = 'elevenlabs') {
        // Try to get cached voices first
        $cached_voices = get_transient('hmg_ai_elevenlabs_voices');
        
        if ($cached_voices !== false) {
            return $cached_voices;
        }
        
        // If no cache or expired, fetch from API
        $api_voices = $this->fetch_voices_from_api();
        
        if (!empty($api_voices)) {
            // Cache for 24 hours
            set_transient('hmg_ai_elevenlabs_voices', $api_voices, DAY_IN_SECONDS);
            return $api_voices;
        }
        
        // Fallback to hardcoded voices if API fails
        return $this->voices;
    }
    
    /**
     * Fetch voices from Eleven Labs API
     */
    private function fetch_voices_from_api() {
        $api_key = get_option('hmg_ai_elevenlabs_api_key');
        
        if (!$api_key) {
            error_log('HMG AI Eleven Labs - Cannot fetch voices: API key not configured');
            return array();
        }
        
        error_log('HMG AI Eleven Labs - Fetching available voices from API');
        
        $response = wp_remote_get(
            $this->api_base . '/voices',
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'xi-api-key' => $api_key
                ),
                'timeout' => 15, // Increased timeout
                'sslverify' => false,
                'blocking' => true,
                'httpversion' => '1.1'
            )
        );
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('HMG AI Eleven Labs - Failed to fetch voices: ' . $error_message);
            
            // Check for timeout
            if (strpos($error_message, 'cURL error 28') !== false || strpos($error_message, 'timed out') !== false) {
                error_log('HMG AI Eleven Labs - Timeout when fetching voices. This may be due to network restrictions.');
            }
            
            return array();
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            error_log('HMG AI Eleven Labs - Failed to fetch voices. Response code: ' . $response_code);
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['voices']) || !is_array($data['voices'])) {
            error_log('HMG AI Eleven Labs - Invalid voices response format');
            return array();
        }
        
        // Format voices for our plugin
        $formatted_voices = array();
        
        foreach ($data['voices'] as $voice) {
            if (!isset($voice['voice_id']) || !isset($voice['name'])) {
                continue;
            }
            
            // Extract useful information
            $labels = isset($voice['labels']) ? $voice['labels'] : array();
            $accent = isset($labels['accent']) ? $labels['accent'] : 'unknown';
            $gender = isset($labels['gender']) ? $labels['gender'] : 'unknown';
            $age = isset($labels['age']) ? $labels['age'] : '';
            $use_case = isset($labels['use case']) ? $labels['use case'] : '';
            
            // Build description
            $description_parts = array();
            if ($gender !== 'unknown') {
                $description_parts[] = ucfirst($gender);
            }
            if ($age) {
                $description_parts[] = $age;
            }
            if ($accent !== 'unknown') {
                $description_parts[] = ucfirst($accent) . ' accent';
            }
            if ($use_case) {
                $description_parts[] = 'for ' . $use_case;
            }
            
            $description = !empty($description_parts) ? implode(', ', $description_parts) : 'AI Voice';
            
            // Check if it's a premade or cloned voice
            $category = isset($voice['category']) ? $voice['category'] : 'premade';
            $voice_type = ($category === 'cloned') ? ' (Cloned)' : '';
            
            $formatted_voices[$voice['voice_id']] = array(
                'name' => $voice['name'] . $voice_type,
                'description' => $description,
                'gender' => $gender,
                'accent' => $accent,
                'category' => $category,
                'preview_url' => isset($voice['preview_url']) ? $voice['preview_url'] : '',
                'labels' => $labels
            );
        }
        
        error_log('HMG AI Eleven Labs - Successfully fetched ' . count($formatted_voices) . ' voices from API');
        
        return $formatted_voices;
    }
    
    /**
     * Maybe refresh voice cache
     * Called on init to refresh cache if needed
     */
    public function maybe_refresh_voice_cache() {
        // Only refresh on admin pages to avoid unnecessary API calls
        if (!is_admin()) {
            return;
        }
        
        // Check if we should refresh (e.g., when on plugin settings page)
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && strpos($screen->id, 'hmg-ai') !== false) {
            // Force refresh if cache is older than 12 hours on plugin pages
            $last_refresh = get_transient('hmg_ai_voices_last_refresh');
            if (!$last_refresh || (time() - $last_refresh) > (12 * HOUR_IN_SECONDS)) {
                delete_transient('hmg_ai_elevenlabs_voices');
                $this->get_available_voices();
                set_transient('hmg_ai_voices_last_refresh', time(), WEEK_IN_SECONDS);
            }
        }
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
        $available_voices = $this->get_available_voices();
        return isset($available_voices[$voice_id]) ? $available_voices[$voice_id] : null;
    }
    
    /**
     * Clear voice cache
     * Useful when API key changes or manual refresh needed
     */
    public function clear_voice_cache() {
        delete_transient('hmg_ai_elevenlabs_voices');
        delete_transient('hmg_ai_voices_last_refresh');
        error_log('HMG AI Eleven Labs - Voice cache cleared');
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