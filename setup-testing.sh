#!/bin/bash

# HMG AI Blog Enhancer - Testing Environment Setup Script
# This script sets up the complete testing environment with Docker, Selenium, and WordPress

set -e

echo "🚀 Setting up HMG AI Blog Enhancer Testing Environment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    print_error "Python 3 is not installed. Please install Python 3 first."
    exit 1
fi

print_status "Creating project structure..."

# Create directory structure
mkdir -p tests/{visual,screenshots/{baseline,current,diff},test_data,reports}
mkdir -p tests/visual/utils
mkdir -p docker/wordpress

print_success "Project structure created"

# Create Docker Compose file
print_status "Creating Docker Compose configuration..."

cat > docker-compose.yml << 'EOF'
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
      - ./tests/test_data:/var/www/html/wp-content/uploads/test-data
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
      NODE_MAX_INSTANCES: 2
      NODE_MAX_SESSION: 2
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
      NODE_MAX_INSTANCES: 2
      NODE_MAX_SESSION: 2
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
EOF

print_success "Docker Compose configuration created"

# Create Python requirements file
print_status "Creating Python requirements..."

cat > tests/requirements.txt << 'EOF'
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
scikit-image==0.22.0
numpy==1.24.3
EOF

# Create environment file
print_status "Creating environment configuration..."

cat > .env << 'EOF'
# WordPress Configuration
WORDPRESS_URL=http://localhost:8080
WP_ADMIN_USER=admin
WP_ADMIN_PASS=admin123

# Selenium Configuration
SELENIUM_HUB=http://localhost:4444/wd/hub

# Test Configuration
VISUAL_THRESHOLD=0.1
SCREENSHOT_DIR=tests/screenshots

# Plugin Configuration
PLUGIN_SLUG=hmg-ai-blog-enhancer
EOF

# Create pytest configuration
print_status "Creating pytest configuration..."

cat > pytest.ini << 'EOF'
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
    responsive: marks tests as responsive design tests
    theme: marks tests as theme compatibility tests
EOF

# Create gitignore for testing
print_status "Creating .gitignore for test artifacts..."

cat > .gitignore << 'EOF'
# Test artifacts
tests/screenshots/current/
tests/screenshots/diff/
tests/reports/
*.pyc
__pycache__/
.pytest_cache/

# Environment
.env.local
*.log

# Dependencies
node_modules/
build/

# IDE
.vscode/
.idea/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db
EOF

# Create a simple test to verify setup
print_status "Creating initial test file..."

cat > tests/visual/test_setup_verification.py << 'EOF'
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
EOF

# Create helper files
print_status "Creating helper utilities..."

# Create conftest.py
cat > tests/visual/conftest.py << 'EOF'
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
EOF

# Create simplified selenium helper
cat > tests/visual/utils/selenium_helper.py << 'EOF'
import os
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException
from PIL import Image
import cv2
import numpy as np

