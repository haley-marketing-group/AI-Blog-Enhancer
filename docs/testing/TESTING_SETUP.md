# Testing Setup & Visual Testing Guide
## HMG AI Blog Enhancer WordPress Plugin

This guide provides a complete testing framework with Selenium visual testing and local WordPress development environment.

---

## 🎯 Testing Strategy Overview

This guide sets up a comprehensive testing environment including:
- **Local WordPress Development Environment**
- **Selenium Visual Testing Framework**
- **Automated Screenshot Comparison**
- **Cross-browser Testing**
- **Mobile Responsive Testing**
- **Theme Compatibility Testing**

---

## 🐳 Local WordPress Environment Setup

### Option 1: Docker Compose (Recommended)

#### `docker-compose.yml`
```yaml
version: '3.8'

services:
  wordpress:
    image: wordpress:latest
    container_name: hmg-ai-wordpress
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
      WORDPRESS_DEBUG: 1
      WORDPRESS_CONFIG_EXTRA: |
        define('WP_DEBUG', true);
        define('WP_DEBUG_LOG', true);
        define('WP_DEBUG_DISPLAY', false);
        define('SCRIPT_DEBUG', true);
    volumes:
      - wordpress_data:/var/www/html
      - ./hmg-ai-blog-enhancer:/var/www/html/wp-content/plugins/hmg-ai-blog-enhancer
      - ./test-content:/var/www/html/wp-content/uploads
    depends_on:
      - db
    networks:
      - wordpress-network

  db:
    image: mysql:8.0
    container_name: hmg-ai-mysql
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: rootpassword
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - wordpress-network

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: hmg-ai-phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      MYSQL_ROOT_PASSWORD: rootpassword
    depends_on:
      - db
    networks:
      - wordpress-network

  selenium-hub:
    image: selenium/hub:4.15.0
    container_name: hmg-ai-selenium-hub
    ports:
      - "4444:4444"
    environment:
      GRID_MAX_SESSION: 16
      GRID_BROWSER_TIMEOUT: 300
      GRID_TIMEOUT: 300
    networks:
      - wordpress-network

  selenium-chrome:
    image: selenium/node-chrome:4.15.0
    container_name: hmg-ai-chrome
    environment:
      HUB_HOST: selenium-hub
      HUB_PORT: 4444
      NODE_MAX_INSTANCES: 4
      NODE_MAX_SESSION: 4
    volumes:
      - /dev/shm:/dev/shm
    depends_on:
      - selenium-hub
    networks:
      - wordpress-network

  selenium-firefox:
    image: selenium/node-firefox:4.15.0
    container_name: hmg-ai-firefox
    environment:
      HUB_HOST: selenium-hub
      HUB_PORT: 4444
      NODE_MAX_INSTANCES: 4
      NODE_MAX_SESSION: 4
    volumes:
      - /dev/shm:/dev/shm
    depends_on:
      - selenium-hub
    networks:
      - wordpress-network

volumes:
  wordpress_data:
  db_data:

networks:
  wordpress-network:
    driver: bridge
```

#### Setup Commands
```bash
# Start the environment
docker-compose up -d

# Install WordPress (first time only)
docker-compose exec wordpress wp core install \
  --url=http://localhost:8080 \
  --title="HMG AI Test Site" \
  --admin_user=admin \
  --admin_password=admin123 \
  --admin_email=test@hmgtools.com \
  --allow-root

# Install WP-CLI in container
docker-compose exec wordpress wp --info --allow-root

# Stop the environment
docker-compose down

# Reset everything (careful!)
docker-compose down -v
```

### Option 2: Local by Flywheel / XAMPP / MAMP

#### Local by Flywheel Setup
```bash
# Download Local by Flywheel
# Create new site: "hmg-ai-test"
# PHP 8.1, MySQL 8.0, Nginx
# Domain: hmg-ai-test.local
```

---

## 🧪 Selenium Visual Testing Framework

