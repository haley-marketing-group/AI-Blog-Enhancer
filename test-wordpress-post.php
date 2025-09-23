<?php
/**
 * WordPress Test Post for HMG AI Blog Enhancer Shortcodes
 * 
 * This script creates a test post in WordPress with sample AI-generated content
 * and demonstrates all shortcode functionality.
 */

// Only run if we're in WordPress
if (!defined('ABSPATH')) {
    die('This script must be run within WordPress.');
}

/**
 * Create test post with AI-generated content and shortcodes
 */
function create_hmg_ai_test_post() {
    // Check if test post already exists
    $existing_post = get_page_by_title('HMG AI Blog Enhancer - Shortcode Test', OBJECT, 'post');
    if ($existing_post) {
        wp_delete_post($existing_post->ID, true);
    }
    
    // Create the test post content
    $post_content = get_test_post_content();
    
    // Insert the post
    $post_id = wp_insert_post(array(
        'post_title' => 'HMG AI Blog Enhancer - Shortcode Test',
        'post_content' => $post_content,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => 1,
        'meta_input' => array(
            '_hmg_ai_takeaways' => get_test_takeaways_data(),
            '_hmg_ai_faq' => get_test_faq_data(),
            '_hmg_ai_toc' => get_test_toc_data(),
            '_hmg_ai_audio_url' => 'https://www.soundjay.com/misc/sounds/bell-ringing-05.wav',
            '_hmg_ai_audio_title' => 'The Future of AI in Content Marketing - Audio Version',
            '_hmg_ai_audio_duration' => '15:30',
            '_hmg_ai_audio_size' => '12.8 MB'
        )
    ));
    
    if ($post_id && !is_wp_error($post_id)) {
        return array(
            'success' => true,
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'edit_url' => get_edit_post_link($post_id)
        );
    } else {
        return array(
            'success' => false,
            'error' => is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error'
        );
    }
}

/**
 * Get test post content with shortcodes
 */
