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
