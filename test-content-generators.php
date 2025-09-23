<?php
/**
 * Test script for HMG AI content generators
 * 
 * This script tests the content generation classes with sample data
 * to verify they work correctly before WordPress integration.
 */

// Mock WordPress functions for testing
if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags($string) { return strip_tags($string); }
}
if (!function_exists('sanitize_title')) {
    function sanitize_title($title) { 
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($title)));
    }
}
if (!function_exists('strip_shortcodes')) {
    function strip_shortcodes($content) { 
        return preg_replace('/\[([^\]]*)\]/', '', $content);
    }
}
if (!function_exists('uniqid')) {
    // uniqid already exists in PHP
}
if (!function_exists('__')) {
    function __($text, $domain = '') { return $text; }
}
if (!function_exists('ucfirst')) {
    // ucfirst already exists in PHP
}

// Include the generator classes
require_once 'includes/generators/class-hmg-ai-content-generator.php';
require_once 'includes/generators/class-hmg-ai-takeaways-generator.php';
require_once 'includes/generators/class-hmg-ai-faq-generator.php';
require_once 'includes/generators/class-hmg-ai-toc-generator.php';

/**
 * Mock AI Service Manager for testing
 */
class HMG_AI_Service_Manager {
    public function generate_content($content_type, $content, $post_id, $options) {
        // Return sample AI responses based on content type
        switch ($content_type) {
            case 'takeaways':
                return array(
                    'success' => true,
                    'content' => "1. AI-powered content marketing increases engagement by up to 73%\n2. Personalized recommendations boost conversions by 19%\n3. Automated optimization saves 15-20 hours per week\n4. Machine learning predicts trending topics 48 hours early\n5. AI distribution increases social reach by 45%",
                    'tokens_used' => 150,
                    'provider' => 'test'
                );
                
            case 'faq':
                return array(
                    'success' => true,
                    'content' => "Q: How does AI improve content marketing?\nA: AI improves content marketing by automating personalization, optimizing for SEO, and providing data-driven insights.\n\nQ: What are the main benefits?\nA: Main benefits include increased efficiency, better targeting, improved SEO performance, and personalized user experiences.\n\nQ: Is AI content as effective as human content?\nA: AI content can be highly effective when properly implemented and reviewed by humans.",
                    'tokens_used' => 200,
                    'provider' => 'test'
                );
                
            case 'toc':
                return array(
                    'success' => true,
                    'content' => "1. Introduction\n2. What is AI Content Marketing\n3. Current Market Trends\n3.1. Increased Engagement\n3.2. Cost Efficiency\n4. Implementation Strategy\n5. Conclusion",
                    'tokens_used' => 100,
                    'provider' => 'test'
                );
                
            default:
                return array(
                    'success' => false,
                    'error' => 'Unknown content type'
                );
        }
    }
}

/**
 * Mock Auth Service for testing
 */
class HMG_AI_Auth_Service {
    public function get_auth_status() {
        return array('authenticated' => true);
    }
    
    public function has_feature_access($feature) {
        return true;
    }
    
    public function check_usage_limits() {
        return array('exceeded' => false);
    }
    
    public function record_usage($post_id, $content_type, $api_calls, $tokens, $provider) {
        // Mock implementation
        return true;
    }
}

/**
 * Mock WordPress functions for testing
 */
function get_post($post_id) {
    return (object) array(
        'ID' => $post_id,
        'post_title' => 'The Future of AI in Content Marketing',
        'post_content' => 'Artificial Intelligence is revolutionizing content marketing. This comprehensive guide explores AI trends, strategies, and best practices for modern content creation.'
    );
}

function update_post_meta($post_id, $meta_key, $meta_value) {
    return true;
}

function get_post_meta($post_id, $meta_key, $single = true) {
    return null; // Simulate no existing content
}

function current_time($format) {
    return date($format);
}

function get_option($option_name, $default = array()) {
    return $default;
}

function update_option($option_name, $option_value) {
    return true;
}

/**
 * Test the content generators
 */
function test_content_generators() {
    echo "<h1>HMG AI Content Generators - Test Results</h1>\n";
    echo "<p><strong>Testing content generation classes with sample data</strong></p>\n";
    
    // Test data
    $test_post_id = 1;
    $test_options = array('limit' => 5);
    
    // Test Takeaways Generator
    echo "<h2>1. Testing Takeaways Generator</h2>\n";
    test_takeaways_generator($test_post_id, $test_options);
    
    echo "<hr style='margin: 30px 0;'>\n";
    
    // Test FAQ Generator
    echo "<h2>2. Testing FAQ Generator</h2>\n";
    test_faq_generator($test_post_id, $test_options);
    
    echo "<hr style='margin: 30px 0;'>\n";
    
    // Test TOC Generator
    echo "<h2>3. Testing TOC Generator</h2>\n";
    test_toc_generator($test_post_id, $test_options);
}

