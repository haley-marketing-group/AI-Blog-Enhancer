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
