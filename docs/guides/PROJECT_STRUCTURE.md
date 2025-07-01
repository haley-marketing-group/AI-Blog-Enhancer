# Project Structure & Development Guide
## HMG AI Blog Enhancer WordPress Plugin

---

## 📁 Complete Directory Structure

```
hmg-ai-blog-enhancer/
├── 📄 hmg-ai-blog-enhancer.php         # Main plugin file
├── 📄 uninstall.php                    # Uninstall cleanup
├── 📄 README.txt                       # WordPress.org readme
├── 📄 composer.json                    # PHP dependencies
├── 📄 package.json                     # Node.js dependencies
├── 📄 webpack.config.js                # Build configuration
├── 📄 .gitignore                       # Git ignore rules
│
├── 📁 includes/                        # Core plugin logic
│   ├── 📄 class-hmg-ai-activator.php   # Plugin activation
│   ├── 📄 class-hmg-ai-deactivator.php # Plugin deactivation
│   ├── 📄 class-hmg-ai-loader.php      # Hook loader
│   ├── 📄 class-hmg-ai-core.php        # Main plugin class
│   ├── 📄 class-hmg-ai-admin.php       # Admin functionality
│   ├── 📄 class-hmg-ai-public.php      # Public functionality
│   │
│   ├── 📁 services/                    # API and external services
│   │   ├── 📄 class-gemini-service.php
│   │   ├── 📄 class-tts-service.php
│   │   ├── 📄 class-auth-service.php
│   │   ├── 📄 class-usage-tracker.php
│   │   └── 📄 class-content-analyzer.php
│   │
│   ├── 📁 generators/                  # Content generators
│   │   ├── 📄 class-takeaways-generator.php
│   │   ├── 📄 class-faq-generator.php
│   │   ├── 📄 class-toc-generator.php
│   │   └── 📄 class-cta-enhancer.php
│   │
│   ├── 📁 shortcodes/                  # Shortcode handlers
│   │   ├── 📄 class-ai-shortcodes.php
│   │   └── 📄 class-shortcode-manager.php
│   │
│   └── 📁 utils/                       # Utility classes
│       ├── 📄 class-cache-manager.php
│       ├── 📄 class-error-handler.php
│       └── 📄 class-security-helper.php
│
├── 📁 admin/                           # Admin interface
│   ├── 📁 css/                         # Admin styles
│   │   ├── 📄 hmg-ai-admin.css
│   │   └── 📄 hmg-ai-components.css
│   │
│   ├── 📁 js/                          # Admin JavaScript
│   │   ├── 📄 hmg-ai-admin.js
│   │   ├── 📄 content-generator.js
│   │   ├── 📄 settings-manager.js
│   │   └── 📄 analytics-dashboard.js
│   │
│   ├── 📁 partials/                    # Admin view templates
│   │   ├── 📄 admin-display.php
│   │   ├── 📄 settings-display.php
│   │   ├── 📄 analytics-display.php
│   │   ├── 📄 meta-box-content-generator.php
│   │   └── 📄 meta-box-ai-settings.php
│   │
│   └── 📁 components/                  # Reusable admin components
│       ├── 📄 usage-meter.php
│       ├── 📄 generation-controls.php
│       └── 📄 content-preview.php
│
├── 📁 public/                          # Public-facing functionality
│   ├── 📁 css/                         # Public styles
│   │   ├── 📄 hmg-ai-public.css
│   │   ├── 📄 takeaways-styles.css
│   │   ├── 📄 faq-styles.css
│   │   ├── 📄 toc-styles.css
│   │   └── 📄 audio-player-styles.css
│   │
│   ├── 📁 js/                          # Public JavaScript
│   │   ├── 📄 hmg-ai-public.js
│   │   ├── 📄 faq-accordion.js
│   │   ├── 📄 toc-navigation.js
│   │   └── 📄 audio-player.js
│   │
│   └── 📁 partials/                    # Public view templates
│       ├── 📄 takeaways-template.php
│       ├── 📄 faq-template.php
│       ├── 📄 toc-template.php
│       └── 📄 audio-player-template.php
│
├── 📁 assets/                          # Static assets
│   ├── 📁 icons/                       # Plugin icons
│   │   ├── 📄 plugin-icon-128x128.png
│   │   ├── 📄 plugin-icon-256x256.png
│   │   └── 📄 banner-1544x500.png
│   │
│   ├── 📁 images/                      # Plugin images
│   │   ├── 📄 ai-robot.svg
│   │   ├── 📄 loading-spinner.gif
│   │   └── 📄 feature-icons/
│   │
│   └── 📁 audio/                       # Audio assets
│       └── 📄 notification-sounds/
│
├── 📁 languages/                       # Internationalization
│   ├── 📄 hmg-ai-blog-enhancer.pot
│   ├── 📄 hmg-ai-blog-enhancer-en_US.po
│   └── 📄 hmg-ai-blog-enhancer-en_US.mo
│
├── 📁 tests/                           # Unit and integration tests
│   ├── 📁 unit/
│   │   ├── 📄 test-gemini-service.php
│   │   ├── 📄 test-auth-service.php
│   │   └── 📄 test-usage-tracker.php
│   │
│   ├── 📁 integration/
│   │   ├── 📄 test-shortcodes.php
│   │   └── 📄 test-admin-interface.php
│   │
│   └── 📄 bootstrap.php
│
├── 📁 docs/                            # Documentation
│   ├── 📄 API.md
│   ├── 📄 HOOKS.md
│   ├── 📄 SHORTCODES.md
│   └── 📄 CUSTOMIZATION.md
│
└── 📁 build/                           # Build output (gitignored)
    ├── 📁 css/
    ├── 📁 js/
    └── 📄 manifest.json
```

