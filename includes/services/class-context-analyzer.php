<?php
/**
 * Context Analyzer Service
 *
 * Analyzes website content to understand brand voice, style, and context
 * for more intelligent and consistent AI content generation.
 *
 * @link       https://haleymarketing.com
 * @since      1.2.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

class HMG_AI_Context_Analyzer {

    /**
     * Analyzed context data
     *
     * @since    1.2.0
     * @access   private
     * @var      array    $context_data    Stored context analysis data.
     */
    private $context_data;

    /**
     * Brand profile
     *
     * @since    1.2.0
     * @access   private
     * @var      array    $brand_profile    Extracted brand characteristics.
     */
    private $brand_profile;

    /**
     * Initialize the Context Analyzer
     *
     * @since    1.2.0
     */
    public function __construct() {
        $this->context_data = get_option('hmg_ai_context_data', array());
        $this->brand_profile = get_option('hmg_ai_brand_profile', array());
    }

    /**
     * Analyze website content to build brand profile
     *
     * @since    1.2.0
     * @param    int      $limit    Number of posts to analyze
     * @return   array              Analysis results
     */
    public function analyze_website_content($limit = 10) {
        $results = array(
            'posts_analyzed' => 0,
            'brand_voice' => array(),
            'common_topics' => array(),
            'writing_style' => array(),
            'vocabulary' => array(),
            'content_patterns' => array()
        );

        // Get recent published posts
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $posts = get_posts($args);

        if (empty($posts)) {
            return array(
                'success' => false,
                'message' => __('No posts found to analyze.', 'hmg-ai-blog-enhancer')
            );
        }

        foreach ($posts as $post) {
            $content = $post->post_content;
            $title = $post->post_title;
            
            // Clean content
            $clean_content = wp_strip_all_tags($content);
            $clean_content = html_entity_decode($clean_content);
            
            // Analyze tone and style
            $results['brand_voice'][] = $this->analyze_tone($clean_content);
            
            // Extract vocabulary patterns
            $results['vocabulary'][] = $this->analyze_vocabulary($clean_content);
            
            // Identify content patterns
            $results['content_patterns'][] = $this->analyze_content_patterns($content);
            
            // Extract topics and themes
            $results['common_topics'][] = $this->extract_topics($title, $clean_content);
            
            // Analyze writing style
            $results['writing_style'][] = $this->analyze_writing_style($clean_content);
            
            $results['posts_analyzed']++;
        }

        // Aggregate and summarize findings
        $brand_profile = $this->build_brand_profile($results);
        
        // Save to database
        $this->save_brand_profile($brand_profile);
        
        return array(
            'success' => true,
            'posts_analyzed' => $results['posts_analyzed'],
            'brand_profile' => $brand_profile
        );
    }

    /**
     * Analyze tone of content
     *
     * @since    1.2.0
     * @param    string    $content    Content to analyze
     * @return   array                 Tone characteristics
     */
    private function analyze_tone($content) {
        $tone = array(
            'formality' => 'neutral',
            'emotion' => 'neutral',
            'perspective' => 'third_person'
        );

        // Check formality
        $formal_indicators = array('therefore', 'moreover', 'furthermore', 'consequently', 'nevertheless');
        $informal_indicators = array("you're", "we're", "let's", "gonna", "wanna", "can't", "won't");
        
        $formal_count = 0;
        $informal_count = 0;
        
        foreach ($formal_indicators as $word) {
            $formal_count += substr_count(strtolower($content), $word);
        }
        
        foreach ($informal_indicators as $word) {
            $informal_count += substr_count(strtolower($content), $word);
        }
        
        if ($formal_count > $informal_count * 2) {
            $tone['formality'] = 'formal';
        } elseif ($informal_count > $formal_count * 2) {
            $tone['formality'] = 'informal';
        } else {
            $tone['formality'] = 'balanced';
        }

        // Check perspective (first, second, or third person)
        $first_person = substr_count(strtolower($content), ' i ') + substr_count(strtolower($content), ' we ');
        $second_person = substr_count(strtolower($content), ' you ');
        $third_person = substr_count(strtolower($content), ' they ') + substr_count(strtolower($content), ' it ');
        
        if ($first_person > max($second_person, $third_person)) {
            $tone['perspective'] = 'first_person';
        } elseif ($second_person > max($first_person, $third_person)) {
            $tone['perspective'] = 'second_person';
        } else {
            $tone['perspective'] = 'third_person';
        }

        // Check emotional tone
        $positive_words = array('great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'love', 'perfect');
        $negative_words = array('bad', 'terrible', 'awful', 'hate', 'worst', 'horrible', 'poor');
        
        $positive_count = 0;
        $negative_count = 0;
        
        foreach ($positive_words as $word) {
            $positive_count += substr_count(strtolower($content), $word);
        }
        
        foreach ($negative_words as $word) {
            $negative_count += substr_count(strtolower($content), $word);
        }
        
        if ($positive_count > $negative_count * 2) {
            $tone['emotion'] = 'positive';
        } elseif ($negative_count > $positive_count * 2) {
            $tone['emotion'] = 'negative';
        } else {
            $tone['emotion'] = 'neutral';
        }

        return $tone;
    }

    /**
     * Analyze vocabulary patterns
     *
     * @since    1.2.0
     * @param    string    $content    Content to analyze
     * @return   array                 Vocabulary characteristics
     */
    private function analyze_vocabulary($content) {
        $words = str_word_count(strtolower($content), 1);
        $word_count = count($words);
        $unique_words = array_unique($words);
        
        // Calculate average word length
        $total_length = 0;
        foreach ($words as $word) {
            $total_length += strlen($word);
        }
        $avg_word_length = $word_count > 0 ? round($total_length / $word_count, 1) : 0;
        
        // Identify frequently used words (excluding common stop words)
        $stop_words = array('the', 'is', 'at', 'which', 'on', 'a', 'an', 'as', 'are', 'was', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'could', 'to', 'of', 'in', 'for', 'with', 'by', 'from', 'about', 'into', 'after', 'over', 'under', 'between', 'through', 'during', 'before', 'after', 'above', 'below', 'up', 'down', 'out', 'off', 'on', 'and', 'but', 'or', 'so', 'if', 'when', 'where', 'what', 'who', 'why', 'how', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'them', 'their', 'our', 'your');
        
        $filtered_words = array_diff($words, $stop_words);
        $word_frequency = array_count_values($filtered_words);
        arsort($word_frequency);
        $top_words = array_slice($word_frequency, 0, 10, true);
        
        return array(
            'avg_word_length' => $avg_word_length,
            'vocabulary_diversity' => count($unique_words) / max($word_count, 1),
            'top_words' => array_keys($top_words),
            'complexity' => $avg_word_length > 6 ? 'complex' : ($avg_word_length > 4 ? 'moderate' : 'simple')
        );
    }

    /**
     * Analyze content patterns
     *
     * @since    1.2.0
     * @param    string    $content    HTML content to analyze
     * @return   array                 Content pattern characteristics
     */
    private function analyze_content_patterns($content) {
        $patterns = array(
            'has_lists' => false,
            'has_headings' => false,
            'has_quotes' => false,
            'has_images' => false,
            'has_links' => false,
            'paragraph_count' => 0,
            'avg_paragraph_length' => 0
        );

        // Check for lists
        $patterns['has_lists'] = (strpos($content, '<ul') !== false) || (strpos($content, '<ol') !== false);
        
        // Check for headings
        $patterns['has_headings'] = preg_match('/<h[1-6]/', $content);
        
        // Check for quotes
        $patterns['has_quotes'] = (strpos($content, '<blockquote') !== false) || (strpos($content, '"') !== false);
        
        // Check for images
        $patterns['has_images'] = strpos($content, '<img') !== false;
        
        // Check for links
        $patterns['has_links'] = strpos($content, '<a ') !== false;
        
        // Count paragraphs
        $paragraphs = explode('</p>', $content);
        $patterns['paragraph_count'] = count($paragraphs) - 1;
        
        // Calculate average paragraph length
        $total_length = 0;
        $valid_paragraphs = 0;
        foreach ($paragraphs as $p) {
            $clean_p = wp_strip_all_tags($p);
            if (strlen(trim($clean_p)) > 10) {
                $total_length += str_word_count($clean_p);
                $valid_paragraphs++;
            }
        }
        
        $patterns['avg_paragraph_length'] = $valid_paragraphs > 0 ? round($total_length / $valid_paragraphs) : 0;
        
        return $patterns;
    }

    /**
     * Extract topics and themes
     *
     * @since    1.2.0
     * @param    string    $title      Post title
     * @param    string    $content    Post content
     * @return   array                 Extracted topics
     */
    private function extract_topics($title, $content) {
        $topics = array();
        
        // Get categories
        $categories = get_the_category(get_the_ID());
        foreach ($categories as $category) {
            $topics[] = $category->name;
        }
        
        // Get tags
        $tags = get_the_tags(get_the_ID());
        if ($tags) {
            foreach ($tags as $tag) {
                $topics[] = $tag->name;
            }
        }
        
        // Extract key phrases from title
        $title_words = explode(' ', $title);
        if (count($title_words) > 2) {
            $topics[] = $title;
        }
        
        return array_unique($topics);
    }

    /**
     * Analyze writing style
     *
     * @since    1.2.0
     * @param    string    $content    Content to analyze
     * @return   array                 Writing style characteristics
     */
    private function analyze_writing_style($content) {
        $sentences = preg_split('/[.!?]+/', $content);
        $sentence_count = count($sentences) - 1;
        
        // Calculate average sentence length
        $total_words = 0;
        foreach ($sentences as $sentence) {
            $total_words += str_word_count($sentence);
        }
        $avg_sentence_length = $sentence_count > 0 ? round($total_words / $sentence_count) : 0;
        
        // Determine style based on sentence length
        if ($avg_sentence_length < 15) {
            $style = 'concise';
        } elseif ($avg_sentence_length < 25) {
            $style = 'balanced';
        } else {
            $style = 'elaborate';
        }
        
        return array(
            'avg_sentence_length' => $avg_sentence_length,
            'style' => $style,
            'sentence_variety' => $this->calculate_sentence_variety($sentences)
        );
    }

    /**
     * Calculate sentence variety
     *
     * @since    1.2.0
     * @param    array    $sentences    Array of sentences
     * @return   string                 Variety level
     */
    private function calculate_sentence_variety($sentences) {
        $lengths = array();
        foreach ($sentences as $sentence) {
            $word_count = str_word_count($sentence);
            if ($word_count > 0) {
                $lengths[] = $word_count;
            }
        }
        
        if (empty($lengths)) {
            return 'none';
        }
        
        $std_dev = $this->calculate_std_deviation($lengths);
        
        if ($std_dev < 5) {
            return 'low';
        } elseif ($std_dev < 10) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    /**
     * Calculate standard deviation
     *
     * @since    1.2.0
     * @param    array    $values    Array of numeric values
     * @return   float                Standard deviation
     */
    private function calculate_std_deviation($values) {
        $count = count($values);
        if ($count < 2) {
            return 0;
        }
        
        $mean = array_sum($values) / $count;
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        $variance = $variance / ($count - 1);
        return sqrt($variance);
    }

    /**
     * Build brand profile from analysis results
     *
     * @since    1.2.0
     * @param    array    $results    Analysis results
     * @return   array                Brand profile
     */
    private function build_brand_profile($results) {
        $profile = array(
            'tone' => $this->aggregate_tone($results['brand_voice']),
            'vocabulary' => $this->aggregate_vocabulary($results['vocabulary']),
            'style' => $this->aggregate_style($results['writing_style']),
            'patterns' => $this->aggregate_patterns($results['content_patterns']),
            'topics' => $this->aggregate_topics($results['common_topics']),
            'guidelines' => array(),
            'last_updated' => current_time('mysql')
        );
        
        // Generate content guidelines based on analysis
        $profile['guidelines'] = $this->generate_content_guidelines($profile);
        
        return $profile;
    }

    /**
     * Aggregate tone characteristics
     *
     * @since    1.2.0
     * @param    array    $tones    Array of tone analyses
     * @return   array              Aggregated tone profile
     */
    private function aggregate_tone($tones) {
        if (empty($tones)) {
            return array();
        }
        
        $formality_counts = array_count_values(array_column($tones, 'formality'));
        $emotion_counts = array_count_values(array_column($tones, 'emotion'));
        $perspective_counts = array_count_values(array_column($tones, 'perspective'));
        
        return array(
            'primary_formality' => array_search(max($formality_counts), $formality_counts),
            'primary_emotion' => array_search(max($emotion_counts), $emotion_counts),
            'primary_perspective' => array_search(max($perspective_counts), $perspective_counts),
            'consistency' => $this->calculate_consistency($formality_counts)
        );
    }

    /**
     * Calculate consistency score
     *
     * @since    1.2.0
     * @param    array    $counts    Value counts
     * @return   string              Consistency level
     */
    private function calculate_consistency($counts) {
        if (empty($counts)) {
            return 'none';
        }
        
        $total = array_sum($counts);
        $max = max($counts);
        $ratio = $max / $total;
        
        if ($ratio > 0.8) {
            return 'high';
        } elseif ($ratio > 0.6) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Aggregate vocabulary characteristics
     *
     * @since    1.2.0
     * @param    array    $vocabularies    Array of vocabulary analyses
     * @return   array                     Aggregated vocabulary profile
     */
    private function aggregate_vocabulary($vocabularies) {
        if (empty($vocabularies)) {
            return array();
        }
        
        $avg_word_lengths = array_column($vocabularies, 'avg_word_length');
        $all_top_words = array();
        
        foreach ($vocabularies as $vocab) {
            $all_top_words = array_merge($all_top_words, $vocab['top_words']);
        }
        
        $word_frequency = array_count_values($all_top_words);
        arsort($word_frequency);
        
        return array(
            'avg_word_length' => round(array_sum($avg_word_lengths) / count($avg_word_lengths), 1),
            'signature_words' => array_slice(array_keys($word_frequency), 0, 20),
            'complexity' => $this->determine_complexity($avg_word_lengths)
        );
    }

    /**
     * Determine complexity level
     *
     * @since    1.2.0
     * @param    array    $lengths    Word lengths
     * @return   string              Complexity level
     */
    private function determine_complexity($lengths) {
        $avg = array_sum($lengths) / count($lengths);
        
        if ($avg > 6) {
            return 'complex';
        } elseif ($avg > 4.5) {
            return 'moderate';
        } else {
            return 'simple';
        }
    }

    /**
     * Aggregate writing style
     *
     * @since    1.2.0
     * @param    array    $styles    Array of style analyses
     * @return   array               Aggregated style profile
     */
    private function aggregate_style($styles) {
        if (empty($styles)) {
            return array();
        }
        
        $sentence_lengths = array_column($styles, 'avg_sentence_length');
        
        return array(
            'avg_sentence_length' => round(array_sum($sentence_lengths) / count($sentence_lengths)),
            'primary_style' => $this->determine_primary_style($styles),
            'sentence_variety' => $this->determine_average_variety($styles)
        );
    }

    /**
     * Determine primary writing style
     *
     * @since    1.2.0
     * @param    array    $styles    Style analyses
     * @return   string              Primary style
     */
    private function determine_primary_style($styles) {
        $style_counts = array_count_values(array_column($styles, 'style'));
        return array_search(max($style_counts), $style_counts);
    }

    /**
     * Determine average sentence variety
     *
     * @since    1.2.0
     * @param    array    $styles    Style analyses
     * @return   string              Average variety
     */
    private function determine_average_variety($styles) {
        $variety_counts = array_count_values(array_column($styles, 'sentence_variety'));
        return array_search(max($variety_counts), $variety_counts);
    }

    /**
     * Aggregate content patterns
     *
     * @since    1.2.0
     * @param    array    $patterns    Array of pattern analyses
     * @return   array                 Aggregated patterns
     */
    private function aggregate_patterns($patterns) {
        if (empty($patterns)) {
            return array();
        }
        
        $uses_lists = 0;
        $uses_headings = 0;
        $uses_quotes = 0;
        $uses_images = 0;
        $uses_links = 0;
        $paragraph_lengths = array();
        
        foreach ($patterns as $pattern) {
            if ($pattern['has_lists']) $uses_lists++;
            if ($pattern['has_headings']) $uses_headings++;
            if ($pattern['has_quotes']) $uses_quotes++;
            if ($pattern['has_images']) $uses_images++;
            if ($pattern['has_links']) $uses_links++;
            $paragraph_lengths[] = $pattern['avg_paragraph_length'];
        }
        
        $total = count($patterns);
        
        return array(
            'frequently_uses_lists' => ($uses_lists / $total) > 0.5,
            'frequently_uses_headings' => ($uses_headings / $total) > 0.7,
            'frequently_uses_quotes' => ($uses_quotes / $total) > 0.3,
            'frequently_uses_images' => ($uses_images / $total) > 0.5,
            'frequently_uses_links' => ($uses_links / $total) > 0.7,
            'avg_paragraph_length' => round(array_sum($paragraph_lengths) / count($paragraph_lengths))
        );
    }

    /**
     * Aggregate topics
     *
     * @since    1.2.0
     * @param    array    $topics    Array of topic lists
     * @return   array               Aggregated topics
     */
    private function aggregate_topics($topics) {
        $all_topics = array();
        
        foreach ($topics as $topic_list) {
            $all_topics = array_merge($all_topics, $topic_list);
        }
        
        $topic_frequency = array_count_values($all_topics);
        arsort($topic_frequency);
        
        return array_slice(array_keys($topic_frequency), 0, 10);
    }

    /**
     * Generate content guidelines
     *
     * @since    1.2.0
     * @param    array    $profile    Brand profile
     * @return   array                Content guidelines
     */
    private function generate_content_guidelines($profile) {
        $guidelines = array();
        
        // Tone guidelines
        if (isset($profile['tone']['primary_formality'])) {
            $guidelines[] = sprintf(
                'Use a %s tone in your writing',
                $profile['tone']['primary_formality']
            );
        }
        
        if (isset($profile['tone']['primary_perspective'])) {
            $guidelines[] = sprintf(
                'Write primarily in %s perspective',
                str_replace('_', ' ', $profile['tone']['primary_perspective'])
            );
        }
        
        // Style guidelines
        if (isset($profile['style']['primary_style'])) {
            $guidelines[] = sprintf(
                'Maintain a %s writing style',
                $profile['style']['primary_style']
            );
        }
        
        // Pattern guidelines
        if (isset($profile['patterns']['frequently_uses_lists']) && $profile['patterns']['frequently_uses_lists']) {
            $guidelines[] = 'Include bullet points or numbered lists when appropriate';
        }
        
        if (isset($profile['patterns']['frequently_uses_headings']) && $profile['patterns']['frequently_uses_headings']) {
            $guidelines[] = 'Use clear headings to structure content';
        }
        
        // Vocabulary guidelines
        if (isset($profile['vocabulary']['complexity'])) {
            $guidelines[] = sprintf(
                'Use %s vocabulary appropriate for the audience',
                $profile['vocabulary']['complexity']
            );
        }
        
        return $guidelines;
    }

    /**
     * Save brand profile to database
     *
     * @since    1.2.0
     * @param    array    $profile    Brand profile to save
     * @return   bool                 Success status
     */
    private function save_brand_profile($profile) {
        return update_option('hmg_ai_brand_profile', $profile);
    }

    /**
     * Get brand profile
     *
     * @since    1.2.0
     * @return   array    Brand profile
     */
    public function get_brand_profile() {
        return $this->brand_profile;
    }

    /**
     * Get content guidelines
     *
     * @since    1.2.0
     * @return   array    Content guidelines
     */
    public function get_content_guidelines() {
        if (isset($this->brand_profile['guidelines'])) {
            return $this->brand_profile['guidelines'];
        }
        return array();
    }

    /**
     * Get context for AI generation
     *
     * @since    1.2.0
     * @return   string    Context prompt for AI
     */
    public function get_ai_context() {
        if (empty($this->brand_profile)) {
            return '';
        }
        
        $context = "Brand Voice Context:\n";
        
        // Add tone information
        if (isset($this->brand_profile['tone'])) {
            $context .= sprintf(
                "- Tone: %s, %s, %s perspective\n",
                $this->brand_profile['tone']['primary_formality'] ?? 'balanced',
                $this->brand_profile['tone']['primary_emotion'] ?? 'neutral',
                str_replace('_', ' ', $this->brand_profile['tone']['primary_perspective'] ?? 'third person')
            );
        }
        
        // Add style information
        if (isset($this->brand_profile['style'])) {
            $context .= sprintf(
                "- Style: %s writing with average sentence length of %d words\n",
                $this->brand_profile['style']['primary_style'] ?? 'balanced',
                $this->brand_profile['style']['avg_sentence_length'] ?? 20
            );
        }
        
        // Add vocabulary information
        if (isset($this->brand_profile['vocabulary']['signature_words']) && !empty($this->brand_profile['vocabulary']['signature_words'])) {
            $context .= "- Frequently used terms: " . implode(', ', array_slice($this->brand_profile['vocabulary']['signature_words'], 0, 5)) . "\n";
        }
        
        // Add guidelines
        if (isset($this->brand_profile['guidelines']) && !empty($this->brand_profile['guidelines'])) {
            $context .= "\nContent Guidelines:\n";
            foreach ($this->brand_profile['guidelines'] as $guideline) {
                $context .= "- " . $guideline . "\n";
            }
        }
        
        return $context;
    }

    /**
     * Clear brand profile
     *
     * @since    1.2.0
     * @return   bool    Success status
     */
    public function clear_brand_profile() {
        delete_option('hmg_ai_brand_profile');
        delete_option('hmg_ai_context_data');
        $this->brand_profile = array();
        $this->context_data = array();
        return true;
    }
}