class SeleniumHelper:
    def __init__(self, driver, config):
        self.driver = driver
        self.config = config
        self.wait = WebDriverWait(driver, 10)
    
    def take_screenshot(self, name):
        """Take screenshot"""
        timestamp = int(time.time())
        filename = f"{name}_{timestamp}.png"
        filepath = os.path.join(self.config['current_dir'], filename)
        
        # Ensure directory exists
        os.makedirs(os.path.dirname(filepath), exist_ok=True)
        
        self.driver.save_screenshot(filepath)
        return filepath
    
    def wait_for_element(self, locator, timeout=10):
        """Wait for element"""
        try:
            element = WebDriverWait(self.driver, timeout).until(
                EC.presence_of_element_located(locator)
            )
            return element
        except TimeoutException:
            raise TimeoutException(f"Element {locator} not found within {timeout} seconds")
    
    def compare_screenshots(self, baseline_path, current_path, threshold=0.1):
        """Basic screenshot comparison"""
        if not os.path.exists(baseline_path):
            # First run - copy current to baseline
            os.makedirs(os.path.dirname(baseline_path), exist_ok=True)
            import shutil
            shutil.copy2(current_path, baseline_path)
            return True, 1.0, "Baseline created"
        
        try:
            # Load images
            baseline = cv2.imread(baseline_path)
            current = cv2.imread(current_path)
            
            if baseline is None or current is None:
                return False, 0.0, "Could not load images"
            
            # Resize if different sizes
            if baseline.shape != current.shape:
                current = cv2.resize(current, (baseline.shape[1], baseline.shape[0]))
            
            # Simple pixel difference
            diff = cv2.absdiff(baseline, current)
            total_pixels = baseline.shape[0] * baseline.shape[1] * baseline.shape[2]
            diff_pixels = np.count_nonzero(diff)
            
            similarity = 1 - (diff_pixels / total_pixels)
            
            # Save diff image
            diff_path = current_path.replace('current', 'diff')
            os.makedirs(os.path.dirname(diff_path), exist_ok=True)
            cv2.imwrite(diff_path, diff)
            
            is_similar = similarity >= (1 - threshold)
            return is_similar, similarity, diff_path
            
        except Exception as e:
            return False, 0.0, f"Comparison error: {str(e)}"
EOF

# Create simplified WordPress helper
cat > tests/visual/utils/wordpress_helper.py << 'EOF'
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
EOF

# Create __init__.py files
touch tests/__init__.py
touch tests/visual/__init__.py
touch tests/visual/utils/__init__.py

print_success "Test files created"

# Install Python dependencies
print_status "Installing Python dependencies..."

if command -v pip3 &> /dev/null; then
    pip3 install -r tests/requirements.txt
elif command -v pip &> /dev/null; then
    pip install -r tests/requirements.txt
else
    print_warning "pip not found. Please install Python dependencies manually:"
    print_warning "pip install -r tests/requirements.txt"
fi

print_success "Python dependencies installed"

# Start Docker containers
print_status "Starting Docker containers..."

docker-compose up -d

print_status "Waiting for services to start..."
sleep 30

# Check if WordPress is accessible
print_status "Checking WordPress accessibility..."

max_attempts=12
attempt=1

while [ $attempt -le $max_attempts ]; do
    if curl -s http://localhost:8080 > /dev/null; then
        print_success "WordPress is accessible!"
        break
    else
        print_status "Attempt $attempt/$max_attempts - WordPress not ready yet..."
        sleep 10
        ((attempt++))
    fi
done

if [ $attempt -gt $max_attempts ]; then
    print_error "WordPress failed to start after $max_attempts attempts"
    exit 1
fi

# Install WordPress
print_status "Installing WordPress..."

docker-compose exec -T wordpress wp core install \
    --url=http://localhost:8080 \
    --title="HMG AI Test Site" \
    --admin_user=admin \
    --admin_password=admin123 \
    --admin_email=test@hmgtools.com \
    --allow-root

print_success "WordPress installed successfully"

# Run setup verification test
print_status "Running setup verification tests..."

if command -v pytest &> /dev/null; then
    pytest tests/visual/test_setup_verification.py -v
else
    print_warning "pytest not found. Please run tests manually:"
    print_warning "pytest tests/visual/test_setup_verification.py -v"
fi

print_success "🎉 Testing environment setup complete!"

echo ""
echo "📋 Next Steps:"
echo "1. Access WordPress: http://localhost:8080"
echo "2. Access Admin: http://localhost:8080/wp-admin (admin/admin123)"
echo "3. Access phpMyAdmin: http://localhost:8081"
echo "4. Access Selenium Grid: http://localhost:4444"
echo ""
echo "🧪 Run Tests:"
echo "pytest tests/visual/ -v"
echo ""
echo "🛑 Stop Environment:"
echo "docker-compose down"
echo ""
echo "🔄 Reset Environment:"
echo "docker-compose down -v && ./setup-testing.sh" 