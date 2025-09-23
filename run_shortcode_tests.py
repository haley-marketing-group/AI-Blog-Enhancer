#!/usr/bin/env python3
"""
HMG AI Blog Enhancer - Shortcode Visual Testing Runner

This script runs comprehensive Selenium-based visual tests for all shortcode functionality.
"""

import os
import sys
import subprocess
import time
import argparse
from pathlib import Path

def check_selenium_grid():
    """Check if Selenium Grid is running"""
    try:
        import requests
        response = requests.get('http://localhost:4444/status', timeout=5)
        if response.status_code == 200:
            print("✅ Selenium Grid is running")
            return True
    except:
        pass
    
    print("❌ Selenium Grid is not running")
    print("Please start Selenium Grid with: docker-compose up selenium-hub chrome")
    return False

def check_wordpress():
    """Check if WordPress is accessible"""
    try:
        import requests
        response = requests.get('http://localhost:8085', timeout=10)
        if response.status_code == 200:
            print("✅ WordPress is accessible")
            return True
    except:
        pass
    
    print("❌ WordPress is not accessible at http://localhost:8085")
    print("Please start WordPress with: docker-compose up wordpress")
    return False

def setup_test_environment():
    """Set up test environment"""
    print("🔧 Setting up test environment...")
    
    # Create screenshot directories
    screenshot_dirs = [
        'tests/screenshots/baseline',
        'tests/screenshots/current', 
        'tests/screenshots/diff'
    ]
    
    for dir_path in screenshot_dirs:
        Path(dir_path).mkdir(parents=True, exist_ok=True)
        print(f"✅ Created directory: {dir_path}")
    
    # Set environment variables
    os.environ.setdefault('WORDPRESS_URL', 'http://localhost:8085')
    os.environ.setdefault('WP_ADMIN_USER', 'admin')
    os.environ.setdefault('WP_ADMIN_PASS', 'admin123')
    os.environ.setdefault('SELENIUM_HUB', 'http://localhost:4444/wd/hub')
    
    print("✅ Environment variables set")

def run_visual_tests(test_filter=None, verbose=False):
    """Run visual tests"""
    print("🧪 Running shortcode visual tests...")
    
    # Build pytest command
    cmd = [
        'python', '-m', 'pytest',
        'tests/visual/test_shortcode_visual.py',
        '-v' if verbose else '-q',
        '--tb=short',
        '--capture=no' if verbose else '--capture=sys'
    ]
    
    if test_filter:
        cmd.extend(['-k', test_filter])
    
    # Add HTML report
    cmd.extend(['--html=tests/reports/shortcode_visual_report.html', '--self-contained-html'])
    
    print(f"Running: {' '.join(cmd)}")
    
    try:
        result = subprocess.run(cmd, cwd=os.getcwd(), check=False)
        return result.returncode == 0
    except Exception as e:
        print(f"❌ Error running tests: {e}")
        return False

