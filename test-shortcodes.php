<?php
/**
 * HMG AI Blog Enhancer - Shortcode Testing Script
 * 
 * This script creates test data and demonstrates all shortcode functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // For testing outside WordPress, simulate WordPress environment
    define('ABSPATH', true);
    
    // Mock WordPress functions for testing
    if (!function_exists('wp_kses_post')) {
        function wp_kses_post($data) { return $data; }
    }
    if (!function_exists('esc_attr')) {
        function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    }
    if (!function_exists('esc_html')) {
        function esc_html($text) { return htmlspecialchars($text, ENT_HTML5, 'UTF-8'); }
    }
    if (!function_exists('esc_url')) {
        function esc_url($url) { return filter_var($url, FILTER_SANITIZE_URL); }
    }
    if (!function_exists('esc_js')) {
        function esc_js($text) { return addslashes($text); }
    }
    if (!function_exists('wp_strip_all_tags')) {
        function wp_strip_all_tags($string) { return strip_tags($string); }
    }
    if (!function_exists('get_the_ID')) {
        function get_the_ID() { return 1; }
    }
    if (!function_exists('get_the_title')) {
        function get_the_title($id = null) { return 'Test Blog Post: The Future of AI in Content Marketing'; }
    }
    if (!function_exists('get_post_meta')) {
        function get_post_meta($post_id, $key, $single = false) {
            return generate_test_data($key);
        }
    }
    if (!function_exists('sanitize_html_class')) {
        function sanitize_html_class($class) { return preg_replace('/[^a-zA-Z0-9_-]/', '', $class); }
    }
    if (!function_exists('uniqid')) {
        // uniqid already exists in PHP
    }
}

/**
 * Generate test data for different content types
 */
function generate_test_data($key) {
    switch ($key) {
        case '_hmg_ai_takeaways':
            return json_encode([
                "AI-powered content marketing increases engagement by up to 73% compared to traditional methods",
                "Personalized content recommendations can boost conversion rates by 19% on average",
                "Automated content optimization saves content creators 15-20 hours per week",
                "Machine learning algorithms can predict trending topics 48 hours before they peak",
                "AI-generated meta descriptions improve click-through rates by 25% in search results"
            ]);
            
        case '_hmg_ai_faq':
            return json_encode([
                [
                    "question" => "What makes AI content marketing more effective than traditional methods?",
                    "answer" => "AI content marketing leverages machine learning algorithms to analyze user behavior, preferences, and engagement patterns in real-time. This allows for highly personalized content delivery, optimal timing, and continuous optimization based on performance data. Unlike traditional methods that rely on broad demographic targeting, AI can create micro-segments and deliver individualized experiences at scale."
                ],
                [
                    "question" => "How does AI help with content personalization?",
                    "answer" => "AI analyzes vast amounts of user data including browsing history, engagement patterns, demographic information, and real-time behavior to create detailed user profiles. It then matches content characteristics with user preferences, delivering the right message to the right person at the optimal time. This level of personalization was previously impossible to achieve manually."
                ],
                [
                    "question" => "What are the main benefits of using AI for content optimization?",
                    "answer" => "Key benefits include: 1) Real-time performance tracking and adjustment, 2) Predictive analytics for content planning, 3) Automated A/B testing at scale, 4) SEO optimization based on search trends, 5) Content gap analysis, and 6) Audience sentiment analysis. These capabilities result in higher engagement rates, better ROI, and more efficient content production workflows."
                ],
                [
                    "question" => "Is AI content marketing suitable for small businesses?",
                    "answer" => "Absolutely! Modern AI tools are designed to be accessible and affordable for businesses of all sizes. Small businesses can particularly benefit from AI's ability to automate time-consuming tasks, provide insights that were previously only available to large enterprises, and compete more effectively by delivering personalized experiences that rival those of bigger competitors."
                ],
                [
                    "question" => "How do you measure the success of AI-driven content marketing?",
                    "answer" => "Success metrics include: engagement rates (likes, shares, comments), conversion rates, time spent on content, click-through rates, lead generation quality, customer lifetime value, and ROI. AI tools provide detailed analytics and can track micro-conversions throughout the customer journey, offering more granular insights than traditional measurement methods."
                ]
            ]);
            
        case '_hmg_ai_toc':
            return json_encode([
                [
                    "title" => "Introduction to AI Content Marketing",
                    "anchor" => "#introduction",
                    "level" => 1,
                    "subsections" => [
                        [
                            "title" => "What is AI Content Marketing?",
                            "anchor" => "#what-is-ai-content-marketing"
                        ],
                        [
                            "title" => "Current Market Trends",
                            "anchor" => "#current-market-trends"
                        ]
                    ]
                ],
                [
                    "title" => "Key Benefits and Advantages",
                    "anchor" => "#key-benefits",
                    "level" => 1,
                    "subsections" => [
                        [
                            "title" => "Increased Engagement",
                            "anchor" => "#increased-engagement"
                        ],
                        [
                            "title" => "Cost Efficiency",
                            "anchor" => "#cost-efficiency"
                        ]
                    ]
                ],
                [
                    "title" => "Implementation Strategies",
                    "anchor" => "#implementation-strategies",
                    "level" => 1
                ],
                [
                    "title" => "Tools and Technologies",
                    "anchor" => "#tools-and-technologies",
                    "level" => 1,
                    "subsections" => [
                        [
                            "title" => "Content Generation Tools",
                            "anchor" => "#content-generation-tools"
                        ],
                        [
                            "title" => "Analytics Platforms",
                            "anchor" => "#analytics-platforms"
                        ],
                        [
                            "title" => "Automation Software",
                            "anchor" => "#automation-software"
                        ]
                    ]
                ],
                [
                    "title" => "Best Practices and Tips",
                    "anchor" => "#best-practices",
                    "level" => 1
                ],
                [
                    "title" => "Future Outlook",
                    "anchor" => "#future-outlook",
                    "level" => 1
                ],
                [
                    "title" => "Conclusion",
                    "anchor" => "#conclusion",
                    "level" => 1
                ]
            ]);
            
        case '_hmg_ai_audio_url':
            return 'https://www.soundjay.com/misc/sounds/bell-ringing-05.wav'; // Sample audio URL
            
        case '_hmg_ai_audio_title':
            return 'AI Content Marketing Guide - Audio Version';
            
        case '_hmg_ai_audio_duration':
            return '12:45';
            
        case '_hmg_ai_audio_size':
            return '8.2 MB';
            
        default:
            return '';
    }
}