### Project Structure
```
tests/
├── visual/
│   ├── __init__.py
│   ├── conftest.py                 # Pytest configuration
│   ├── test_admin_interface.py     # Admin UI tests
│   ├── test_public_display.py      # Public shortcode tests
│   ├── test_responsive.py          # Mobile responsive tests
│   ├── test_theme_compatibility.py # Theme compatibility tests
│   └── utils/
│       ├── __init__.py
│       ├── selenium_helper.py      # Selenium utilities
│       ├── screenshot_compare.py   # Visual comparison
│       └── wordpress_helper.py     # WordPress specific helpers
├── screenshots/
│   ├── baseline/                   # Reference screenshots
│   ├── current/                    # Current test screenshots
│   └── diff/                       # Difference images
├── test_data/
│   ├── sample_posts.json          # Test blog posts
│   ├── test_themes.json           # Themes to test
│   └── user_scenarios.json        # User interaction scenarios
└── requirements.txt               # Python dependencies
```

### Python Dependencies

#### `requirements.txt`
```txt
selenium==4.15.0
pytest==7.4.3
pytest-html==4.1.1
pytest-xdist==3.5.0
Pillow==10.1.0
opencv-python==4.8.1.78
webdriver-manager==4.0.1
requests==2.31.0
python-dotenv==1.0.0
allure-pytest==2.13.2
pytest-rerunfailures==12.0
beautifulsoup4==4.12.2
```

### Selenium Test Configuration

#### `tests/visual/conftest.py`
```python
import pytest
import os
from selenium import webdriver
from selenium.webdriver.chrome.options import Options as ChromeOptions
from selenium.webdriver.firefox.options import Options as FirefoxOptions
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from utils.selenium_helper import SeleniumHelper
from utils.wordpress_helper import WordPressHelper

@pytest.fixture(scope="session")
def test_config():
    """Test configuration"""
    return {
        'wordpress_url': os.getenv('WORDPRESS_URL', 'http://localhost:8080'),
        'admin_user': os.getenv('WP_ADMIN_USER', 'admin'),
        'admin_pass': os.getenv('WP_ADMIN_PASS', 'admin123'),
        'selenium_hub': os.getenv('SELENIUM_HUB', 'http://localhost:4444/wd/hub'),
        'screenshot_dir': 'tests/screenshots',
        'baseline_dir': 'tests/screenshots/baseline',
        'current_dir': 'tests/screenshots/current',
        'diff_dir': 'tests/screenshots/diff'
    }

@pytest.fixture(params=['chrome', 'firefox'])
def browser(request, test_config):
    """Browser fixture for cross-browser testing"""
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
    
    elif browser_name == 'firefox':
        options = FirefoxOptions()
        options.add_argument('--headless')
        options.add_argument('--width=1920')
        options.add_argument('--height=1080')
        
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
    """WordPress specific helper utilities"""
    return WordPressHelper(browser, test_config)

@pytest.fixture(scope="session", autouse=True)
def setup_test_environment(test_config):
    """Setup test environment before all tests"""
    # Create screenshot directories
    for dir_path in [test_config['baseline_dir'], test_config['current_dir'], test_config['diff_dir']]:
        os.makedirs(dir_path, exist_ok=True)
    
    # Setup test WordPress site
    wp_helper = WordPressHelper(None, test_config)
    wp_helper.setup_test_site()
    
    yield
    
    # Cleanup after all tests
    wp_helper.cleanup_test_site()
```

### Selenium Helper Utilities