function get_test_post_content() {
    return '
<h1 id="introduction">The Future of AI in Content Marketing</h1>

<p>Artificial Intelligence is revolutionizing the way we create, distribute, and optimize content. This comprehensive guide explores the latest trends, strategies, and best practices for leveraging AI in your content marketing efforts.</p>

[hmg_ai_toc style="horizontal"]

<p>Content marketing has evolved dramatically over the past decade, but perhaps no advancement has been as transformative as the integration of artificial intelligence. From personalized content recommendations to automated optimization, AI is reshaping how brands connect with their audiences.</p>

[hmg_ai_takeaways style="cards"]

<h2 id="what-is-ai-content-marketing">What is AI Content Marketing?</h2>

<p>AI content marketing leverages machine learning algorithms, natural language processing, and predictive analytics to enhance every aspect of the content lifecycle. This includes content creation, distribution, personalization, and performance optimization.</p>

<h2 id="current-market-trends">Current Market Trends</h2>

<p>The AI content marketing landscape is rapidly evolving, with new tools and technologies emerging regularly. Key trends include:</p>

<ul>
<li>Automated content generation using GPT models</li>
<li>Real-time personalization based on user behavior</li>
<li>Predictive content performance analytics</li>
<li>Voice and conversational AI integration</li>
<li>Visual content optimization through computer vision</li>
</ul>

<h2 id="key-benefits">Key Benefits and Advantages</h2>

<p>Organizations implementing AI-driven content marketing strategies report significant improvements across multiple metrics:</p>

<h3 id="increased-engagement">Increased Engagement</h3>

<p>AI-powered personalization can increase engagement rates by up to 73% compared to traditional one-size-fits-all approaches. By analyzing user behavior patterns, AI systems can deliver the right content to the right person at the optimal time.</p>

<h3 id="cost-efficiency">Cost Efficiency</h3>

<p>Automation reduces manual content creation and optimization tasks, allowing teams to focus on strategy and creativity. Many organizations report 40-60% time savings in content production workflows.</p>

<h2 id="implementation-strategies">Implementation Strategies</h2>

<p>Successfully implementing AI in content marketing requires a strategic approach:</p>

<ol>
<li><strong>Start with clear objectives</strong> - Define what you want to achieve with AI</li>
<li><strong>Audit your current content</strong> - Understand your baseline performance</li>
<li><strong>Choose the right tools</strong> - Select AI platforms that align with your goals</li>
<li><strong>Train your team</strong> - Ensure your staff understands how to leverage AI effectively</li>
<li><strong>Monitor and optimize</strong> - Continuously refine your AI-driven processes</li>
</ol>

<h2 id="tools-and-technologies">Tools and Technologies</h2>

<p>The AI content marketing ecosystem includes various specialized tools and platforms:</p>

<h3 id="content-generation-tools">Content Generation Tools</h3>

<p>Modern AI writing assistants can help create blog posts, social media content, email campaigns, and more. These tools use large language models trained on vast datasets to generate human-like text.</p>

<h3 id="analytics-platforms">Analytics Platforms</h3>

<p>AI-powered analytics platforms provide deeper insights into content performance, audience behavior, and optimization opportunities. They can predict which content will perform best and suggest improvements.</p>

<h3 id="automation-software">Automation Software</h3>

<p>Content automation platforms can handle scheduling, distribution, and even basic optimization tasks, freeing up human resources for more strategic work.</p>

[hmg_ai_faq style="accordion"]

<h2 id="best-practices">Best Practices and Tips</h2>

<p>To maximize the benefits of AI in content marketing, consider these best practices:</p>

<ul>
<li><strong>Maintain human oversight</strong> - AI should augment, not replace, human creativity</li>
<li><strong>Focus on quality over quantity</strong> - Use AI to improve content quality, not just increase volume</li>
<li><strong>Respect privacy</strong> - Ensure your AI systems comply with data protection regulations</li>
<li><strong>Test and iterate</strong> - Continuously experiment with different AI approaches</li>
<li><strong>Stay updated</strong> - The AI landscape evolves rapidly, so keep learning</li>
</ul>

<h2 id="future-outlook">Future Outlook</h2>

<p>The future of AI in content marketing looks incredibly promising. Emerging technologies like GPT-4, advanced computer vision, and multimodal AI systems will enable even more sophisticated content experiences.</p>

<p>We can expect to see:</p>

<ul>
<li>More natural and conversational AI interactions</li>
<li>Better integration between different content channels</li>
<li>Advanced predictive capabilities for content planning</li>
<li>Improved accessibility through AI-powered content adaptation</li>
<li>Greater personalization at scale</li>
</ul>

[hmg_ai_audio style="card"]

<h2 id="conclusion">Conclusion</h2>

<p>AI is not just the future of content marketing—it\'s the present. Organizations that embrace AI technologies today will have a significant competitive advantage as the digital landscape continues to evolve.</p>

<p>The key is to approach AI implementation strategically, focusing on how these technologies can enhance human creativity and deliver better experiences for your audience. With the right strategy, tools, and mindset, AI can transform your content marketing from good to exceptional.</p>

<hr>

<h2>Shortcode Testing Examples</h2>

<p>Below are examples of all available shortcode styles for testing purposes:</p>

<h3>Takeaways Styles</h3>
<h4>Default Style</h4>
[hmg_ai_takeaways style="default"]

<h4>Numbered Style</h4>
[hmg_ai_takeaways style="numbered"]

<h4>Highlights Style</h4>
[hmg_ai_takeaways style="highlights"]

<h3>FAQ Styles</h3>
<h4>List Style</h4>
[hmg_ai_faq style="list"]

<h4>Cards Style</h4>
[hmg_ai_faq style="cards"]

<h3>Table of Contents Styles</h3>
<h4>Numbered Style</h4>
[hmg_ai_toc style="numbered"]

<h4>Minimal Style</h4>
[hmg_ai_toc style="minimal"]

<h4>Sidebar Style</h4>
[hmg_ai_toc style="sidebar"]

<h3>Audio Player Styles</h3>
<h4>Player Style (Default)</h4>
[hmg_ai_audio style="player"]

<h4>Compact Style</h4>
[hmg_ai_audio style="compact"]

<h4>Minimal Style</h4>
[hmg_ai_audio style="minimal"]
';
}

/**
 * Get test takeaways data
 */
function get_test_takeaways_data() {
    return json_encode([
        "AI-powered content marketing increases engagement by up to 73% compared to traditional methods",
        "Personalized content recommendations can boost conversion rates by 19% on average", 
        "Automated content optimization saves content creators 15-20 hours per week",
        "Machine learning algorithms can predict trending topics 48 hours before they peak",
        "AI-generated meta descriptions improve click-through rates by 25% in search results"
    ]);
}

/**
 * Get test FAQ data
 */