/**
 * Test all shortcode templates
 */
function test_all_shortcodes() {
    echo "<h1>HMG AI Blog Enhancer - Shortcode Testing</h1>\n";
    echo "<p><strong>Testing all shortcode templates with different styles</strong></p>\n";
    
    // Test Takeaways
    echo "<h2>1. Testing Takeaways Shortcode</h2>\n";
    test_takeaways_shortcode();
    
    echo "<hr style='margin: 40px 0;'>\n";
    
    // Test FAQ
    echo "<h2>2. Testing FAQ Shortcode</h2>\n";
    test_faq_shortcode();
    
    echo "<hr style='margin: 40px 0;'>\n";
    
    // Test TOC
    echo "<h2>3. Testing Table of Contents Shortcode</h2>\n";
    test_toc_shortcode();
    
    echo "<hr style='margin: 40px 0;'>\n";
    
    // Test Audio
    echo "<h2>4. Testing Audio Player Shortcode</h2>\n";
    test_audio_shortcode();
}

/**
 * Test takeaways shortcode with all styles
 */
function test_takeaways_shortcode() {
    $styles = ['default', 'numbered', 'cards', 'highlights'];
    
    foreach ($styles as $style) {
        echo "<h3>Takeaways - {$style} style</h3>\n";
        
        // Simulate shortcode attributes
        $atts = ['post_id' => 1, 'style' => $style];
        $takeaways = get_post_meta(1, '_hmg_ai_takeaways', true);
        
        // Include the template
        ob_start();
        include 'public/partials/takeaways-template.php';
        $output = ob_get_clean();
        
        echo $output . "\n";
        echo "<br><br>\n";
    }
}

/**
 * Test FAQ shortcode with all styles
 */
function test_faq_shortcode() {
    $styles = ['accordion', 'list', 'cards'];
    
    foreach ($styles as $style) {
        echo "<h3>FAQ - {$style} style</h3>\n";
        
        // Simulate shortcode attributes
        $atts = ['post_id' => 1, 'style' => $style];
        $faq = get_post_meta(1, '_hmg_ai_faq', true);
        
        // Include the template
        ob_start();
        include 'public/partials/faq-template.php';
        $output = ob_get_clean();
        
        echo $output . "\n";
        echo "<br><br>\n";
    }
}

/**
 * Test TOC shortcode with all styles
 */
function test_toc_shortcode() {
    $styles = ['numbered', 'horizontal', 'minimal', 'sidebar'];
    
    foreach ($styles as $style) {
        echo "<h3>Table of Contents - {$style} style</h3>\n";
        
        // Simulate shortcode attributes
        $atts = ['post_id' => 1, 'style' => $style];
        $toc = get_post_meta(1, '_hmg_ai_toc', true);
        
        // Include the template
        ob_start();
        include 'public/partials/toc-template.php';
        $output = ob_get_clean();
        
        echo $output . "\n";
        echo "<br><br>\n";
    }
}

/**
 * Test audio shortcode with all styles
 */
function test_audio_shortcode() {
    $styles = ['player', 'compact', 'minimal', 'card'];
    
    foreach ($styles as $style) {
        echo "<h3>Audio Player - {$style} style</h3>\n";
        
        // Simulate shortcode attributes
        $atts = ['post_id' => 1, 'style' => $style];
        $audio_url = get_post_meta(1, '_hmg_ai_audio_url', true);
        
        // Include the template
        ob_start();
        include 'public/partials/audio-player-template.php';
        $output = ob_get_clean();
        
        echo $output . "\n";
        echo "<br><br>\n";
    }
}

// Generate complete HTML page for testing
function generate_test_page() {
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMG AI Blog Enhancer - Shortcode Testing</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/hmg-ai-public.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .test-section {
            background: white;
            padding: 30px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #332A86;
        }
        hr {
            border: none;
            height: 2px;
            background: linear-gradient(to right, #332A86, #5E9732);
            margin: 40px 0;
        }
    </style>
</head>
<body>
    <div class="test-section">
        <?php test_all_shortcodes(); ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="public/js/hmg-ai-public.js"></script>
</body>
</html>
    <?php
    return ob_get_clean();
}

// If running directly, output the test page
if (php_sapi_name() === 'cli') {
    echo "HMG AI Blog Enhancer - Shortcode Testing\n";
    echo "========================================\n\n";
    test_all_shortcodes();
} else {
    echo generate_test_page();
}
?> 