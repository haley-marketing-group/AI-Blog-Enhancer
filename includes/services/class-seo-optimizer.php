<?php
/**
 * SEO Optimizer Service
 *
 * Optimizes AI-generated content for search engines with meta descriptions,
 * keywords, schema markup, readability scoring, and more.
 *
 * @link       https://haleymarketing.com
 * @since      1.3.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

class HMG_AI_SEO_Optimizer {

    /**
     * AI Service Manager instance
     *
     * @since    1.3.0
     * @access   private
     * @var      HMG_AI_Service_Manager    $ai_service    AI service manager.
     */
    private $ai_service;

    /**
     * SEO configuration options
     *
     * @since    1.3.0
     * @access   private
     * @var      array    $seo_options    SEO configuration.
     */
    private $seo_options;

    /**
     * Initialize the SEO Optimizer
     *
     * @since    1.3.0
     */
    public function __construct() {
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        $this->seo_options = array(
            'enable_meta_generation' => $options['seo_enable_meta'] ?? true,
            'enable_keyword_optimization' => $options['seo_enable_keywords'] ?? true,
            'enable_schema_markup' => $options['seo_enable_schema'] ?? true,
            'enable_readability_scoring' => $options['seo_enable_readability'] ?? true,
            'enable_internal_linking' => $options['seo_enable_linking'] ?? true,
            'target_keyword_density' => $options['seo_keyword_density'] ?? 1.5,
            'target_readability_score' => $options['seo_readability_target'] ?? 60,
            'meta_description_length' => $options['seo_meta_length'] ?? 155
        );
        
        // Initialize AI service if enabled
        if ($this->seo_options['enable_meta_generation'] || $this->seo_options['enable_keyword_optimization']) {
            if (class_exists('HMG_AI_Service_Manager')) {
                $this->ai_service = new HMG_AI_Service_Manager();
            }
        }
    }

    /**
     * Optimize content for SEO
     *
     * @since    1.3.0
     * @param    string    $content       Content to optimize
     * @param    string    $title         Post title
     * @param    int       $post_id       Post ID
     * @param    array     $options       Additional options
     * @return   array                    Optimization results
     */
    public function optimize_content($content, $title = '', $post_id = 0, $options = array()) {
        $results = array(
            'success' => true,
            'original_content' => $content,
            'optimized_content' => $content,
            'meta_description' => '',
            'keywords' => array(),
            'schema_markup' => '',
            'readability_score' => 0,
            'internal_links' => array(),
            'seo_title' => $title,
            'suggestions' => array()
        );

        try {
            // 1. Generate meta description
            if ($this->seo_options['enable_meta_generation']) {
                $results['meta_description'] = $this->generate_meta_description($content, $title);
            }

            // 2. Extract and optimize keywords
            if ($this->seo_options['enable_keyword_optimization']) {
                $keyword_data = $this->optimize_keywords($content, $title);
                $results['keywords'] = $keyword_data['keywords'];
                $results['optimized_content'] = $keyword_data['optimized_content'];
                $results['keyword_density'] = $keyword_data['density'];
            }

            // 3. Generate schema markup
            if ($this->seo_options['enable_schema_markup']) {
                $results['schema_markup'] = $this->generate_schema_markup($content, $title, $post_id);
            }

            // 4. Calculate readability score
            if ($this->seo_options['enable_readability_scoring']) {
                $readability_data = $this->calculate_readability($content);
                $results['readability_score'] = $readability_data['score'];
                $results['readability_grade'] = $readability_data['grade'];
                $results['readability_issues'] = $readability_data['issues'];
            }

            // 5. Suggest internal links
            if ($this->seo_options['enable_internal_linking']) {
                $results['internal_links'] = $this->suggest_internal_links($content, $post_id);
            }

            // 6. Optimize title for SEO
            $results['seo_title'] = $this->optimize_title($title, $results['keywords']);

            // 7. Generate SEO suggestions
            $results['suggestions'] = $this->generate_seo_suggestions($results);

            // Save SEO data as post meta
            if ($post_id > 0) {
                $this->save_seo_data($post_id, $results);
            }

        } catch (Exception $e) {
            $results['success'] = false;
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Generate meta description using AI
     *
     * @since    1.3.0
     * @param    string    $content    Content to summarize
     * @param    string    $title      Post title
     * @return   string                Meta description
     */
    private function generate_meta_description($content, $title) {
        if (!$this->ai_service) {
            return $this->generate_fallback_meta_description($content);
        }

        // Clean content for AI processing
        $clean_content = wp_strip_all_tags($content);
        $clean_content = substr($clean_content, 0, 2000); // Limit for AI processing

        // Create prompt for meta description
        $prompt = sprintf(
            "Title: %s\n\nContent: %s\n\nGenerate a compelling meta description (max %d characters) that summarizes this content and encourages clicks from search results. Include relevant keywords naturally.",
            $title,
            $clean_content,
            $this->seo_options['meta_description_length']
        );

        $result = $this->ai_service->generate_content('summary', $prompt);

        if ($result['success']) {
            $meta_desc = $result['content'];
            // Ensure proper length
            if (strlen($meta_desc) > $this->seo_options['meta_description_length']) {
                $meta_desc = substr($meta_desc, 0, $this->seo_options['meta_description_length'] - 3) . '...';
            }
            return $meta_desc;
        }

        return $this->generate_fallback_meta_description($content);
    }

    /**
     * Generate fallback meta description without AI
     *
     * @since    1.3.0
     * @param    string    $content    Content to summarize
     * @return   string                Meta description
     */
    private function generate_fallback_meta_description($content) {
        $clean_content = wp_strip_all_tags($content);
        $clean_content = preg_replace('/\s+/', ' ', $clean_content);
        
        // Get first 2-3 sentences
        $sentences = preg_split('/[.!?]+/', $clean_content);
        $meta_desc = '';
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;
            
            if (strlen($meta_desc . ' ' . $sentence) <= $this->seo_options['meta_description_length']) {
                $meta_desc .= ($meta_desc ? ' ' : '') . $sentence . '.';
            } else {
                break;
            }
        }
        
        if (empty($meta_desc)) {
            $meta_desc = substr($clean_content, 0, $this->seo_options['meta_description_length'] - 3) . '...';
        }
        
        return $meta_desc;
    }

    /**
     * Extract and optimize keywords
     *
     * @since    1.3.0
     * @param    string    $content    Content to analyze
     * @param    string    $title      Post title
     * @return   array                 Keyword data
     */
    private function optimize_keywords($content, $title) {
        $clean_content = wp_strip_all_tags($content);
        $full_text = $title . ' ' . $clean_content;
        
        // Extract keywords
        $keywords = $this->extract_keywords($full_text);
        
        // Calculate current density
        $word_count = str_word_count($clean_content);
        $current_density = array();
        
        foreach ($keywords as $keyword) {
            $count = substr_count(strtolower($clean_content), strtolower($keyword));
            $density = ($count / $word_count) * 100;
            $current_density[$keyword] = $density;
        }
        
        // Optimize content for keywords if needed
        $optimized_content = $content;
        $suggestions = array();
        
        foreach ($keywords as $keyword) {
            if (isset($current_density[$keyword])) {
                if ($current_density[$keyword] < $this->seo_options['target_keyword_density'] - 0.5) {
                    $suggestions[] = sprintf(
                        'Consider adding "%s" %d more times (current density: %.1f%%, target: %.1f%%)',
                        $keyword,
                        ceil(($this->seo_options['target_keyword_density'] - $current_density[$keyword]) * $word_count / 100),
                        $current_density[$keyword],
                        $this->seo_options['target_keyword_density']
                    );
                } elseif ($current_density[$keyword] > $this->seo_options['target_keyword_density'] + 1) {
                    $suggestions[] = sprintf(
                        'Consider reducing "%s" usage (current density: %.1f%%, target: %.1f%%)',
                        $keyword,
                        $current_density[$keyword],
                        $this->seo_options['target_keyword_density']
                    );
                }
            }
        }
        
        return array(
            'keywords' => $keywords,
            'density' => $current_density,
            'optimized_content' => $optimized_content,
            'suggestions' => $suggestions
        );
    }

    /**
     * Extract keywords from text
     *
     * @since    1.3.0
     * @param    string    $text    Text to analyze
     * @return   array              Top keywords
     */
    private function extract_keywords($text) {
        // Remove common stop words
        $stop_words = array(
            'the', 'is', 'at', 'which', 'on', 'a', 'an', 'as', 'are', 'was', 'were',
            'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
            'could', 'should', 'may', 'might', 'must', 'can', 'to', 'of', 'in', 'for',
            'with', 'by', 'from', 'about', 'into', 'after', 'over', 'under', 'between',
            'through', 'during', 'before', 'above', 'below', 'up', 'down', 'out', 'off',
            'and', 'but', 'or', 'so', 'if', 'when', 'where', 'what', 'who', 'why', 'how',
            'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they',
            'them', 'their', 'our', 'your', 'its', 'my', 'me', 'him', 'her', 'us'
        );
        
        // Convert to lowercase and split into words
        $words = str_word_count(strtolower($text), 1);
        
        // Filter out stop words and short words
        $filtered_words = array_filter($words, function($word) use ($stop_words) {
            return strlen($word) > 3 && !in_array($word, $stop_words);
        });
        
        // Count word frequency
        $word_freq = array_count_values($filtered_words);
        arsort($word_freq);
        
        // Get top keywords
        $keywords = array_slice(array_keys($word_freq), 0, 5);
        
        // Look for 2-word phrases (bigrams)
        $bigrams = array();
        for ($i = 0; $i < count($words) - 1; $i++) {
            if (!in_array($words[$i], $stop_words) && !in_array($words[$i + 1], $stop_words)) {
                $bigram = $words[$i] . ' ' . $words[$i + 1];
                if (!isset($bigrams[$bigram])) {
                    $bigrams[$bigram] = 0;
                }
                $bigrams[$bigram]++;
            }
        }
        
        arsort($bigrams);
        $top_bigrams = array_slice(array_keys($bigrams), 0, 3);
        
        // Combine single words and bigrams
        return array_merge($keywords, $top_bigrams);
    }

    /**
     * Generate schema markup
     *
     * @since    1.3.0
     * @param    string    $content    Content
     * @param    string    $title      Title
     * @param    int       $post_id    Post ID
     * @return   string                Schema markup JSON-LD
     */
    private function generate_schema_markup($content, $title, $post_id = 0) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $title,
            'description' => substr(wp_strip_all_tags($content), 0, 160),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'author' => array(
                '@type' => 'Organization',
                'name' => 'Haley Marketing',
                'url' => get_site_url()
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => 'Haley Marketing',
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_url() . '/wp-content/plugins/AI-Blog-Enhancer/assets/logo.png'
                )
            )
        );
        
        // Add image if available
        if ($post_id && has_post_thumbnail($post_id)) {
            $image_id = get_post_thumbnail_id($post_id);
            $image_url = wp_get_attachment_image_src($image_id, 'full')[0];
            $schema['image'] = $image_url;
        }
        
        // Add word count and reading time
        $word_count = str_word_count(wp_strip_all_tags($content));
        $reading_time = ceil($word_count / 200); // Assuming 200 words per minute
        
        $schema['wordCount'] = $word_count;
        $schema['timeRequired'] = 'PT' . $reading_time . 'M';
        
        // Add article sections if headings exist
        if (preg_match_all('/<h[2-3][^>]*>(.*?)<\/h[2-3]>/i', $content, $matches)) {
            $schema['articleSection'] = array_map('wp_strip_all_tags', $matches[1]);
        }
        
        return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Calculate readability score
     *
     * @since    1.3.0
     * @param    string    $content    Content to analyze
     * @return   array                 Readability data
     */
    private function calculate_readability($content) {
        $clean_content = wp_strip_all_tags($content);
        
        // Calculate Flesch Reading Ease score
        $sentences = max(1, preg_match_all('/[.!?]+/', $clean_content, $matches));
        $words = str_word_count($clean_content);
        $syllables = $this->count_syllables($clean_content);
        
        if ($words == 0) {
            return array(
                'score' => 0,
                'grade' => 'N/A',
                'issues' => array('No content to analyze')
            );
        }
        
        // Flesch Reading Ease formula
        $score = 206.835 - 1.015 * ($words / $sentences) - 84.6 * ($syllables / $words);
        $score = max(0, min(100, $score));
        
        // Determine grade level
        if ($score >= 90) {
            $grade = 'Very Easy (5th grade)';
        } elseif ($score >= 80) {
            $grade = 'Easy (6th grade)';
        } elseif ($score >= 70) {
            $grade = 'Fairly Easy (7th grade)';
        } elseif ($score >= 60) {
            $grade = 'Standard (8-9th grade)';
        } elseif ($score >= 50) {
            $grade = 'Fairly Difficult (10-12th grade)';
        } elseif ($score >= 30) {
            $grade = 'Difficult (College)';
        } else {
            $grade = 'Very Difficult (Graduate)';
        }
        
        // Identify issues
        $issues = array();
        
        // Check sentence length
        $avg_words_per_sentence = $words / $sentences;
        if ($avg_words_per_sentence > 20) {
            $issues[] = sprintf('Long sentences (avg: %.1f words). Consider breaking them up.', $avg_words_per_sentence);
        }
        
        // Check paragraph length
        $paragraphs = explode("\n\n", $clean_content);
        $long_paragraphs = 0;
        foreach ($paragraphs as $paragraph) {
            if (str_word_count($paragraph) > 150) {
                $long_paragraphs++;
            }
        }
        if ($long_paragraphs > 0) {
            $issues[] = sprintf('%d paragraph(s) are too long. Keep them under 150 words.', $long_paragraphs);
        }
        
        // Check for passive voice (simple check)
        $passive_indicators = array('was', 'were', 'been', 'being', 'is', 'are', 'am');
        $passive_count = 0;
        foreach ($passive_indicators as $word) {
            $passive_count += substr_count(strtolower($clean_content), ' ' . $word . ' ');
        }
        $passive_percentage = ($passive_count / $sentences) * 100;
        if ($passive_percentage > 20) {
            $issues[] = sprintf('High passive voice usage (%.1f%%). Use more active voice.', $passive_percentage);
        }
        
        // Check transition words
        $transition_words = array('however', 'therefore', 'moreover', 'furthermore', 'additionally', 'consequently', 'nevertheless', 'meanwhile');
        $has_transitions = false;
        foreach ($transition_words as $word) {
            if (stripos($clean_content, $word) !== false) {
                $has_transitions = true;
                break;
            }
        }
        if (!$has_transitions && $sentences > 5) {
            $issues[] = 'Consider adding transition words to improve flow.';
        }
        
        return array(
            'score' => round($score, 1),
            'grade' => $grade,
            'issues' => $issues,
            'stats' => array(
                'sentences' => $sentences,
                'words' => $words,
                'syllables' => $syllables,
                'avg_words_per_sentence' => round($avg_words_per_sentence, 1)
            )
        );
    }

    /**
     * Count syllables in text
     *
     * @since    1.3.0
     * @param    string    $text    Text to analyze
     * @return   int                 Syllable count
     */
    private function count_syllables($text) {
        $words = str_word_count(strtolower($text), 1);
        $total_syllables = 0;
        
        foreach ($words as $word) {
            // Remove non-alphabetic characters
            $word = preg_replace('/[^a-z]/', '', $word);
            
            // Basic syllable counting (not perfect but good enough)
            $syllables = 0;
            $previous_was_vowel = false;
            
            for ($i = 0; $i < strlen($word); $i++) {
                $is_vowel = in_array($word[$i], array('a', 'e', 'i', 'o', 'u', 'y'));
                
                if ($is_vowel && !$previous_was_vowel) {
                    $syllables++;
                }
                
                $previous_was_vowel = $is_vowel;
            }
            
            // Every word has at least one syllable
            if ($syllables == 0) {
                $syllables = 1;
            }
            
            // Adjust for silent e
            if (substr($word, -1) == 'e' && $syllables > 1) {
                $syllables--;
            }
            
            $total_syllables += $syllables;
        }
        
        return $total_syllables;
    }

    /**
     * Suggest internal links
     *
     * @since    1.3.0
     * @param    string    $content    Content to analyze
     * @param    int       $post_id    Current post ID
     * @return   array                 Suggested links
     */
    private function suggest_internal_links($content, $post_id = 0) {
        $suggestions = array();
        
        // Extract keywords from content
        $keywords = $this->extract_keywords(wp_strip_all_tags($content));
        
        foreach ($keywords as $keyword) {
            // Search for related posts
            $args = array(
                's' => $keyword,
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 3,
                'post__not_in' => array($post_id),
                'orderby' => 'relevance'
            );
            
            $related_posts = get_posts($args);
            
            foreach ($related_posts as $post) {
                // Check if this keyword appears in the content
                if (stripos($content, $keyword) !== false) {
                    $suggestions[] = array(
                        'keyword' => $keyword,
                        'post_id' => $post->ID,
                        'title' => $post->post_title,
                        'url' => get_permalink($post->ID),
                        'relevance' => $this->calculate_relevance($content, $post->post_content)
                    );
                }
            }
        }
        
        // Sort by relevance
        usort($suggestions, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });
        
        // Return top 5 suggestions
        return array_slice($suggestions, 0, 5);
    }

    /**
     * Calculate relevance between two pieces of content
     *
     * @since    1.3.0
     * @param    string    $content1    First content
     * @param    string    $content2    Second content
     * @return   float                  Relevance score
     */
    private function calculate_relevance($content1, $content2) {
        $words1 = array_unique(str_word_count(strtolower($content1), 1));
        $words2 = array_unique(str_word_count(strtolower($content2), 1));
        
        $common_words = array_intersect($words1, $words2);
        $total_words = count(array_unique(array_merge($words1, $words2)));
        
        if ($total_words == 0) {
            return 0;
        }
        
        return count($common_words) / $total_words;
    }

    /**
     * Optimize title for SEO
     *
     * @since    1.3.0
     * @param    string    $title       Original title
     * @param    array     $keywords    Target keywords
     * @return   string                 Optimized title
     */
    private function optimize_title($title, $keywords) {
        // Check if title already contains primary keyword
        if (!empty($keywords) && stripos($title, $keywords[0]) === false) {
            // Suggest adding primary keyword to title
            return sprintf('%s: %s', $keywords[0], $title);
        }
        
        // Check title length (50-60 characters is optimal)
        if (strlen($title) > 60) {
            // Truncate but keep it meaningful
            $words = explode(' ', $title);
            $optimized = '';
            
            foreach ($words as $word) {
                if (strlen($optimized . ' ' . $word) <= 57) {
                    $optimized .= ($optimized ? ' ' : '') . $word;
                } else {
                    break;
                }
            }
            
            return $optimized . '...';
        }
        
        return $title;
    }

    /**
     * Generate SEO suggestions
     *
     * @since    1.3.0
     * @param    array    $results    Optimization results
     * @return   array                Suggestions
     */
    private function generate_seo_suggestions($results) {
        $suggestions = array();
        
        // Title suggestions
        if (strlen($results['seo_title']) < 30) {
            $suggestions[] = array(
                'type' => 'title',
                'priority' => 'high',
                'message' => 'Title is too short. Aim for 50-60 characters.'
            );
        } elseif (strlen($results['seo_title']) > 60) {
            $suggestions[] = array(
                'type' => 'title',
                'priority' => 'medium',
                'message' => 'Title is too long. Keep it under 60 characters for best display in search results.'
            );
        }
        
        // Meta description suggestions
        if (strlen($results['meta_description']) < 120) {
            $suggestions[] = array(
                'type' => 'meta',
                'priority' => 'medium',
                'message' => 'Meta description is short. Use 150-160 characters for optimal display.'
            );
        }
        
        // Keyword suggestions
        if (empty($results['keywords'])) {
            $suggestions[] = array(
                'type' => 'keywords',
                'priority' => 'high',
                'message' => 'No focus keywords identified. Add relevant keywords to improve SEO.'
            );
        }
        
        // Readability suggestions
        if (isset($results['readability_score']) && $results['readability_score'] < $this->seo_options['target_readability_score']) {
            $suggestions[] = array(
                'type' => 'readability',
                'priority' => 'medium',
                'message' => sprintf(
                    'Readability score (%.1f) is below target (%.1f). Simplify sentences and use common words.',
                    $results['readability_score'],
                    $this->seo_options['target_readability_score']
                )
            );
        }
        
        // Internal linking suggestions
        if (empty($results['internal_links'])) {
            $suggestions[] = array(
                'type' => 'links',
                'priority' => 'low',
                'message' => 'No internal links suggested. Link to related content to improve SEO and user experience.'
            );
        } elseif (count($results['internal_links']) < 2) {
            $suggestions[] = array(
                'type' => 'links',
                'priority' => 'low',
                'message' => 'Add more internal links (2-4 recommended) to related content.'
            );
        }
        
        // Add keyword density suggestions if available
        if (isset($results['keyword_density'])) {
            foreach ($results['keyword_density'] as $keyword => $density) {
                if ($density < 0.5) {
                    $suggestions[] = array(
                        'type' => 'keyword_density',
                        'priority' => 'low',
                        'message' => sprintf('Keyword "%s" has low density (%.1f%%). Consider using it more frequently.', $keyword, $density)
                    );
                } elseif ($density > 3.0) {
                    $suggestions[] = array(
                        'type' => 'keyword_density',
                        'priority' => 'medium',
                        'message' => sprintf('Keyword "%s" may be overused (%.1f%%). Reduce usage to avoid keyword stuffing.', $keyword, $density)
                    );
                }
            }
        }
        
        return $suggestions;
    }

    /**
     * Save SEO data as post meta
     *
     * @since    1.3.0
     * @param    int      $post_id    Post ID
     * @param    array    $seo_data   SEO data
     * @return   bool                 Success status
     */
    private function save_seo_data($post_id, $seo_data) {
        // Save meta description
        if (!empty($seo_data['meta_description'])) {
            update_post_meta($post_id, '_hmg_ai_meta_description', $seo_data['meta_description']);
        }
        
        // Save keywords
        if (!empty($seo_data['keywords'])) {
            update_post_meta($post_id, '_hmg_ai_keywords', $seo_data['keywords']);
        }
        
        // Save readability score
        if (isset($seo_data['readability_score'])) {
            update_post_meta($post_id, '_hmg_ai_readability_score', $seo_data['readability_score']);
        }
        
        // Save SEO title
        if (!empty($seo_data['seo_title'])) {
            update_post_meta($post_id, '_hmg_ai_seo_title', $seo_data['seo_title']);
        }
        
        // Save schema markup
        if (!empty($seo_data['schema_markup'])) {
            update_post_meta($post_id, '_hmg_ai_schema_markup', $seo_data['schema_markup']);
        }
        
        // Save internal links suggestions
        if (!empty($seo_data['internal_links'])) {
            update_post_meta($post_id, '_hmg_ai_internal_links', $seo_data['internal_links']);
        }
        
        // Save SEO suggestions
        if (!empty($seo_data['suggestions'])) {
            update_post_meta($post_id, '_hmg_ai_seo_suggestions', $seo_data['suggestions']);
        }
        
        // Save optimization timestamp
        update_post_meta($post_id, '_hmg_ai_seo_optimized', current_time('mysql'));
        
        return true;
    }

    /**
     * Get SEO data for a post
     *
     * @since    1.3.0
     * @param    int    $post_id    Post ID
     * @return   array              SEO data
     */
    public function get_seo_data($post_id) {
        return array(
            'meta_description' => get_post_meta($post_id, '_hmg_ai_meta_description', true),
            'keywords' => get_post_meta($post_id, '_hmg_ai_keywords', true),
            'readability_score' => get_post_meta($post_id, '_hmg_ai_readability_score', true),
            'seo_title' => get_post_meta($post_id, '_hmg_ai_seo_title', true),
            'schema_markup' => get_post_meta($post_id, '_hmg_ai_schema_markup', true),
            'internal_links' => get_post_meta($post_id, '_hmg_ai_internal_links', true),
            'suggestions' => get_post_meta($post_id, '_hmg_ai_seo_suggestions', true),
            'optimized_date' => get_post_meta($post_id, '_hmg_ai_seo_optimized', true)
        );
    }

    /**
     * Output schema markup in page head
     *
     * @since    1.3.0
     */
    public function output_schema_markup() {
        if (is_singular('post')) {
            global $post;
            $schema = get_post_meta($post->ID, '_hmg_ai_schema_markup', true);
            if (!empty($schema)) {
                echo $schema . "\n";
            }
        }
    }

    /**
     * Add meta tags to page head
     *
     * @since    1.3.0
     */
    public function add_meta_tags() {
        if (is_singular('post')) {
            global $post;
            
            $meta_desc = get_post_meta($post->ID, '_hmg_ai_meta_description', true);
            if (!empty($meta_desc)) {
                echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
            }
            
            $keywords = get_post_meta($post->ID, '_hmg_ai_keywords', true);
            if (!empty($keywords) && is_array($keywords)) {
                echo '<meta name="keywords" content="' . esc_attr(implode(', ', $keywords)) . '">' . "\n";
            }
        }
    }
}