#### `tests/visual/utils/selenium_helper.py`
```python
import os
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from PIL import Image, ImageDraw, ImageFont
import cv2
import numpy as np

class SeleniumHelper:
    def __init__(self, driver, config):
        self.driver = driver
        self.config = config
        self.wait = WebDriverWait(driver, 10)
    
    def take_screenshot(self, name, element=None, full_page=False):
        """Take screenshot with options for element or full page"""
        timestamp = int(time.time())
        filename = f"{name}_{timestamp}.png"
        filepath = os.path.join(self.config['current_dir'], filename)
        
        if full_page:
            # Full page screenshot
            self.driver.execute_script("window.scrollTo(0, 0);")
            total_height = self.driver.execute_script("return document.body.scrollHeight")
            self.driver.set_window_size(1920, total_height)
            time.sleep(1)
        
        if element:
            # Screenshot of specific element
            element.screenshot(filepath)
        else:
            # Full viewport screenshot
            self.driver.save_screenshot(filepath)
        
        return filepath
    
    def take_responsive_screenshots(self, name, url):
        """Take screenshots at different viewport sizes"""
        viewports = [
            ('desktop', 1920, 1080),
            ('tablet', 768, 1024),
            ('mobile', 375, 667),
            ('mobile-landscape', 667, 375)
        ]
        
        screenshots = {}
        
        for viewport_name, width, height in viewports:
            self.driver.set_window_size(width, height)
            self.driver.get(url)
            time.sleep(2)  # Wait for responsive adjustments
            
            screenshot_name = f"{name}_{viewport_name}"
            filepath = self.take_screenshot(screenshot_name)
            screenshots[viewport_name] = filepath
        
        return screenshots
    
    def wait_for_element(self, locator, timeout=10):
        """Wait for element to be present and visible"""
        try:
            element = WebDriverWait(self.driver, timeout).until(
                EC.presence_of_element_located(locator)
            )
            return element
        except TimeoutException:
            raise TimeoutException(f"Element {locator} not found within {timeout} seconds")
    
    def wait_for_ajax_complete(self, timeout=30):
        """Wait for AJAX requests to complete"""
        try:
            WebDriverWait(self.driver, timeout).until(
                lambda driver: driver.execute_script("return jQuery.active == 0")
            )
        except:
            # Fallback if jQuery is not available
            time.sleep(2)
    
    def scroll_to_element(self, element):
        """Scroll element into view"""
        self.driver.execute_script("arguments[0].scrollIntoView(true);", element)
        time.sleep(0.5)
    
    def hover_element(self, element):
        """Hover over an element"""
        ActionChains(self.driver).move_to_element(element).perform()
        time.sleep(0.5)
    
    def compare_screenshots(self, baseline_path, current_path, threshold=0.1):
        """Compare two screenshots and return similarity score"""
        if not os.path.exists(baseline_path):
            # First time running, save current as baseline
            os.makedirs(os.path.dirname(baseline_path), exist_ok=True)
            os.rename(current_path, baseline_path)
            return True, 1.0, "Baseline created"
        
        # Load images
        baseline = cv2.imread(baseline_path)
        current = cv2.imread(current_path)
        
        # Resize images to same size if different
        if baseline.shape != current.shape:
            current = cv2.resize(current, (baseline.shape[1], baseline.shape[0]))
        
        # Calculate structural similarity
        gray_baseline = cv2.cvtColor(baseline, cv2.COLOR_BGR2GRAY)
        gray_current = cv2.cvtColor(current, cv2.COLOR_BGR2GRAY)
        
        # Compute SSIM
        from skimage.metrics import structural_similarity as ssim
        similarity_score = ssim(gray_baseline, gray_current)
        
        # Create diff image
        diff = cv2.absdiff(baseline, current)
        diff_path = current_path.replace('current', 'diff')
        cv2.imwrite(diff_path, diff)
        
        is_similar = similarity_score >= (1 - threshold)
        
        return is_similar, similarity_score, diff_path
    
    def generate_test_report_screenshot(self, test_name, status, error_msg=None):
        """Generate annotated screenshot for test reports"""
        screenshot_path = self.take_screenshot(f"report_{test_name}")
        
        # Add annotations
        img = Image.open(screenshot_path)
        draw = ImageDraw.Draw(img)
        
        # Add status indicator
        color = "green" if status == "PASS" else "red"
        draw.rectangle([10, 10, 200, 60], fill=color)
        draw.text((20, 25), f"TEST {status}", fill="white")
        
        if error_msg:
            draw.text((20, 70), f"Error: {error_msg[:50]}", fill="red")
        
        annotated_path = screenshot_path.replace('.png', '_annotated.png')
        img.save(annotated_path)
        
        return annotated_path
```

### WordPress Helper Utilities

