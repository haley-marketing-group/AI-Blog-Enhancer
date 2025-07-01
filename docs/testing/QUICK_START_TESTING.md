# Quick Start: Visual Testing Setup
## Get Your Testing Environment Running in 5 Minutes

---

## 🚀 One-Command Setup

Run this single command to set up everything:

```bash
./setup-testing.sh
```

This script will:
- ✅ Create complete project structure
- ✅ Set up Docker Compose with WordPress, MySQL, Selenium Grid
- ✅ Install Python dependencies for testing
- ✅ Configure pytest with visual testing
- ✅ Start all services
- ✅ Install and configure WordPress
- ✅ Run verification tests

---

## 📋 Prerequisites

Make sure you have these installed:

```bash
# Check Docker
docker --version

# Check Docker Compose
docker-compose --version

# Check Python
python3 --version

# Check pip
pip3 --version
```

If missing, install:
- **Docker Desktop**: https://www.docker.com/products/docker-desktop
- **Python 3.8+**: https://www.python.org/downloads/

---

## 🌐 Access Your Environment

After setup completes, you can access:

| Service | URL | Credentials |
|---------|-----|-------------|
| **WordPress Site** | http://localhost:8080 | - |
| **WordPress Admin** | http://localhost:8080/wp-admin | admin / admin123 |
| **phpMyAdmin** | http://localhost:8081 | root / rootpassword |
| **Selenium Grid** | http://localhost:4444 | - |

---

## 🧪 Running Tests

### Basic Test Commands

```bash
# Run all visual tests
pytest tests/visual/ -v

# Run specific test file
pytest tests/visual/test_setup_verification.py -v

# Run with HTML report
pytest tests/visual/ --html=tests/reports/report.html --self-contained-html

# Run tests in parallel
pytest tests/visual/ -n 2 -v
```

### Test Categories

```bash
# Visual regression tests only
pytest tests/visual/ -m visual -v

# Responsive design tests
pytest tests/visual/ -m responsive -v

# Theme compatibility tests
pytest tests/visual/ -m theme -v

# Skip slow tests
pytest tests/visual/ -m "not slow" -v
```

---

## 📸 Screenshot Management

### First Run (Create Baselines)
```bash
# First time running tests creates baseline screenshots
pytest tests/visual/ -v
```

### Update Baselines
```bash
# When you want to update baseline screenshots
rm -rf tests/screenshots/baseline/
pytest tests/visual/ -v
```

### View Test Results
```bash
# Screenshots are saved in:
tests/screenshots/
├── baseline/     # Reference screenshots
├── current/      # Latest test screenshots  
└── diff/         # Difference images

# Open HTML report
open tests/reports/visual_test_report.html
```

---

## 🔄 Environment Management

### Start/Stop Services
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# Restart specific service
docker-compose restart wordpress
```

### Reset Environment
```bash
# Complete reset (removes all data)
docker-compose down -v

# Run setup again
./setup-testing.sh
```

### Backup/Restore WordPress
```bash
# Backup WordPress data
docker-compose exec wordpress wp db export /tmp/backup.sql --allow-root
docker cp hmg-ai-wordpress:/tmp/backup.sql ./backup.sql

# Restore WordPress data
docker cp ./backup.sql hmg-ai-wordpress:/tmp/backup.sql
docker-compose exec wordpress wp db import /tmp/backup.sql --allow-root
```

---

## 🐛 Troubleshooting

### Common Issues

#### Docker Issues
```bash
# If containers won't start
docker-compose down
docker system prune -f
docker-compose up -d

# Check container status
docker-compose ps

# View container logs
docker-compose logs wordpress
docker-compose logs selenium-hub
```

#### WordPress Issues
```bash
# If WordPress won't load
docker-compose restart wordpress

