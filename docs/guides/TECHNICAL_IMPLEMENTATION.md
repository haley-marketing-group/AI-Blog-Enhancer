# Technical Implementation Guide
## HMG AI Blog Enhancer WordPress Plugin

---

## 🏗️ Plugin Architecture

### Directory Structure
```
hmg-ai-blog-enhancer/
├── hmg-ai-blog-enhancer.php          # Main plugin file
├── includes/
│   ├── class-hmg-ai-core.php         # Core plugin class
│   ├── class-hmg-ai-admin.php        # Admin interface
│   ├── class-hmg-ai-public.php       # Public-facing functionality
│   ├── services/
│   │   ├── class-gemini-service.php  # Gemini API integration
│   │   ├── class-tts-service.php     # Text-to-speech service
│   │   ├── class-auth-service.php    # Authentication service
│   │   └── class-usage-tracker.php   # Usage tracking
│   ├── generators/
│   │   ├── class-takeaways-generator.php
│   │   ├── class-faq-generator.php
│   │   └── class-toc-generator.php
│   ├── shortcodes/
│   │   └── class-ai-shortcodes.php   # All shortcode implementations
│   └── admin/
│       ├── partials/                 # Admin view templates
│       ├── css/                      # Admin styles
│       └── js/                       # Admin JavaScript
├── public/
│   ├── css/                          # Public styles
│   ├── js/                           # Public JavaScript
│   └── partials/                     # Public view templates
├── assets/
│   ├── icons/                        # Plugin icons
│   └── images/                       # Plugin images
└── languages/                        # Translation files
```

---

## 🗄️ Database Schema

### Custom Post Meta Fields
```sql
-- AI-generated content storage
_hmg_ai_key_takeaways       TEXT       -- JSON: Generated takeaways
_hmg_ai_faq                 TEXT       -- JSON: Generated FAQ items
_hmg_ai_toc                 TEXT       -- JSON: Table of contents
_hmg_ai_audio_url           VARCHAR    -- String: Audio file URL
_hmg_ai_video_url           VARCHAR    -- String: Video file URL (premium)
_hmg_ai_generation_date     DATETIME   -- Last generation timestamp
_hmg_ai_usage_count         INT        -- API usage tracking per post
_hmg_ai_content_hash        VARCHAR    -- Content hash for change detection
_hmg_ai_settings            TEXT       -- JSON: Post-specific AI settings
```

### Plugin Options
```sql
-- Core configuration
hmg_ai_gemini_api_key          TEXT    -- Gemini API key
hmg_ai_auth_method             VARCHAR -- 'standalone' or 'base_plugin'
hmg_ai_base_plugin_key         TEXT    -- Base plugin authentication key

-- Usage tracking
hmg_ai_usage_limits            TEXT    -- JSON: Usage limits per tier
hmg_ai_current_usage           TEXT    -- JSON: Current month usage
hmg_ai_user_tier               VARCHAR -- 'free', 'pro', 'premium'

-- AI settings
hmg_ai_default_settings        TEXT    -- JSON: Default generation settings
hmg_ai_voice_settings          TEXT    -- JSON: TTS voice configuration
hmg_ai_style_templates         TEXT    -- JSON: CSS style templates

-- Feature toggles
hmg_ai_enabled_features        TEXT    -- JSON: Enabled features array
```

### Custom Tables (if needed)
```sql
-- Usage analytics table
CREATE TABLE {prefix}hmg_ai_usage_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    post_id BIGINT,
    feature VARCHAR(50) NOT NULL,
    tokens_used INT DEFAULT 0,
    api_calls INT DEFAULT 1,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_timestamp (user_id, timestamp),
    INDEX idx_feature (feature)
);

-- Generated content cache
CREATE TABLE {prefix}hmg_ai_content_cache (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    content_hash VARCHAR(64) NOT NULL UNIQUE,
    content_type VARCHAR(50) NOT NULL,
    generated_content LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    INDEX idx_hash (content_hash),
    INDEX idx_expires (expires_at)
);
```

---

## 🔧 Core Classes Implementation

### Main Plugin Class
```php
<?php
/**
 * Main plugin class
 */
class HMG_AI_Blog_Enhancer {
    
    protected $loader;
    protected $plugin_name;
    protected $version;
    
    public function __construct() {
        $this->version = '1.0.0';
        $this->plugin_name = 'hmg-ai-blog-enhancer';
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    private function load_dependencies() {
        // Load all required classes
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-hmg-ai-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services/class-gemini-service.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services/class-auth-service.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-hmg-ai-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-hmg-ai-public.php';
        
        $this->loader = new HMG_AI_Loader();
    }
    
    private function define_admin_hooks() {
        $plugin_admin = new HMG_AI_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_boxes');
        $this->loader->add_action('save_post', $plugin_admin, 'save_post_meta');
    }
    
    private function define_public_hooks() {
        $plugin_public = new HMG_AI_Public($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
    }
    
    public function run() {
        $this->loader->run();
    }
}
```