#### `tests/visual/utils/wordpress_helper.py`
```python
import requests
import json
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

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
        
        # Fill login form
        username_field = self.driver.find_element(By.ID, "user_login")
        password_field = self.driver.find_element(By.ID, "user_pass")
        
        username_field.send_keys(self.admin_user)
        password_field.send_keys(self.admin_pass)
        
        # Submit form
        login_button = self.driver.find_element(By.ID, "wp-submit")
        login_button.click()
        
        # Wait for dashboard
        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.ID, "wpadminbar"))
        )
    
    def activate_plugin(self, plugin_slug):
        """Activate plugin via admin interface"""
        self.login_to_admin()
        
        plugins_url = f"{self.wp_url}/wp-admin/plugins.php"
        self.driver.get(plugins_url)
        
        # Find and activate plugin
        try:
            activate_link = self.driver.find_element(
                By.XPATH, f"//tr[@data-slug='{plugin_slug}']//a[contains(@href, 'action=activate')]"
            )
            activate_link.click()
            
            # Wait for activation
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.CLASS_NAME, "updated"))
            )
            return True
        except:
            return False
    
    def create_test_post(self, title, content, post_type="post"):
        """Create a test post with AI-generated content"""
        self.login_to_admin()
        
        # Go to new post page
        new_post_url = f"{self.wp_url}/wp-admin/post-new.php?post_type={post_type}"
        self.driver.get(new_post_url)
        
        # Wait for editor to load
        WebDriverWait(self.driver, 15).until(
            EC.presence_of_element_located((By.ID, "title"))
        )
        
        # Fill in post details
        title_field = self.driver.find_element(By.ID, "title")
        title_field.send_keys(title)
        
        # Switch to text editor and add content
        text_tab = self.driver.find_element(By.ID, "content-html")
        text_tab.click()
        
        content_area = self.driver.find_element(By.ID, "content")
        content_area.send_keys(content)
        
        # Publish post
        publish_button = self.driver.find_element(By.ID, "publish")
        publish_button.click()
        
        # Wait for publish confirmation
        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "updated"))
        )
        
        # Get post URL
        view_post_link = self.driver.find_element(By.LINK_TEXT, "View post")
        post_url = view_post_link.get_attribute("href")
        
        return post_url
    
    def generate_ai_content_for_post(self, post_id):
        """Generate AI content for a specific post"""
        edit_url = f"{self.wp_url}/wp-admin/post.php?post={post_id}&action=edit"
        self.driver.get(edit_url)
        
        # Wait for meta box to load
        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.ID, "hmg-ai-content-generator"))
        )
        
        # Generate key takeaways
        takeaways_button = self.driver.find_element(By.ID, "generate-takeaways")
        takeaways_button.click()
        
        # Wait for generation to complete
        WebDriverWait(self.driver, 30).until(
            EC.presence_of_element_located((By.CLASS_NAME, "takeaways-generated"))
        )
        
        # Generate FAQ
        faq_button = self.driver.find_element(By.ID, "generate-faq")
        faq_button.click()
        
        # Wait for FAQ generation
        WebDriverWait(self.driver, 30).until(
            EC.presence_of_element_located((By.CLASS_NAME, "faq-generated"))
        )
        
        # Save post
        update_button = self.driver.find_element(By.ID, "publish")
        update_button.click()
        
        return True
    
    def install_test_themes(self):
        """Install and activate test themes"""
        test_themes = [
            'twentytwentythree',
            'twentytwentytwo',
            'astra',
            'generatepress'
        ]
        
        for theme in test_themes:
            self.install_theme(theme)
    
    def install_theme(self, theme_slug):
        """Install a specific theme"""
        self.login_to_admin()
        
        themes_url = f"{self.wp_url}/wp-admin/themes.php"
        self.driver.get(themes_url)
        
        # Click Add New
        add_new_button = self.driver.find_element(By.CLASS_NAME, "page-title-action")
        add_new_button.click()
        
        # Search for theme
        search_box = self.driver.find_element(By.ID, "wp-filter-search-input")
        search_box.send_keys(theme_slug)
        
        time.sleep(2)  # Wait for search results
        
        # Install theme
        try:
            install_button = self.driver.find_element(
                By.XPATH, f"//div[@data-slug='{theme_slug}']//a[contains(@class, 'install')]"
            )
            install_button.click()
            
            # Wait for installation
            WebDriverWait(self.driver, 30).until(
                EC.presence_of_element_located((By.CLASS_NAME, "activate"))
            )
            return True
        except:
            return False
    
    def switch_theme(self, theme_slug):
        """Switch to a specific theme"""
        self.login_to_admin()
        
        themes_url = f"{self.wp_url}/wp-admin/themes.php"
        self.driver.get(themes_url)
        
        try:
            theme_element = self.driver.find_element(
                By.XPATH, f"//div[@data-slug='{theme_slug}']"
            )
            
            # Click on theme
            theme_element.click()
            
            # Activate theme
            activate_button = self.driver.find_element(By.CLASS_NAME, "activate")
            activate_button.click()
            
            return True
        except:
            return False
    
    def setup_test_site(self):
        """Setup complete test site with sample data"""
        # Install and activate plugin
        self.activate_plugin('hmg-ai-blog-enhancer')
        
        # Create sample posts
        sample_posts = [
            {
                'title': 'The Future of AI in Content Creation',
                'content': '''
                <h2>Introduction to AI Content Creation</h2>
                <p>Artificial Intelligence is revolutionizing how we create and consume content...</p>
                
                <h2>Key Benefits of AI-Powered Tools</h2>
                <p>AI tools offer numerous advantages for content creators...</p>
                
                <h3>Improved Efficiency</h3>
                <p>Content creation becomes faster and more streamlined...</p>
                
                <h3>Enhanced Quality</h3>
                <p>AI helps maintain consistency and quality across content...</p>
                
                <h2>Challenges and Considerations</h2>
                <p>While AI offers many benefits, there are important considerations...</p>
                
                <h2>Future Outlook</h2>
                <p>The future of AI in content creation looks promising...</p>
                '''
            },
            {
                'title': 'WordPress Plugin Development Best Practices',
                'content': '''
                <h2>Getting Started with Plugin Development</h2>
                <p>WordPress plugin development requires understanding core concepts...</p>
                
                <h2>Plugin Architecture</h2>
                <p>A well-structured plugin follows WordPress coding standards...</p>
                
                <h3>File Organization</h3>
                <p>Organize your plugin files in a logical structure...</p>
                
                <h3>Security Considerations</h3>
                <p>Security should be a top priority in plugin development...</p>
                
                <h2>Testing and Quality Assurance</h2>
                <p>Thorough testing ensures your plugin works reliably...</p>
                '''
            }
        ]
        
        post_urls = []
        for post_data in sample_posts:
            post_url = self.create_test_post(post_data['title'], post_data['content'])
            post_urls.append(post_url)
        
        return post_urls
    
    def cleanup_test_site(self):
        """Clean up test site data"""
        # This would implement cleanup logic
        pass
```

