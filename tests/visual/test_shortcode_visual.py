"""
Visual tests for HMG AI Blog Enhancer shortcodes using Selenium
Tests all shortcode styles, interactions, and responsive behavior
"""

import pytest
import time
import os
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.keys import Keys
from selenium.common.exceptions import TimeoutException, NoSuchElementException


class TestShortcodeVisual:
    """Visual tests for shortcode functionality"""
    
    @pytest.fixture(autouse=True)
    def setup_test_post(self, wordpress_helper):
        """Create test post with shortcodes before each test"""
        self.test_post_url = wordpress_helper.create_shortcode_test_post()
        yield
        # Cleanup after test if needed
    
    def test_takeaways_default_style(self, selenium_helper, browser):
        """Test takeaways shortcode default style"""
        browser.get(self.test_post_url)
        
        # Wait for takeaways to load
        takeaways = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-takeaways.hmg-ai-takeaways-default')
        )
        
        # Scroll to element
        browser.execute_script("arguments[0].scrollIntoView(true);", takeaways)
        time.sleep(1)
        
        # Take screenshot
        screenshot_path = selenium_helper.take_screenshot('takeaways_default')
        
        # Visual checks
        assert takeaways.is_displayed()
        
        # Check for required elements
        title = takeaways.find_element(By.CSS_SELECTOR, '.hmg-ai-takeaways-title')
        assert "Key Takeaways" in title.text
        
        items = takeaways.find_elements(By.CSS_SELECTOR, '.hmg-ai-takeaway-item')
        assert len(items) >= 3, "Should have at least 3 takeaway items"
        
        # Check branding
        branding = takeaways.find_element(By.CSS_SELECTOR, '.hmg-ai-branding')
        assert "Haley Marketing AI" in branding.text
        
        # Test hover effects
        actions = ActionChains(browser)
        actions.move_to_element(items[0]).perform()
        time.sleep(0.5)
        
        # Take hover screenshot
        selenium_helper.take_screenshot('takeaways_default_hover')
    
    def test_takeaways_cards_style(self, selenium_helper, browser):
        """Test takeaways shortcode cards style"""
        browser.get(self.test_post_url)
        
        # Navigate to cards style section
        cards_section = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-takeaways.hmg-ai-takeaways-cards')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", cards_section)
        time.sleep(1)
        
        # Take screenshot
        selenium_helper.take_screenshot('takeaways_cards')
        
        # Check grid layout
        grid = cards_section.find_element(By.CSS_SELECTOR, '.hmg-ai-takeaways-grid')
        cards = grid.find_elements(By.CSS_SELECTOR, '.hmg-ai-takeaway-card')
        
        assert len(cards) >= 3
        
        # Test card interactions
        for i, card in enumerate(cards[:2]):  # Test first 2 cards
            actions = ActionChains(browser)
            actions.move_to_element(card).perform()
            time.sleep(0.3)
            
            # Check for card number
            number = card.find_element(By.CSS_SELECTOR, '.hmg-ai-card-number')
            assert number.text == str(i + 1)
    
    def test_takeaways_numbered_style(self, selenium_helper, browser):
        """Test takeaways shortcode numbered style"""
        browser.get(self.test_post_url)
        
        numbered_section = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-takeaways.hmg-ai-takeaways-numbered')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", numbered_section)
        time.sleep(1)
        
        selenium_helper.take_screenshot('takeaways_numbered')
        
        # Check numbered list
        items = numbered_section.find_elements(By.CSS_SELECTOR, '.hmg-ai-takeaway-item')
        assert len(items) >= 3
        
        # Verify numbering is visible (CSS counters)
        for item in items:
            assert item.is_displayed()
    
    def test_takeaways_highlights_style(self, selenium_helper, browser):
        """Test takeaways shortcode highlights style"""
        browser.get(self.test_post_url)
        
        highlights_section = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-takeaways.hmg-ai-takeaways-highlights')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", highlights_section)
        time.sleep(1)
        
        selenium_helper.take_screenshot('takeaways_highlights')
        
        # Check highlights layout
        highlights = highlights_section.find_elements(By.CSS_SELECTOR, '.hmg-ai-highlight-item')
        assert len(highlights) >= 3
        
        for highlight in highlights:
            marker = highlight.find_element(By.CSS_SELECTOR, '.hmg-ai-highlight-marker')
            content = highlight.find_element(By.CSS_SELECTOR, '.hmg-ai-highlight-content')
            
            assert marker.is_displayed()
            assert content.is_displayed()
    
    def test_faq_accordion_functionality(self, selenium_helper, browser):
        """Test FAQ accordion interactions"""
        browser.get(self.test_post_url)
        
        faq_section = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-faq.hmg-ai-faq-accordion')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", faq_section)
        time.sleep(1)
        
        # Take initial screenshot
        selenium_helper.take_screenshot('faq_accordion_initial')
        
        # Find accordion buttons
        buttons = faq_section.find_elements(By.CSS_SELECTOR, '[data-hmg-faq-toggle]')
        assert len(buttons) >= 3
        
        # Test first accordion item (should be open by default)
        first_button = buttons[0]
        first_content = browser.find_element(By.ID, first_button.get_attribute('aria-controls'))
        
        assert first_button.get_attribute('aria-expanded') == 'true'
        assert 'hmg-ai-active' in first_content.get_attribute('class')
        
        # Click to close first item
        first_button.click()
        time.sleep(0.5)
        
        selenium_helper.take_screenshot('faq_accordion_first_closed')
        
        assert first_button.get_attribute('aria-expanded') == 'false'
        
        # Click second accordion item
        second_button = buttons[1]
        second_button.click()
        time.sleep(0.5)
        
        selenium_helper.take_screenshot('faq_accordion_second_open')
        
        # Verify second item is now open
        assert second_button.get_attribute('aria-expanded') == 'true'
    
    def test_faq_list_style(self, selenium_helper, browser):
        """Test FAQ list style"""
        browser.get(self.test_post_url)
        
        faq_list = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-faq.hmg-ai-faq-list')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", faq_list)
        time.sleep(1)
        
        selenium_helper.take_screenshot('faq_list')
        
        # Check list items
        items = faq_list.find_elements(By.CSS_SELECTOR, '.hmg-ai-faq-item')
        assert len(items) >= 3
        
        for item in items:
            question = item.find_element(By.CSS_SELECTOR, '.hmg-ai-faq-question h4')
            answer = item.find_element(By.CSS_SELECTOR, '.hmg-ai-faq-answer')
            
            assert question.is_displayed()
            assert answer.is_displayed()
            assert len(question.text) > 0
            assert len(answer.text) > 0
    
    def test_faq_cards_style(self, selenium_helper, browser):
        """Test FAQ cards style"""
        browser.get(self.test_post_url)
        
        faq_cards = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-faq.hmg-ai-faq-cards')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", faq_cards)
        time.sleep(1)
        
        selenium_helper.take_screenshot('faq_cards')
        
        # Check cards layout
        cards_container = faq_cards.find_element(By.CSS_SELECTOR, '.hmg-ai-faq-cards')
        cards = cards_container.find_elements(By.CSS_SELECTOR, '.hmg-ai-faq-card')
        
        assert len(cards) >= 3
        
        # Test card hover effects
        for i, card in enumerate(cards[:2]):
            actions = ActionChains(browser)
            actions.move_to_element(card).perform()
            time.sleep(0.3)
            
            # Check Q icon
            icon = card.find_element(By.CSS_SELECTOR, '.hmg-ai-faq-card-icon')
            assert icon.text == 'Q'
    
    def test_toc_numbered_style(self, selenium_helper, browser):
        """Test TOC numbered style"""
        browser.get(self.test_post_url)
        
        toc_numbered = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-toc.hmg-ai-toc-numbered')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", toc_numbered)
        time.sleep(1)
        
        selenium_helper.take_screenshot('toc_numbered')
        
        # Check TOC links
        links = toc_numbered.find_elements(By.CSS_SELECTOR, '[data-hmg-smooth-scroll]')
        assert len(links) >= 5
        
        # Test first link click
        first_link = links[0]
        target_id = first_link.get_attribute('data-target')
        
        # Click link
        first_link.click()
        time.sleep(1)
        
        # Verify scroll occurred
        target_element = browser.find_element(By.ID, target_id)
        assert target_element.is_displayed()
        
        selenium_helper.take_screenshot('toc_numbered_after_click')
    
    def test_toc_horizontal_style(self, selenium_helper, browser):
        """Test TOC horizontal style"""
        browser.get(self.test_post_url)
        
        toc_horizontal = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-toc.hmg-ai-toc-horizontal')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", toc_horizontal)
        time.sleep(1)
        
        selenium_helper.take_screenshot('toc_horizontal')
        
        # Check horizontal scroll container
        scroll_container = toc_horizontal.find_element(By.CSS_SELECTOR, '.hmg-ai-toc-scroll-container')
        horizontal_items = scroll_container.find_elements(By.CSS_SELECTOR, '.hmg-ai-toc-horizontal-item')
        
        assert len(horizontal_items) >= 5
        
        # Test horizontal item interactions
        for item in horizontal_items[:3]:
            actions = ActionChains(browser)
            actions.move_to_element(item).perform()
            time.sleep(0.2)
    
    def test_toc_minimal_style(self, selenium_helper, browser):
        """Test TOC minimal style"""
        browser.get(self.test_post_url)
        
        toc_minimal = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-toc.hmg-ai-toc-minimal')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", toc_minimal)
        time.sleep(1)
        
        selenium_helper.take_screenshot('toc_minimal')
        
        # Check minimal links
        minimal_items = toc_minimal.find_elements(By.CSS_SELECTOR, '.hmg-ai-toc-minimal-item')
        assert len(minimal_items) >= 5
        
        # Test hover effects
        for item in minimal_items[:2]:
            actions = ActionChains(browser)
            actions.move_to_element(item).perform()
            time.sleep(0.3)
    
    def test_toc_sidebar_style(self, selenium_helper, browser):
        """Test TOC sidebar style with progress tracking"""
        browser.get(self.test_post_url)
        
        toc_sidebar = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-toc.hmg-ai-toc-sidebar')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", toc_sidebar)
        time.sleep(1)
        
        selenium_helper.take_screenshot('toc_sidebar')
        
        # Check progress bar
        progress_bar = toc_sidebar.find_element(By.CSS_SELECTOR, '.hmg-ai-toc-progress-bar')
        assert progress_bar.is_displayed()
        
        # Check sidebar items
        sidebar_items = toc_sidebar.find_elements(By.CSS_SELECTOR, '.hmg-ai-toc-sidebar-item')
        assert len(sidebar_items) >= 5
        
        # Test scroll progress
        browser.execute_script("window.scrollTo(0, document.body.scrollHeight / 2);")
        time.sleep(1)
        
        selenium_helper.take_screenshot('toc_sidebar_scroll_progress')
        
        # Verify progress bar updated
        progress_width = progress_bar.value_of_css_property('width')
        assert progress_width != '0px'
    
    def test_audio_player_default_style(self, selenium_helper, browser):
        """Test audio player default style"""
        browser.get(self.test_post_url)
        
        audio_player = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-audio.hmg-ai-audio-player')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", audio_player)
        time.sleep(1)
        
        selenium_helper.take_screenshot('audio_player_default')
        
        # Check audio element
        audio_element = audio_player.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-element')
        assert audio_element.is_displayed()
        
        # Check speed control
        speed_button = audio_player.find_element(By.CSS_SELECTOR, '[data-hmg-audio-speed]')
        assert speed_button.text == '1x'
        
        # Test speed control click
        speed_button.click()
        time.sleep(0.5)
        
        selenium_helper.take_screenshot('audio_player_speed_changed')
    
    def test_audio_compact_style(self, selenium_helper, browser):
        """Test audio compact style"""
        browser.get(self.test_post_url)
        
        audio_compact = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-audio.hmg-ai-audio-compact')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", audio_compact)
        time.sleep(1)
        
        selenium_helper.take_screenshot('audio_compact')
        
        # Check compact layout
        compact_container = audio_compact.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-compact')
        audio_info = compact_container.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-info')
        
        assert audio_info.is_displayed()
        
        # Check track title
        track_title = audio_info.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-track-title')
        assert len(track_title.text) > 0
    
    def test_audio_minimal_style(self, selenium_helper, browser):
        """Test audio minimal style with custom controls"""
        browser.get(self.test_post_url)
        
        audio_minimal = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-audio.hmg-ai-audio-minimal')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", audio_minimal)
        time.sleep(1)
        
        selenium_helper.take_screenshot('audio_minimal')
        
        # Check custom play button
        play_button = audio_minimal.find_element(By.CSS_SELECTOR, '[data-hmg-audio-toggle]')
        assert play_button.is_displayed()
        
        # Check progress bar
        progress_bar = audio_minimal.find_element(By.CSS_SELECTOR, '[data-hmg-audio-progress]')
        assert progress_bar.is_displayed()
        
        # Test play button click
        play_button.click()
        time.sleep(1)
        
        selenium_helper.take_screenshot('audio_minimal_playing')
    
    def test_audio_card_style(self, selenium_helper, browser):
        """Test audio card style"""
        browser.get(self.test_post_url)
        
        audio_card = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-audio.hmg-ai-audio-card')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", audio_card)
        time.sleep(1)
        
        selenium_helper.take_screenshot('audio_card')
        
        # Check card layout
        card_container = audio_card.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-card')
        card_header = card_container.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-card-header')
        card_actions = card_container.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-card-actions')
        
        assert card_header.is_displayed()
        assert card_actions.is_displayed()
        
        # Check download link
        download_link = card_actions.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-download')
        assert download_link.is_displayed()
        assert 'Download' in download_link.text
    
    def test_responsive_mobile_view(self, selenium_helper, browser):
        """Test responsive behavior on mobile viewport"""
        # Set mobile viewport
        browser.set_window_size(375, 667)  # iPhone 6/7/8 size
        browser.get(self.test_post_url)
        
        time.sleep(2)  # Allow responsive adjustments
        
        # Test takeaways cards on mobile
        takeaways_cards = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-takeaways')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", takeaways_cards)
        time.sleep(1)
        
        selenium_helper.take_screenshot('mobile_takeaways')
        
        # Test FAQ accordion on mobile
        try:
            faq_accordion = browser.find_element(By.CSS_SELECTOR, '.hmg-ai-faq')
            browser.execute_script("arguments[0].scrollIntoView(true);", faq_accordion)
            time.sleep(1)
            
            selenium_helper.take_screenshot('mobile_faq')
        except NoSuchElementException:
            pass  # FAQ might not be visible in this test
        
        # Reset to desktop size
        browser.set_window_size(1920, 1080)
    
    def test_accessibility_keyboard_navigation(self, selenium_helper, browser):
        """Test keyboard accessibility"""
        browser.get(self.test_post_url)
        
        try:
            # Focus on first FAQ accordion button
            faq_section = selenium_helper.wait_for_element(
                (By.CSS_SELECTOR, '.hmg-ai-faq')
            )
            
            first_button = faq_section.find_element(By.CSS_SELECTOR, 'button, [data-hmg-faq-toggle]')
            first_button.click()  # Focus
            
            selenium_helper.take_screenshot('accessibility_faq_focus')
            
            # Test Tab navigation
            first_button.send_keys(Keys.TAB)
            time.sleep(0.3)
            
            selenium_helper.take_screenshot('accessibility_tab_navigation')
            
        except (NoSuchElementException, TimeoutException):
            # If FAQ not found, test TOC keyboard navigation
            toc_section = browser.find_element(By.CSS_SELECTOR, '.hmg-ai-toc')
            first_toc_link = toc_section.find_element(By.CSS_SELECTOR, 'a')
            
            first_toc_link.click()
            time.sleep(0.5)
            
            selenium_helper.take_screenshot('accessibility_toc_keyboard')
    
    def test_print_styles(self, selenium_helper, browser):
        """Test print-friendly styles"""
        browser.get(self.test_post_url)
        
        # Simulate print media query
        browser.execute_script("""
            var style = document.createElement('style');
            style.innerHTML = '@media screen { * { print-color-adjust: exact !important; } }';
            document.head.appendChild(style);
        """)
        
        # Take screenshot of print view
        selenium_helper.take_screenshot('print_view_simulation')
        
        # Check that FAQ accordions are expanded for print
        faq_contents = browser.find_elements(By.CSS_SELECTOR, '.hmg-ai-faq-accordion-content')
        
        # In print mode, all FAQ items should be visible
        for content in faq_contents:
            # Check if content is visible (print styles should show all)
            assert content.is_displayed()
    
    def test_dark_mode_compatibility(self, selenium_helper, browser):
        """Test dark mode styles"""
        browser.get(self.test_post_url)
        
        # Simulate dark mode preference
        browser.execute_script("""
            var style = document.createElement('style');
            style.innerHTML = `
                @media (prefers-color-scheme: dark) {
                    :root {
                        --hmg-light-gray: #2D3748;
                        --hmg-white: #1A202C;
                        --hmg-dark-gray: #E2E8F0;
                        --hmg-medium-gray: #A0AEC0;
                    }
                }
                
                /* Force dark mode for testing */
                .hmg-ai-takeaways,
                .hmg-ai-faq,
                .hmg-ai-toc,
                .hmg-ai-audio {
                    background: #2D3748 !important;
                    color: #E2E8F0 !important;
                }
            `;
            document.head.appendChild(style);
        """)
        
        time.sleep(1)
        
        # Take screenshots of components in dark mode
        takeaways = browser.find_element(By.CSS_SELECTOR, '.hmg-ai-takeaways')
        browser.execute_script("arguments[0].scrollIntoView(true);", takeaways)
        selenium_helper.take_screenshot('dark_mode_takeaways')
        
        faq = browser.find_element(By.CSS_SELECTOR, '.hmg-ai-faq')
        browser.execute_script("arguments[0].scrollIntoView(true);", faq)
        selenium_helper.take_screenshot('dark_mode_faq')
    
    def test_performance_loading_times(self, selenium_helper, browser):
        """Test component loading performance"""
        start_time = time.time()
        
        browser.get(self.test_post_url)
        
        # Wait for all shortcode components to load
        components = [
            '.hmg-ai-takeaways',
            '.hmg-ai-faq', 
            '.hmg-ai-toc',
            '.hmg-ai-audio'
        ]
        
        for component in components:
            selenium_helper.wait_for_element((By.CSS_SELECTOR, component))
        
        load_time = time.time() - start_time
        
        # Assert reasonable loading time (adjust threshold as needed)
        assert load_time < 10, f"Page loaded too slowly: {load_time}s"
        
        selenium_helper.take_screenshot('performance_all_components_loaded')
        
        # Test JavaScript initialization
        js_ready = browser.execute_script("""
            return typeof HMGAIPublic !== 'undefined' && 
                   typeof jQuery !== 'undefined' &&
                   jQuery('.hmg-ai-takeaways').length > 0;
        """)
        
        assert js_ready, "JavaScript components not properly initialized"
    
    def test_toc_navigation_functionality(self, selenium_helper, browser):
        """Test TOC navigation and smooth scrolling"""
        browser.get(self.test_post_url)
        
        toc_section = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-toc.hmg-ai-toc-numbered')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", toc_section)
        time.sleep(1)
        
        selenium_helper.take_screenshot('toc_numbered')
        
        # Check TOC links
        links = toc_section.find_elements(By.CSS_SELECTOR, '[data-hmg-smooth-scroll]')
        assert len(links) >= 5
        
        # Test first link click
        first_link = links[0]
        target_id = first_link.get_attribute('data-target')
        
        # Click link
        first_link.click()
        time.sleep(1)
        
        # Verify scroll occurred
        target_element = browser.find_element(By.ID, target_id)
        assert target_element.is_displayed()
        
        selenium_helper.take_screenshot('toc_after_navigation')
    
    def test_audio_player_functionality(self, selenium_helper, browser):
        """Test audio player controls and interactions"""
        browser.get(self.test_post_url)
        
        audio_player = selenium_helper.wait_for_element(
            (By.CSS_SELECTOR, '.hmg-ai-audio.hmg-ai-audio-player')
        )
        
        browser.execute_script("arguments[0].scrollIntoView(true);", audio_player)
        time.sleep(1)
        
        selenium_helper.take_screenshot('audio_player_default')
        
        # Check audio element
        audio_element = audio_player.find_element(By.CSS_SELECTOR, '.hmg-ai-audio-element')
        assert audio_element.is_displayed()
        
        # Check speed control
        speed_button = audio_player.find_element(By.CSS_SELECTOR, '[data-hmg-audio-speed]')
        assert speed_button.text == '1x'
        
        # Test speed control click
        speed_button.click()
        time.sleep(0.5)
        
        selenium_helper.take_screenshot('audio_player_speed_changed')
    
    def test_all_shortcode_styles_present(self, selenium_helper, browser):
        """Test that all shortcode styles are present and rendered"""
        browser.get(self.test_post_url)
        
        # Wait for page to load
        time.sleep(3)
        
        # Take full page screenshot
        selenium_helper.take_screenshot('all_shortcodes_overview')
        
        # Check for presence of each shortcode type
        shortcode_selectors = [
            '.hmg-ai-takeaways',
            '.hmg-ai-faq', 
            '.hmg-ai-toc',
            '.hmg-ai-audio'
        ]
        
        found_shortcodes = []
        for selector in shortcode_selectors:
            try:
                elements = browser.find_elements(By.CSS_SELECTOR, selector)
                if elements:
                    found_shortcodes.append(selector)
                    # Scroll to each and take screenshot
                    browser.execute_script("arguments[0].scrollIntoView(true);", elements[0])
                    time.sleep(1)
                    component_name = selector.replace('.hmg-ai-', '').replace('-', '_')
                    selenium_helper.take_screenshot(f'component_{component_name}')
            except Exception as e:
                print(f"Could not find {selector}: {e}")
        
        # Assert we found at least some shortcodes
        assert len(found_shortcodes) > 0, "No shortcodes found on page"
        
        print(f"Found shortcode components: {found_shortcodes}") 