# Reset WordPress installation
docker-compose exec wordpress wp core download --force --allow-root
docker-compose exec wordpress wp core install \
  --url=http://localhost:8080 \
  --title="HMG AI Test Site" \
  --admin_user=admin \
  --admin_password=admin123 \
  --admin_email=test@hmgtools.com \
  --allow-root
```

#### Python/Selenium Issues
```bash
# Reinstall Python dependencies
pip3 install -r tests/requirements.txt --force-reinstall

# Check Selenium Grid status
curl http://localhost:4444/status

# Restart Selenium services
docker-compose restart selenium-hub selenium-chrome selenium-firefox
```

#### Port Conflicts
```bash
# If ports 8080, 8081, or 4444 are in use, edit docker-compose.yml:
# Change "8080:80" to "8090:80" for WordPress
# Change "8081:80" to "8091:80" for phpMyAdmin  
# Change "4444:4444" to "4445:4444" for Selenium

# Then update .env file:
WORDPRESS_URL=http://localhost:8090
SELENIUM_HUB=http://localhost:4445/wd/hub
```

---

## 📝 Writing Your First Test

Create a new test file:

```python
# tests/visual/test_my_plugin.py
import pytest
from selenium.webdriver.common.by import By

class TestMyPlugin:
    
    def test_plugin_admin_page(self, selenium_helper, wordpress_helper):
        """Test plugin admin page appears correctly"""
        # Login to WordPress admin
        wordpress_helper.login_to_admin()
        
        # Navigate to plugin page
        plugin_url = f"{wordpress_helper.wp_url}/wp-admin/admin.php?page=my-plugin"
        selenium_helper.driver.get(plugin_url)
        
        # Wait for page to load
        selenium_helper.wait_for_element((By.CLASS_NAME, "my-plugin-container"))
        
        # Take screenshot
        screenshot_path = selenium_helper.take_screenshot("plugin_admin_page")
        
        # Compare with baseline (creates baseline on first run)
        baseline_path = screenshot_path.replace('current', 'baseline')
        is_similar, score, diff_path = selenium_helper.compare_screenshots(
            baseline_path, screenshot_path
        )
        
        assert is_similar, f"Plugin admin page visual regression detected. Similarity: {score:.2f}"
        print(f"✅ Plugin admin page test passed (similarity: {score:.3f})")

    def test_shortcode_display(self, selenium_helper, wordpress_helper):
        """Test shortcode displays correctly on frontend"""
        # Create a test post with shortcode
        post_url = wordpress_helper.create_test_post(
            "Shortcode Test",
            "<h2>Test Post</h2>[my_shortcode]<p>End of post</p>"
        )
        
        # Visit the post
        selenium_helper.driver.get(post_url)
        
        # Wait for shortcode content
        selenium_helper.wait_for_element((By.CLASS_NAME, "my-shortcode-output"))
        
        # Take responsive screenshots
        screenshots = selenium_helper.take_responsive_screenshots("shortcode_display", post_url)
        
        # Verify shortcode rendered
        shortcode_element = selenium_helper.driver.find_element(By.CLASS_NAME, "my-shortcode-output")
        assert shortcode_element.is_displayed(), "Shortcode should be visible"
        
        print("✅ Shortcode display test passed")
```

Run your test:
```bash
pytest tests/visual/test_my_plugin.py -v
```

---

## 🎯 Next Steps

1. **Start Development**: Your testing environment is ready!
2. **Create Plugin Structure**: Follow the PROJECT_STRUCTURE.md guide
3. **Write Tests First**: Use TDD approach with visual tests
4. **Continuous Testing**: Run tests after each change
5. **CI/CD Integration**: Add tests to your deployment pipeline

---

## 📞 Need Help?

- **View Logs**: `docker-compose logs -f`
- **Check Services**: `docker-compose ps`
- **Test Report**: Open `tests/reports/visual_test_report.html`
- **Screenshots**: Check `tests/screenshots/` directory

Your visual testing environment is now ready! You can see exactly what your users will see and catch visual regressions before they reach production. 