### Visual Test Cases

#### `tests/visual/test_admin_interface.py`
```python
import pytest
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestAdminInterface:
    
    def test_plugin_settings_page(self, selenium_helper, wordpress_helper):
        """Test plugin settings page appearance"""
        wordpress_helper.login_to_admin()
        
        # Navigate to plugin settings
        settings_url = f"{wordpress_helper.wp_url}/wp-admin/admin.php?page=hmg-ai-settings"
        selenium_helper.driver.get(settings_url)
        
        # Wait for page to load
        selenium_helper.wait_for_element((By.CLASS_NAME, "hmg-ai-settings"))
        
        # Take screenshot
        screenshot_path = selenium_helper.take_screenshot("admin_settings_page")
        
        # Compare with baseline
        baseline_path = screenshot_path.replace('current', 'baseline')
        is_similar, score, diff_path = selenium_helper.compare_screenshots(
            baseline_path, screenshot_path
        )
        
        assert is_similar, f"Settings page visual regression detected. Similarity: {score:.2f}"
    
    def test_meta_box_appearance(self, selenium_helper, wordpress_helper):
        """Test AI content generator meta box"""
        # Create test post
        post_url = wordpress_helper.create_test_post(
            "Test Post for Meta Box",
            "<h2>Sample Content</h2><p>This is a test post for meta box testing.</p>"
        )
        
        # Get post ID from URL
        post_id = post_url.split('p=')[1] if 'p=' in post_url else post_url.split('/')[-2]
        
        # Navigate to post edit page
        edit_url = f"{wordpress_helper.wp_url}/wp-admin/post.php?post={post_id}&action=edit"
        selenium_helper.driver.get(edit_url)
        
        # Wait for meta box
        meta_box = selenium_helper.wait_for_element((By.ID, "hmg-ai-content-generator"))
        
        # Take screenshot of meta box
        screenshot_path = selenium_helper.take_screenshot("meta_box", element=meta_box)
        
        # Compare with baseline
        baseline_path = screenshot_path.replace('current', 'baseline')
        is_similar, score, diff_path = selenium_helper.compare_screenshots(
            baseline_path, screenshot_path
        )
        
        assert is_similar, f"Meta box visual regression detected. Similarity: {score:.2f}"
    
    def test_content_generation_progress(self, selenium_helper, wordpress_helper):
        """Test content generation progress indicators"""
        # Setup test post
        post_url = wordpress_helper.create_test_post(
            "AI Generation Test Post",
            "<h2>AI Content Test</h2><p>This post will test AI content generation.</p>"
        )
        
        post_id = post_url.split('p=')[1] if 'p=' in post_url else post_url.split('/')[-2]
        edit_url = f"{wordpress_helper.wp_url}/wp-admin/post.php?post={post_id}&action=edit"
        selenium_helper.driver.get(edit_url)
        
        # Wait for meta box
        selenium_helper.wait_for_element((By.ID, "hmg-ai-content-generator"))
        
        # Click generate takeaways button
        generate_button = selenium_helper.driver.find_element(By.ID, "generate-takeaways")
        generate_button.click()
        
        # Wait for progress indicator
        selenium_helper.wait_for_element((By.CLASS_NAME, "generation-progress"))
        
        # Take screenshot during generation
        screenshot_path = selenium_helper.take_screenshot("generation_progress")
        
        # Verify progress indicator is visible
        progress_element = selenium_helper.driver.find_element(By.CLASS_NAME, "generation-progress")
        assert progress_element.is_displayed(), "Progress indicator should be visible during generation"
```