---

## 🚀 Quick Start Development Guide

### 1. Environment Setup

#### Prerequisites
```bash
# Required software
- PHP 7.4+ (WordPress requirement)
- Node.js 16+ (for build tools)
- Composer (PHP package manager)
- WordPress development environment
```

#### Initial Setup
```bash
# Clone or create project directory
mkdir hmg-ai-blog-enhancer
cd hmg-ai-blog-enhancer

# Initialize package.json
npm init -y

# Initialize composer.json
composer init

# Install development dependencies
npm install --save-dev webpack webpack-cli css-loader mini-css-extract-plugin
composer require --dev phpunit/phpunit

# Create basic structure
mkdir -p includes/{services,generators,shortcodes,utils}
mkdir -p admin/{css,js,partials,components}
mkdir -p public/{css,js,partials}
mkdir -p assets/{icons,images,audio}
mkdir -p languages tests/{unit,integration} docs build
```

### 2. Main Plugin File Template

#### `hmg-ai-blog-enhancer.php`
```php
<?php
/**
 * Plugin Name:       HMG AI Blog Enhancer
 * Plugin URI:        https://hmgtools.com/ai-blog-enhancer
 * Description:       AI-powered blog content enhancement with key takeaways, FAQ generation, TOC, and audio conversion.
 * Version:           1.0.0
 * Author:            HMG Tools
 * Author URI:        https://hmgtools.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hmg-ai-blog-enhancer
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 * Network:           false
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin constants
 */
define('HMG_AI_BLOG_ENHANCER_VERSION', '1.0.0');
define('HMG_AI_BLOG_ENHANCER_PLUGIN_NAME', 'hmg-ai-blog-enhancer');
define('HMG_AI_BLOG_ENHANCER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HMG_AI_BLOG_ENHANCER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HMG_AI_BLOG_ENHANCER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_hmg_ai_blog_enhancer() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-hmg-ai-activator.php';
    HMG_AI_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_hmg_ai_blog_enhancer() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-hmg-ai-deactivator.php';
    HMG_AI_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_hmg_ai_blog_enhancer');
register_deactivation_hook(__FILE__, 'deactivate_hmg_ai_blog_enhancer');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-hmg-ai-core.php';

/**
 * Begins execution of the plugin.
 */
function run_hmg_ai_blog_enhancer() {
    $plugin = new HMG_AI_Core();
    $plugin->run();
}

run_hmg_ai_blog_enhancer();
```

### 3. Core Class Template

#### `includes/class-hmg-ai-core.php`
```php
<?php
/**
 * The core plugin class.
 */
class HMG_AI_Core {
    
    protected $loader;
    protected $plugin_name;
    protected $version;
    
    public function __construct() {
        $this->version = HMG_AI_BLOG_ENHANCER_VERSION;
        $this->plugin_name = HMG_AI_BLOG_ENHANCER_PLUGIN_NAME;
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    private function load_dependencies() {
        // Load the plugin class responsible for orchestrating the actions and filters
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-loader.php';
        
        // Load the plugin class responsible for defining internationalization functionality
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-i18n.php';
        
        // Load admin-specific functionality
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-admin.php';
        
        // Load public-facing functionality
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-public.php';
        
        // Load services
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-auth-service.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-gemini-service.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-usage-tracker.php';
        
        // Load generators
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/generators/class-takeaways-generator.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/generators/class-faq-generator.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/generators/class-toc-generator.php';
        
        // Load shortcodes
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/shortcodes/class-ai-shortcodes.php';
        
        $this->loader = new HMG_AI_Loader();
    }
    
    private function set_locale() {
        $plugin_i18n = new HMG_AI_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }
    
    private function define_admin_hooks() {
        $plugin_admin = new HMG_AI_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_boxes');
        $this->loader->add_action('save_post', $plugin_admin, 'save_post_meta');
        
        // AJAX handlers
        $this->loader->add_action('wp_ajax_hmg_generate_takeaways', $plugin_admin, 'ajax_generate_takeaways');
        $this->loader->add_action('wp_ajax_hmg_generate_faq', $plugin_admin, 'ajax_generate_faq');
        $this->loader->add_action('wp_ajax_hmg_generate_toc', $plugin_admin, 'ajax_generate_toc');
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
    
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    public function get_version() {
        return $this->version;
    }
    
    public function get_loader() {
        return $this->loader;
    }
}
```