### Authentication Service
```php
<?php
/**
 * Authentication service for API access
 */
class HMG_Auth_Service {
    
    private $auth_method;
    private $base_plugin_active;
    
    public function __construct() {
        $this->auth_method = get_option('hmg_ai_auth_method', 'auto');
        $this->base_plugin_active = $this->check_base_plugin();
    }
    
    /**
     * Validate user access and return user tier
     */
    public function validate_access() {
        if ($this->auth_method === 'base_plugin' && $this->base_plugin_active) {
            return $this->validate_base_plugin_auth();
        }
        
        return $this->validate_standalone_auth();
    }
    
    /**
     * Check if base HMG plugin is active
     */
    private function check_base_plugin() {
        // Check for specific HMG plugin
        return is_plugin_active('hmg-wordpress-tools/hmg-wordpress-tools.php');
    }
    
    /**
     * Validate using base plugin authentication
     */
    private function validate_base_plugin_auth() {
        if (!$this->base_plugin_active) {
            return new WP_Error('base_plugin_inactive', 'Base HMG plugin is not active');
        }
        
        // Get authentication from base plugin
        $base_auth = get_option('hmg_base_auth_key');
        
        if (empty($base_auth)) {
            return new WP_Error('no_base_auth', 'No authentication found in base plugin');
        }
        
        // Validate with HMG server
        $response = $this->validate_with_server($base_auth);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return [
            'valid' => true,
            'tier' => $response['tier'],
            'limits' => $response['limits'],
            'user_id' => $response['user_id']
        ];
    }
    
    /**
     * Validate using standalone API key
     */
    private function validate_standalone_auth() {
        $api_key = get_option('hmg_ai_standalone_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'No API key configured');
        }
        
        // Validate with HMG server
        $response = $this->validate_with_server($api_key);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return [
            'valid' => true,
            'tier' => $response['tier'],
            'limits' => $response['limits'],
            'user_id' => $response['user_id']
        ];
    }
    
    /**
     * Validate authentication with HMG server
     */
    private function validate_with_server($auth_key) {
        $response = wp_remote_post('https://api.hmgtools.com/v1/validate', [
            'headers' => [
                'Authorization' => 'Bearer ' . $auth_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'plugin' => 'ai-blog-enhancer',
                'version' => '1.0.0',
                'site_url' => get_site_url()
            ]),
            'timeout' => 10
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('auth_failed', $data['message'] ?? 'Authentication failed');
        }
        
        return $data;
    }
    
    /**
     * Get current user tier
     */
    public function get_user_tier() {
        $auth = $this->validate_access();
        
        if (is_wp_error($auth)) {
            return 'free'; // Default to free tier
        }
        
        return $auth['tier'];
    }
    
    /**
     * Get usage limits for current user
     */
    public function get_usage_limits() {
        $auth = $this->validate_access();
        
        if (is_wp_error($auth)) {
            // Default free tier limits
            return [
                'takeaways' => 5,
                'faq' => 3,
                'toc' => -1, // unlimited
                'audio' => 0,
                'video' => 0
            ];
        }
        
        return $auth['limits'];
    }
}
```

---

## 🔐 Authentication Strategy

### Option 1: Standalone API Key System
```php
// Independent authentication service
class WPT_AI_Auth {
    public function validate_api_key($key) {
        // Call to HMG authentication server
        // Returns user tier, limits, and permissions
    }
    
    public function get_access_token($api_key) {
        // Exchange API key for JWT token
        // Token includes user permissions and limits
    }
}
```

### Option 2: Base Plugin Integration
```php
// Check for existing HMG plugin and leverage its authentication
class WPT_AI_Auth_Integration {
    public function check_base_plugin() {
        // Verify HMG base plugin is installed and active
        // Use existing authentication if available
    }
    
    public function fallback_to_standalone() {
        // Fallback to standalone auth if base plugin not available
    }
}
```

### Recommended Approach: Hybrid System
- **Primary**: Check for base plugin authentication
- **Fallback**: Standalone API key system
- **Benefits**: Seamless for existing customers, accessible for new users
- **Implementation**: Detect base plugin → Use existing auth → Fallback to API key

---

## 🚀 Getting Started

### Installation Steps
1. **Create plugin directory structure**
2. **Implement core classes** (start with main plugin file and core class)
3. **Set up database schema** (custom post meta fields)
4. **Implement Gemini API service**
5. **Create basic admin interface**
6. **Add shortcode support**
7. **Implement authentication system**
8. **Add usage tracking**
9. **Create modern UI components**
10. **Test and optimize**

### Development Environment Setup
```bash
# WordPress development environment
wp-env start

# Install dependencies
composer install
npm install

# Build assets
npm run build

# Run tests
phpunit
npm test
```

### Next Steps
1. Review the authentication strategy and decide on the implementation approach
2. Set up development environment
3. Create the basic plugin structure
4. Implement Phase 1 features according to the roadmap
5. Test with Gemini API integration
6. Build the admin interface
7. Add shortcode functionality

This technical implementation guide provides a solid foundation for building your AI-powered WordPress plugin. The modular architecture allows for easy expansion and maintenance as you add new features. 