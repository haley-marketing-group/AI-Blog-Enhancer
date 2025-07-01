import pytest
import os
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestSetupVerification:
    """Basic tests to verify the testing environment is working"""
    
    def test_wordpress_is_accessible(self, selenium_helper, wordpress_helper):
        """Verify WordPress site is accessible"""
        selenium_helper.driver.get(wordpress_helper.wp_url)
        
        # Wait for WordPress to load
        WebDriverWait(selenium_helper.driver, 10).until(
            lambda driver: "WordPress" in driver.title or "Just another WordPress site" in driver.page_source
        )
        
        # Take a screenshot to verify
        screenshot_path = selenium_helper.take_screenshot("wordpress_homepage")
        
        assert os.path.exists(screenshot_path), "Screenshot should be created"
        print(f"✅ WordPress accessible at {wordpress_helper.wp_url}")
    
    def test_admin_login_works(self, selenium_helper, wordpress_helper):
        """Verify admin login functionality"""
        wordpress_helper.login_to_admin()
        
        # Verify we're in admin
        assert "wp-admin" in selenium_helper.driver.current_url
        
        # Check for admin bar
        admin_bar = selenium_helper.wait_for_element((By.ID, "wpadminbar"))
        assert admin_bar.is_displayed()
        
        # Take screenshot of admin dashboard
        screenshot_path = selenium_helper.take_screenshot("admin_dashboard")
        
        print("✅ Admin login successful")
    
    def test_selenium_grid_connectivity(self, selenium_helper):
        """Verify Selenium Grid is working"""
        # Simple navigation test
        selenium_helper.driver.get("https://www.google.com")
        
        # Wait for page to load
        WebDriverWait(selenium_helper.driver, 10).until(
            EC.presence_of_element_located((By.NAME, "q"))
        )
        
        print("✅ Selenium Grid connectivity verified")
    
    def test_screenshot_comparison_works(self, selenium_helper):
        """Test screenshot comparison functionality"""
        # Take two screenshots of the same page
        selenium_helper.driver.get("https://www.example.com")
        
        screenshot1 = selenium_helper.take_screenshot("comparison_test_1")
        screenshot2 = selenium_helper.take_screenshot("comparison_test_2")
        
        # Compare screenshots (should be identical)
        is_similar, score, diff_path = selenium_helper.compare_screenshots(
            screenshot1, screenshot2
        )
        
        assert score > 0.95, f"Screenshots should be nearly identical, got {score}"
        print(f"✅ Screenshot comparison working (similarity: {score:.3f})")