function get_test_faq_data() {
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
            "answer" => "Absolutely! Modern AI tools are designed to be accessible and affordable for businesses of all sizes. Small businesses can particularly benefit from AI\'s ability to automate time-consuming tasks, provide insights that were previously only available to large enterprises, and compete more effectively by delivering personalized experiences that rival those of bigger competitors."
        ],
        [
            "question" => "How do you measure the success of AI-driven content marketing?",
            "answer" => "Success metrics include: engagement rates (likes, shares, comments), conversion rates, time spent on content, click-through rates, lead generation quality, customer lifetime value, and ROI. AI tools provide detailed analytics and can track micro-conversions throughout the customer journey, offering more granular insights than traditional measurement methods."
        ]
    ]);
}

/**
 * Get test TOC data
 */
function get_test_toc_data() {
    return json_encode([
        [
            "title" => "Introduction",
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
}

// If running in WordPress admin, create the test post
if (is_admin() && isset($_GET['create_hmg_test_post'])) {
    $result = create_hmg_ai_test_post();
    
    if ($result['success']) {
        add_action('admin_notices', function() use ($result) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Success!</strong> Test post created successfully.</p>';
            echo '<p><a href="' . esc_url($result['post_url']) . '" target="_blank">View Post</a> | ';
            echo '<a href="' . esc_url($result['edit_url']) . '">Edit Post</a></p>';
            echo '</div>';
        });
    } else {
        add_action('admin_notices', function() use ($result) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>Error:</strong> ' . esc_html($result['error']) . '</p>';
            echo '</div>';
        });
    }
}

// Add admin menu item for easy testing
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'HMG AI Shortcode Test',
        'HMG AI Test Post',
        'manage_options',
        'hmg-ai-test',
        'hmg_ai_test_page'
    );
});

function hmg_ai_test_page() {
    ?>
    <div class="wrap">
        <h1>HMG AI Blog Enhancer - Shortcode Testing</h1>
        
        <div class="card">
            <h2>Create Test Post</h2>
            <p>Click the button below to create a comprehensive test post that demonstrates all HMG AI shortcode functionality.</p>
            
            <p>
                <a href="<?php echo esc_url(admin_url('tools.php?page=hmg-ai-test&create_hmg_test_post=1')); ?>" 
                   class="button button-primary">
                    Create Test Post
                </a>
            </p>
        </div>
        
        <div class="card">
            <h2>Available Shortcodes</h2>
            
            <h3>Key Takeaways</h3>
            <code>[hmg_ai_takeaways]</code> - Default style<br>
            <code>[hmg_ai_takeaways style="numbered"]</code> - Numbered style<br>
            <code>[hmg_ai_takeaways style="cards"]</code> - Cards style<br>
            <code>[hmg_ai_takeaways style="highlights"]</code> - Highlights style<br>
            
            <h3>FAQ Section</h3>
            <code>[hmg_ai_faq]</code> - Accordion style (default)<br>
            <code>[hmg_ai_faq style="list"]</code> - List style<br>
            <code>[hmg_ai_faq style="cards"]</code> - Cards style<br>
            
            <h3>Table of Contents</h3>
            <code>[hmg_ai_toc]</code> - Numbered style (default)<br>
            <code>[hmg_ai_toc style="horizontal"]</code> - Horizontal style<br>
            <code>[hmg_ai_toc style="minimal"]</code> - Minimal style<br>
            <code>[hmg_ai_toc style="sidebar"]</code> - Sidebar style<br>
            
            <h3>Audio Player</h3>
            <code>[hmg_ai_audio]</code> - Player style (default)<br>
            <code>[hmg_ai_audio style="compact"]</code> - Compact style<br>
            <code>[hmg_ai_audio style="minimal"]</code> - Minimal style<br>
            <code>[hmg_ai_audio style="card"]</code> - Card style<br>
        </div>
        
        <div class="card">
            <h2>Testing Instructions</h2>
            <ol>
                <li>Create the test post using the button above</li>
                <li>View the post to see all shortcodes in action</li>
                <li>Test interactive features (FAQ accordion, TOC navigation, audio controls)</li>
                <li>Check responsive design on mobile devices</li>
                <li>Verify accessibility with keyboard navigation</li>
                <li>Test print functionality</li>
            </ol>
        </div>
    </div>
    
    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .card h2 {
            margin-top: 0;
            color: #23282d;
        }
        
        .card h3 {
            color: #23282d;
            margin-top: 20px;
        }
        
        .card code {
            background: #f1f1f1;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: Consolas, Monaco, monospace;
        }
    </style>
    <?php
}
?> 