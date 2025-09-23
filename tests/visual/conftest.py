import pytest
import os
from selenium import webdriver
from selenium.webdriver.chrome.options import Options as ChromeOptions
from selenium.webdriver.firefox.options import Options as FirefoxOptions
import sys
sys.path.append(os.path.join(os.path.dirname(__file__), 'utils'))

from selenium_helper import SeleniumHelper
from wordpress_helper import WordPressHelper

@pytest.fixture(scope="session")
def test_config():
    """Test configuration"""
    # Use host.docker.internal for Selenium containers to access host machine
    wordpress_url = os.getenv('WORDPRESS_URL', 'http://host.docker.internal:8085')
    
    return {
        'wordpress_url': wordpress_url,
        'admin_user': os.getenv('WP_ADMIN_USER', 'admin'),
        'admin_pass': os.getenv('WP_ADMIN_PASS', 'admin123'),
        'selenium_hub': os.getenv('SELENIUM_HUB', 'http://localhost:4444/wd/hub'),
        'screenshot_dir': 'tests/screenshots',
        'baseline_dir': 'tests/screenshots/baseline',
        'current_dir': 'tests/screenshots/current',
        'diff_dir': 'tests/screenshots/diff'
    }

@pytest.fixture(params=['chrome'])  # Start with Chrome only
def browser(request, test_config):
    """Browser fixture"""
    browser_name = request.param
    
    if browser_name == 'chrome':
        options = ChromeOptions()
        options.add_argument('--headless')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        options.add_argument('--window-size=1920,1080')
        
        driver = webdriver.Remote(
            command_executor=test_config['selenium_hub'],
            options=options
        )
    
    driver.implicitly_wait(10)
    yield driver
    driver.quit()

@pytest.fixture
def selenium_helper(browser, test_config):
    """Selenium helper utilities"""
    return SeleniumHelper(browser, test_config)

@pytest.fixture
def wordpress_helper(browser, test_config):
    """WordPress helper utilities"""
    return WordPressHelper(browser, test_config)