#### `tests/visual/test_public_display.py`
```python
import pytest
from selenium.webdriver.common.by import By

class TestPublicDisplay:
    
    def test_key_takeaways_display(self, selenium_helper, wordpress_helper):
        """Test key takeaways public display"""
        # Create post with AI content
        post_url = wordpress_helper.create_test_post(
            "Takeaways Display Test",
            '''
            <h2>Introduction</h2>
            <p>This is a test post for takeaways display.</p>
            
            [hmg_key_takeaways style="cards"]
            
            <h2>Conclusion</h2>
            <p>End of test post.</p>
            '''
        )
        
        # Generate AI content for the post
        post_id = post_url.split('p=')[1] if 'p=' in post_url else post_url.split('/')[-2]
        wordpress_helper.generate_ai_content_for_post(post_id)
        
        # Visit public post
        selenium_helper.driver.get(post_url)
        
        # Wait for takeaways to load
        takeaways_element = selenium_helper.wait_for_element((By.CLASS_NAME, "hmg-ai-takeaways"))
        
        # Take responsive screenshots
        screenshots = selenium_helper.take_responsive_screenshots("takeaways_display", post_url)
        
        # Verify takeaways are displayed
        assert takeaways_element.is_displayed(), "Takeaways should be visible on public post"
        
        # Compare screenshots across viewports
        for viewport, screenshot_path in screenshots.items():
            baseline_path = screenshot_path.replace('current', 'baseline')
            is_similar, score, diff_path = selenium_helper.compare_screenshots(
                baseline_path, screenshot_path
            )
            
            assert is_similar, f"Takeaways visual regression on {viewport}. Similarity: {score:.2f}"
    
    def test_faq_accordion_functionality(self, selenium_helper, wordpress_helper):
        """Test FAQ accordion interaction"""
        # Create post with FAQ
        post_url = wordpress_helper.create_test_post(
            "FAQ Test Post",
            '''
            <h2>Frequently Asked Questions</h2>
            [hmg_faq style="accordion"]
            '''
        )
        
        post_id = post_url.split('p=')[1] if 'p=' in post_url else post_url.split('/')[-2]
        wordpress_helper.generate_ai_content_for_post(post_id)
        
        # Visit public post
        selenium_helper.driver.get(post_url)
        
        # Wait for FAQ to load
        faq_element = selenium_helper.wait_for_element((By.CLASS_NAME, "hmg-ai-faq"))
        
        # Take screenshot of closed FAQ
        selenium_helper.take_screenshot("faq_closed")
        
        # Click first FAQ question
        first_question = selenium_helper.driver.find_element(
            By.CSS_SELECTOR, ".hmg-ai-faq__question:first-child"
        )
        first_question.click()
        
        # Wait for animation
        selenium_helper.wait_for_element((By.CSS_SELECTOR, ".hmg-ai-faq__answer[style*='block']"))
        
        # Take screenshot of opened FAQ
        screenshot_path = selenium_helper.take_screenshot("faq_opened")
        
        # Verify FAQ opened
        first_answer = selenium_helper.driver.find_element(
            By.CSS_SELECTOR, ".hmg-ai-faq__answer:first-child"
        )
        assert first_answer.is_displayed(), "FAQ answer should be visible when opened"
```