def generate_visual_report():
    """Generate visual comparison report"""
    print("📊 Generating visual comparison report...")
    
    report_html = """
    <!DOCTYPE html>
    <html>
    <head>
        <title>HMG AI Shortcode Visual Test Report</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; }
            .header { background: #332A86; color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
            .test-section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            .screenshot { max-width: 300px; margin: 10px; border: 1px solid #ccc; }
            .pass { color: #5E9732; font-weight: bold; }
            .fail { color: #E36F1E; font-weight: bold; }
            .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🎨 HMG AI Shortcode Visual Test Report</h1>
            <p>Comprehensive visual testing results for all shortcode styles and interactions</p>
        </div>
        
        <div class="test-section">
            <h2>📋 Test Summary</h2>
            <p>Visual tests verify the appearance and functionality of all shortcode components:</p>
            <ul>
                <li><strong>Takeaways:</strong> 4 styles (default, numbered, cards, highlights)</li>
                <li><strong>FAQ:</strong> 3 styles (accordion, list, cards)</li>
                <li><strong>Table of Contents:</strong> 4 styles (numbered, horizontal, minimal, sidebar)</li>
                <li><strong>Audio Player:</strong> 4 styles (player, compact, minimal, card)</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>🖼️ Screenshots</h2>
            <p>Screenshots are saved in <code>tests/screenshots/</code> directory:</p>
            <ul>
                <li><code>baseline/</code> - Reference screenshots</li>
                <li><code>current/</code> - Latest test run screenshots</li>
                <li><code>diff/</code> - Visual difference comparisons</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>🎯 Test Coverage</h2>
            <div class="grid">
                <div>
                    <h3>Functional Tests</h3>
                    <ul>
                        <li>FAQ accordion interactions</li>
                        <li>TOC smooth scrolling</li>
                        <li>Audio player controls</li>
                        <li>Keyboard navigation</li>
                    </ul>
                </div>
                <div>
                    <h3>Visual Tests</h3>
                    <ul>
                        <li>Brand color compliance</li>
                        <li>Typography consistency</li>
                        <li>Responsive layouts</li>
                        <li>Hover effects</li>
                    </ul>
                </div>
                <div>
                    <h3>Accessibility Tests</h3>
                    <ul>
                        <li>ARIA attributes</li>
                        <li>Keyboard navigation</li>
                        <li>Focus indicators</li>
                        <li>Screen reader support</li>
                    </ul>
                </div>
                <div>
                    <h3>Performance Tests</h3>
                    <ul>
                        <li>Loading times</li>
                        <li>JavaScript initialization</li>
                        <li>CSS rendering</li>
                        <li>Mobile responsiveness</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h2>🔧 Running Tests</h2>
            <p>To run the visual tests:</p>
            <pre><code>python run_shortcode_tests.py --all</code></pre>
            
            <p>To run specific tests:</p>
            <pre><code>python run_shortcode_tests.py --filter "takeaways"</code></pre>
            
            <p>To run with verbose output:</p>
            <pre><code>python run_shortcode_tests.py --verbose</code></pre>
        </div>
    </body>
    </html>
    """
    
    # Ensure reports directory exists
    Path('tests/reports').mkdir(parents=True, exist_ok=True)
    
    with open('tests/reports/visual_test_overview.html', 'w') as f:
        f.write(report_html)
    
    print("✅ Visual report generated: tests/reports/visual_test_overview.html")

def main():
    parser = argparse.ArgumentParser(description='Run HMG AI shortcode visual tests')
    parser.add_argument('--all', action='store_true', help='Run all tests')
    parser.add_argument('--filter', help='Filter tests by name pattern')
    parser.add_argument('--verbose', '-v', action='store_true', help='Verbose output')
    parser.add_argument('--skip-checks', action='store_true', help='Skip environment checks')
    parser.add_argument('--report-only', action='store_true', help='Generate report only')
    
    args = parser.parse_args()
    
    if args.report_only:
        generate_visual_report()
        return
    
    print("🚀 HMG AI Blog Enhancer - Shortcode Visual Testing")
    print("=" * 60)
    
    # Environment checks
    if not args.skip_checks:
        print("🔍 Checking environment...")
        
        if not check_selenium_grid():
            print("\n💡 To start Selenium Grid:")
            print("   docker run -d -p 4444:4444 --shm-size=2g selenium/standalone-chrome:latest")
            return 1
        
        if not check_wordpress():
            print("\n💡 To start WordPress:")
            print("   docker-compose up -d wordpress")
            return 1
    
    # Setup
    setup_test_environment()
    
    # Run tests
    test_filter = args.filter if args.filter else None
    if args.all:
        test_filter = None
    
    success = run_visual_tests(test_filter, args.verbose)
    
    # Generate report
    generate_visual_report()
    
    if success:
        print("\n✅ All tests passed!")
        print("📊 View reports:")
        print("   - HTML Report: tests/reports/shortcode_visual_report.html")
        print("   - Overview: tests/reports/visual_test_overview.html")
        print("   - Screenshots: tests/screenshots/")
        return 0
    else:
        print("\n❌ Some tests failed!")
        print("📊 Check reports for details:")
        print("   - HTML Report: tests/reports/shortcode_visual_report.html")
        print("   - Screenshots: tests/screenshots/")
        return 1

if __name__ == '__main__':
    sys.exit(main()) 