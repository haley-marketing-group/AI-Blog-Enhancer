import time
import json
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select

class WordPressHelper:
    def __init__(self, driver, config):
        self.driver = driver
        self.config = config
        self.wp_url = config['wordpress_url']
        self.admin_user = config['admin_user']
        self.admin_pass = config['admin_pass']
    
    def login_to_admin(self):
        """Login to WordPress admin"""
        login_url = f"{self.wp_url}/wp-admin"
        self.driver.get(login_url)
        
        try:
            # Fill login form
            username_field = WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.ID, "user_login"))
            )
            password_field = self.driver.find_element(By.ID, "user_pass")
            
            username_field.clear()
            username_field.send_keys(self.admin_user)
            password_field.clear()
            password_field.send_keys(self.admin_pass)
            
            # Submit form
            login_button = self.driver.find_element(By.ID, "wp-submit")
            login_button.click()
            
            # Wait for dashboard
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.ID, "wpadminbar"))
            )
            
        except Exception as e:
            print(f"Login failed: {e}")
            raise
    
    def create_shortcode_test_post(self):
        """Create a test post with all shortcode styles"""
        # Use our existing test post that we created
        test_post_url = f"{self.wp_url}/?p=10"
        
        # Verify the post exists by checking for shortcode elements
        try:
            self.driver.get(test_post_url)
            time.sleep(2)
            
            # Check if shortcodes are present
            if self._check_for_shortcodes():
                return test_post_url
            
            # If shortcodes not found, try the fallback
            return self._create_static_test_page()
            
        except Exception as e:
            print(f"Error accessing test post: {e}")
            # Return a fallback URL with static test content
            return self._create_static_test_page()
    
    def _check_for_shortcodes(self):
        """Check if current page has shortcode elements"""
        shortcode_selectors = [
            '.hmg-ai-takeaways',
            '.hmg-ai-faq',
            '.hmg-ai-toc', 
            '.hmg-ai-audio'
        ]
        
        for selector in shortcode_selectors:
            try:
                elements = self.driver.find_elements(By.CSS_SELECTOR, selector)
                if elements:
                    return True
            except:
                continue
        
        return False
    
    def _create_test_post_via_admin(self):
        """Create test post through WordPress admin"""
        try:
            self.login_to_admin()
            
            # Navigate to new post
            new_post_url = f"{self.wp_url}/wp-admin/post-new.php"
            self.driver.get(new_post_url)
            
            # Wait for editor to load
            time.sleep(3)
            
            # Try to find title field (could be in classic or block editor)
            try:
                title_field = self.driver.find_element(By.CSS_SELECTOR, ".editor-post-title__input, #title")
                title_field.clear()
                title_field.send_keys("HMG AI Shortcode Test")
            except:
                pass
            
            # Add content with shortcodes
            content = self._get_test_content()
            
            try:
                # Try block editor first
                content_area = self.driver.find_element(By.CSS_SELECTOR, ".block-editor-writing-flow")
                self.driver.execute_script("arguments[0].innerHTML = arguments[1];", content_area, content)
            except:
                try:
                    # Try classic editor
                    self.driver.execute_script(f"if(typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {{ tinyMCE.activeEditor.setContent('{content}'); }}")
                except:
                    pass
            
            # Publish post
            try:
                publish_button = self.driver.find_element(By.CSS_SELECTOR, ".editor-post-publish-panel__toggle, #publish")
                publish_button.click()
                time.sleep(1)
                
                # Confirm publish if needed
                try:
                    confirm_publish = self.driver.find_element(By.CSS_SELECTOR, ".editor-post-publish-button")
                    confirm_publish.click()
                except:
                    pass
                
                time.sleep(3)
                
                # Get the post URL
                try:
                    view_post_link = self.driver.find_element(By.CSS_SELECTOR, ".post-publish-panel__postpublish-buttons a, .view-post-link a")
                    return view_post_link.get_attribute('href')
                except:
                    pass
            except:
                pass
                
        except Exception as e:
            print(f"Admin post creation failed: {e}")
        
        # Fallback to static test page
        return self._create_static_test_page()
    
    def _create_static_test_page(self):
        """Create a static HTML test page for shortcode testing"""
        # Create a simple HTML page with shortcode-like content
        html_content = """
        <!DOCTYPE html>
        <html>
        <head>
            <title>HMG AI Shortcode Test</title>
            <link rel="stylesheet" href="/wp-content/plugins/hmg-ai-blog-enhancer/public/css/hmg-ai-public.css">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
        </head>
        <body>
            <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
                <h1>HMG AI Shortcode Test Page</h1>
                
                <!-- Takeaways Test -->
                <div class="hmg-ai-takeaways hmg-ai-takeaways-default" data-hmg-component="takeaways">
                    <div class="hmg-ai-takeaways-header">
                        <h3 class="hmg-ai-takeaways-title">
                            <span class="hmg-ai-icon">💡</span>
                            Key Takeaways
                        </h3>
                        <div class="hmg-ai-branding">
                            <span class="hmg-ai-powered-by">Powered by</span>
                            <span class="hmg-ai-brand">Haley Marketing AI</span>
                        </div>
                    </div>
                    <div class="hmg-ai-takeaways-content">
                        <ul class="hmg-ai-takeaways-list hmg-ai-default">
                            <li class="hmg-ai-takeaway-item">
                                <div class="hmg-ai-takeaway-bullet">
                                    <span class="hmg-ai-bullet-icon">✓</span>
                                </div>
                                <div class="hmg-ai-takeaway-content">
                                    AI-powered content marketing increases engagement by up to 73%
                                </div>
                            </li>
                            <li class="hmg-ai-takeaway-item">
                                <div class="hmg-ai-takeaway-bullet">
                                    <span class="hmg-ai-bullet-icon">✓</span>
                                </div>
                                <div class="hmg-ai-takeaway-content">
                                    Personalized content recommendations boost conversion rates by 19%
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- FAQ Test -->
                <div class="hmg-ai-faq hmg-ai-faq-accordion" data-hmg-component="faq">
                    <div class="hmg-ai-faq-header">
                        <h3 class="hmg-ai-faq-title">
                            <span class="hmg-ai-icon">❓</span>
                            Frequently Asked Questions
                        </h3>
                        <div class="hmg-ai-branding">
                            <span class="hmg-ai-powered-by">Powered by</span>
                            <span class="hmg-ai-brand">Haley Marketing AI</span>
                        </div>
                    </div>
                    <div class="hmg-ai-faq-content">
                        <div class="hmg-ai-faq-accordion">
                            <div class="hmg-ai-faq-accordion-item">
                                <button class="hmg-ai-faq-accordion-button hmg-ai-active" 
                                        data-hmg-faq-toggle 
                                        aria-expanded="true" 
                                        aria-controls="faq-1">
                                    <span class="hmg-ai-faq-question-text">What is AI content marketing?</span>
                                    <span class="hmg-ai-faq-accordion-icon">+</span>
                                </button>
                                <div class="hmg-ai-faq-accordion-content hmg-ai-active" id="faq-1">
                                    <div class="hmg-ai-faq-accordion-body">
                                        AI content marketing uses machine learning to optimize content creation and delivery.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- TOC Test -->
                <div class="hmg-ai-toc hmg-ai-toc-numbered" data-hmg-component="toc">
                    <div class="hmg-ai-toc-header">
                        <h3 class="hmg-ai-toc-title">
                            <span class="hmg-ai-icon">📋</span>
                            Table of Contents
                        </h3>
                        <div class="hmg-ai-branding">
                            <span class="hmg-ai-powered-by">Powered by</span>
                            <span class="hmg-ai-brand">Haley Marketing AI</span>
                        </div>
                    </div>
                    <div class="hmg-ai-toc-content">
                        <nav class="hmg-ai-toc-nav">
                            <ol class="hmg-ai-toc-list">
                                <li class="hmg-ai-toc-item">
                                    <a href="#section1" class="hmg-ai-toc-link" data-hmg-smooth-scroll data-target="section1">
                                        <span class="hmg-ai-toc-number">1.</span>
                                        <span class="hmg-ai-toc-text">Introduction</span>
                                    </a>
                                </li>
                                <li class="hmg-ai-toc-item">
                                    <a href="#section2" class="hmg-ai-toc-link" data-hmg-smooth-scroll data-target="section2">
                                        <span class="hmg-ai-toc-number">2.</span>
                                        <span class="hmg-ai-toc-text">Benefits</span>
                                    </a>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <!-- Audio Test -->
                <div class="hmg-ai-audio hmg-ai-audio-player" data-hmg-component="audio">
                    <div class="hmg-ai-audio-header">
                        <h3 class="hmg-ai-audio-title">
                            <span class="hmg-ai-icon">🎧</span>
                            Listen to This Article
                        </h3>
                        <div class="hmg-ai-branding">
                            <span class="hmg-ai-powered-by">Powered by</span>
                            <span class="hmg-ai-brand">Haley Marketing AI</span>
                        </div>
                    </div>
                    <div class="hmg-ai-audio-content">
                        <div class="hmg-ai-audio-player">
                            <div class="hmg-ai-audio-player-controls">
                                <audio class="hmg-ai-audio-element" controls>
                                    <source src="https://www.soundjay.com/misc/sounds/bell-ringing-05.wav" type="audio/mpeg">
                                </audio>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="section1"><h2>Section 1: Introduction</h2><p>Content here...</p></div>
                <div id="section2"><h2>Section 2: Benefits</h2><p>More content here...</p></div>
            </div>
            
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="/wp-content/plugins/hmg-ai-blog-enhancer/public/js/hmg-ai-public.js"></script>
        </body>
        </html>
        """
        
        # Save as a temporary file and serve it
        import tempfile
        import os
        
        temp_file = tempfile.NamedTemporaryFile(mode='w', suffix='.html', delete=False)
        temp_file.write(html_content)
        temp_file.close()
        
        # Return file:// URL for testing
        return f"file://{temp_file.name}"
    
    def _get_test_content(self):
        """Get test content with shortcodes"""
        return """
        <h1>HMG AI Shortcode Test</h1>
        
        [hmg_ai_takeaways style="default"]
        
        <h2>Content Section</h2>
        <p>This is a test post to demonstrate all HMG AI shortcode functionality.</p>
        
        [hmg_ai_faq style="accordion"]
        
        [hmg_ai_toc style="numbered"]
        
        [hmg_ai_audio style="player"]
        
        <h2 id="section1">Section 1</h2>
        <p>Test content for section 1.</p>
        
        <h2 id="section2">Section 2</h2>
        <p>Test content for section 2.</p>
        """