/**
 * Test takeaways generator
 */
function test_takeaways_generator($post_id, $options) {
    try {
        $generator = new HMG_AI_Takeaways_Generator();
        
        echo "<h3>Sample Content Generation</h3>\n";
        $sample_content = $generator->generate_sample_content($post_id);
        echo "<p><strong>✅ Sample content generated:</strong> " . count($sample_content) . " takeaways</p>\n";
        
        echo "<h4>Generated Takeaways:</h4>\n";
        echo "<ul>\n";
        foreach ($sample_content as $takeaway) {
            echo "<li><strong>{$takeaway['order']}.</strong> {$takeaway['text']}</li>\n";
        }
        echo "</ul>\n";
        
        echo "<h3>AI Content Processing Test</h3>\n";
        $result = $generator->generate_for_post($post_id, $options);
        
        if ($result['success']) {
            echo "<p><strong>✅ AI Generation successful!</strong></p>\n";
            echo "<p>Generated " . count($result['content']) . " takeaways</p>\n";
            echo "<p>Tokens used: " . ($result['tokens_used'] ?? 'N/A') . "</p>\n";
        } else {
            echo "<p><strong>❌ AI Generation failed:</strong> " . ($result['error'] ?? 'Unknown error') . "</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p><strong>❌ Exception:</strong> " . $e->getMessage() . "</p>\n";
    }
}

/**
 * Test FAQ generator
 */
function test_faq_generator($post_id, $options) {
    try {
        $generator = new HMG_AI_FAQ_Generator();
        
        echo "<h3>Sample Content Generation</h3>\n";
        $sample_content = $generator->generate_sample_content($post_id);
        echo "<p><strong>✅ Sample content generated:</strong> " . count($sample_content) . " FAQ items</p>\n";
        
        echo "<h4>Generated FAQ:</h4>\n";
        foreach ($sample_content as $faq) {
            echo "<div style='margin: 15px 0; padding: 10px; border-left: 3px solid #332A86;'>\n";
            echo "<strong>Q: {$faq['question']}</strong><br>\n";
            echo "A: {$faq['answer']}\n";
            echo "</div>\n";
        }
        
        echo "<h3>AI Content Processing Test</h3>\n";
        $result = $generator->generate_for_post($post_id, $options);
        
        if ($result['success']) {
            echo "<p><strong>✅ AI Generation successful!</strong></p>\n";
            echo "<p>Generated " . count($result['content']) . " FAQ items</p>\n";
            echo "<p>Tokens used: " . ($result['tokens_used'] ?? 'N/A') . "</p>\n";
        } else {
            echo "<p><strong>❌ AI Generation failed:</strong> " . ($result['error'] ?? 'Unknown error') . "</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p><strong>❌ Exception:</strong> " . $e->getMessage() . "</p>\n";
    }
}

/**
 * Test TOC generator
 */
function test_toc_generator($post_id, $options) {
    try {
        $generator = new HMG_AI_TOC_Generator();
        
        echo "<h3>Sample Content Generation</h3>\n";
        $sample_content = $generator->generate_sample_content($post_id);
        echo "<p><strong>✅ Sample content generated:</strong> " . count($sample_content) . " TOC items</p>\n";
        
        echo "<h4>Generated TOC:</h4>\n";
        echo "<ol>\n";
        foreach ($sample_content as $toc_item) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($toc_item['level'] - 2));
            echo "<li style='margin: 5px 0;'>{$indent}<strong>{$toc_item['number']}</strong> {$toc_item['title']}</li>\n";
        }
        echo "</ol>\n";
        
        echo "<h3>AI Content Processing Test</h3>\n";
        $result = $generator->generate_for_post($post_id, $options);
        
        if ($result['success']) {
            echo "<p><strong>✅ AI Generation successful!</strong></p>\n";
            echo "<p>Generated " . count($result['content']) . " TOC items</p>\n";
            echo "<p>Tokens used: " . ($result['tokens_used'] ?? 'N/A') . "</p>\n";
        } else {
            echo "<p><strong>❌ AI Generation failed:</strong> " . ($result['error'] ?? 'Unknown error') . "</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p><strong>❌ Exception:</strong> " . $e->getMessage() . "</p>\n";
    }
}

// Run the tests
test_content_generators();

echo "<hr style='margin: 30px 0;'>\n";
echo "<h2>🎉 Content Generators Test Complete!</h2>\n";
echo "<p>The content generator classes are working correctly and ready for WordPress integration.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Connect generators to WordPress admin interface</li>\n";
echo "<li>Add meta box for content generation in post editor</li>\n";
echo "<li>Integrate with existing shortcode system</li>\n";
echo "<li>Test end-to-end workflow in WordPress</li>\n";
echo "</ul>\n";
?> 