### 4. Package Configuration Files

#### `package.json`
```json
{
  "name": "hmg-ai-blog-enhancer",
  "version": "1.0.0",
  "description": "AI-powered blog content enhancement WordPress plugin",
  "main": "webpack.config.js",
  "scripts": {
    "build": "webpack --mode=production",
    "dev": "webpack --mode=development --watch",
    "test": "jest",
    "lint:js": "eslint admin/js public/js",
    "lint:css": "stylelint admin/css public/css"
  },
  "devDependencies": {
    "webpack": "^5.88.0",
    "webpack-cli": "^5.1.0",
    "css-loader": "^6.8.0",
    "mini-css-extract-plugin": "^2.7.0",
    "sass": "^1.63.0",
    "sass-loader": "^13.3.0",
    "babel-loader": "^9.1.0",
    "@babel/core": "^7.22.0",
    "@babel/preset-env": "^7.22.0",
    "eslint": "^8.44.0",
    "stylelint": "^15.10.0",
    "jest": "^29.6.0"
  },
  "keywords": [
    "wordpress",
    "plugin",
    "ai",
    "blog",
    "content",
    "gemini"
  ],
  "author": "HMG Tools",
  "license": "GPL-2.0-or-later"
}
```

#### `composer.json`
```json
{
    "name": "hmg-tools/ai-blog-enhancer",
    "description": "AI-powered blog content enhancement WordPress plugin",
    "type": "wordpress-plugin",
    "license": "GPL-2.0-or-later",
    "authors": [
        {
            "name": "HMG Tools",
            "homepage": "https://hmgtools.com"
        }
    ],
    "require": {
        "php": ">=7.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "wp-coding-standards/wpcs": "^3.0",
        "dealerdirect/phpcodesniffer-composer-installer": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "HMG\\AI\\": "includes/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "lint": "phpcs --standard=WordPress includes/ admin/ public/",
        "lint:fix": "phpcbf --standard=WordPress includes/ admin/ public/"
    }
}
```

#### `webpack.config.js`
```javascript
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: {
        'admin': './admin/js/hmg-ai-admin.js',
        'public': './public/js/hmg-ai-public.js',
        'admin-styles': './admin/css/hmg-ai-admin.scss',
        'public-styles': './public/css/hmg-ai-public.scss'
    },
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: 'js/[name].js',
        clean: true
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader'
                ]
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].css'
        })
    ]
};
```

---

## 🔧 Development Workflow

### Phase 1: Foundation (Weeks 1-2)
1. **Setup project structure**
   ```bash
   # Create directory structure
   # Initialize package.json and composer.json
   # Setup build tools
   ```

2. **Create core plugin files**
   - Main plugin file
   - Core class
   - Loader class
   - Activator/Deactivator

3. **Implement authentication service**
   - Base plugin detection
   - API key validation
   - User tier management

### Phase 2: Core AI Features (Weeks 3-4)
1. **Gemini API integration**
   - Service class
   - Error handling
   - Rate limiting

2. **Content generators**
   - Key takeaways
   - FAQ generation
   - Table of contents

3. **Basic admin interface**
   - Meta boxes
   - Settings page
   - AJAX handlers

### Phase 3: UI/UX Enhancement (Weeks 5-6)
1. **Modern admin interface**
   - React components (optional)
   - CSS framework
   - Interactive controls

2. **Shortcode system**
   - All shortcode handlers
   - Style variations
   - Template system

3. **Public-facing styles**
   - Responsive design
   - Theme compatibility
   - Custom CSS options

### Phase 4: Advanced Features (Weeks 7-8)
1. **Usage tracking**
   - Database tables
   - Analytics dashboard
   - Limit enforcement

2. **Context-aware AI**
   - Website analysis
   - Content relationships
   - SEO integration

3. **Audio features**
   - TTS integration
   - Audio player
   - File management

---

## 📝 Development Best Practices

### Code Standards
- Follow WordPress Coding Standards
- Use PSR-4 autoloading
- Implement proper error handling
- Add comprehensive documentation

### Security
- Sanitize all inputs
- Validate and escape outputs
- Use nonces for AJAX requests
- Implement capability checks

### Performance
- Cache API responses
- Optimize database queries
- Minimize HTTP requests
- Use transients for temporary data

### Testing
- Unit tests for all services
- Integration tests for workflows
- Manual testing on different themes
- Performance testing under load

This project structure provides a solid foundation for building a professional WordPress plugin with modern development practices and scalable architecture. 