#### `tests/visual/test_theme_compatibility.py`
```python
import pytest

class TestThemeCompatibility:
    
    @pytest.mark.parametrize("theme", [
        'twentytwentythree',
        'twentytwentytwo',
        'astra',
        'generatepress'
    ])
    def test_theme_compatibility(self, selenium_helper, wordpress_helper, theme):
        """Test plugin appearance across different themes"""
        # Switch to test theme
        wordpress_helper.switch_theme(theme)
        
        # Create test post
        post_url = wordpress_helper.create_test_post(
            f"Theme Test - {theme}",
            '''
            <h2>Theme Compatibility Test</h2>
            <p>Testing plugin appearance with different themes.</p>
            
            [hmg_key_takeaways style="cards"]
            [hmg_faq style="accordion"]
            [hmg_toc]
            '''
        )
        
        post_id = post_url.split('p=')[1] if 'p=' in post_url else post_url.split('/')[-2]
        wordpress_helper.generate_ai_content_for_post(post_id)
        
        # Take screenshots
        screenshots = selenium_helper.take_responsive_screenshots(f"theme_{theme}", post_url)
        
        # Verify no layout issues
        for viewport, screenshot_path in screenshots.items():
            baseline_path = screenshot_path.replace('current', 'baseline')
            is_similar, score, diff_path = selenium_helper.compare_screenshots(
                baseline_path, screenshot_path, threshold=0.15  # More lenient for theme differences
            )
            
            # Log theme compatibility results
            print(f"Theme {theme} on {viewport}: Similarity {score:.2f}")
```

### Running the Tests

#### `pytest.ini`
```ini
[tool:pytest]
testpaths = tests/visual
python_files = test_*.py
python_classes = Test*
python_functions = test_*
addopts = 
    --html=tests/reports/visual_test_report.html
    --self-contained-html
    --tb=short
    -v
markers =
    slow: marks tests as slow (deselect with '-m "not slow"')
    integration: marks tests as integration tests
    visual: marks tests as visual regression tests
```

#### Environment Configuration

#### `.env`
```bash
# WordPress Configuration
WORDPRESS_URL=http://localhost:8080
WP_ADMIN_USER=admin
WP_ADMIN_PASS=admin123

# Selenium Configuration
SELENIUM_HUB=http://localhost:4444/wd/hub

# Test Configuration
VISUAL_THRESHOLD=0.1
SCREENSHOT_DIR=tests/screenshots
```

### Running Commands

```bash
# Start test environment
docker-compose up -d

# Install Python dependencies
pip install -r tests/requirements.txt

# Run all visual tests
pytest tests/visual/ -v

# Run specific test categories
pytest tests/visual/ -m "not slow" -v
pytest tests/visual/test_admin_interface.py -v

# Run tests with specific browser
pytest tests/visual/ -v --browser=chrome

# Generate test report
pytest tests/visual/ --html=reports/visual_test_report.html --self-contained-html

# Run tests in parallel
pytest tests/visual/ -n 4 -v

# Update baseline screenshots (first run)
pytest tests/visual/ --update-baseline

# Run with verbose screenshot comparison
pytest tests/visual/ --screenshot-mode=all -v
```

This comprehensive testing setup gives you:

1. **Complete local WordPress environment** with Docker
2. **Selenium Grid** for cross-browser testing  
3. **Visual regression testing** with screenshot comparison
4. **Responsive testing** across multiple viewports
5. **Theme compatibility testing** 
6. **Automated test reporting** with screenshots
7. **WordPress-specific helpers** for plugin testing

The framework is designed to catch visual regressions early and ensure your plugin works consistently across different environments, browsers, and themes. 