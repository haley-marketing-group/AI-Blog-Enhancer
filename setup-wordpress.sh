#!/bin/bash

# HMG AI Blog Enhancer - WordPress Setup Script
# Automates WordPress installation and plugin setup

set -e

echo "🚀 Setting up WordPress for HMG AI Blog Enhancer development..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
WP_URL="http://localhost:8085"
WP_TITLE="HMG AI Blog Enhancer Dev Site"
WP_ADMIN_USER="admin"
WP_ADMIN_PASS="admin123"
WP_ADMIN_EMAIL="dev@haleymarketing.com"

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

# Wait for WordPress to be ready
print_status "Waiting for WordPress to be ready..."
for i in {1..30}; do
    if curl -s -f "$WP_URL" > /dev/null; then
        break
    fi
    echo -n "."
    sleep 2
done
echo

# Check if WordPress is already installed
if curl -s "$WP_URL/wp-admin/" | grep -q "wp-login"; then
    print_success "WordPress is already installed!"
else
    print_status "Installing WordPress..."
    
    # Install WordPress using wp-cli in the container
    docker exec hmg-ai-wordpress wp core install \
        --url="$WP_URL" \
        --title="$WP_TITLE" \
        --admin_user="$WP_ADMIN_USER" \
        --admin_password="$WP_ADMIN_PASS" \
        --admin_email="$WP_ADMIN_EMAIL" \
        --allow-root
    
    print_success "WordPress installed successfully!"
fi

# Activate our plugin
print_status "Activating HMG AI Blog Enhancer plugin..."

# Check if plugin directory exists in the container
if docker exec hmg-ai-wordpress test -d /var/www/html/wp-content/plugins/hmg-ai-blog-enhancer; then
    # Activate the plugin
    docker exec hmg-ai-wordpress wp plugin activate hmg-ai-blog-enhancer --allow-root || {
        print_warning "Plugin activation failed, but plugin files are present"
    }
    print_success "Plugin activation attempted"
else
    print_error "Plugin directory not found in container"
    print_status "Plugin should be mounted at: /var/www/html/wp-content/plugins/hmg-ai-blog-enhancer"
fi

# Set up some test content
print_status "Creating test content..."

# Create a test post
docker exec hmg-ai-wordpress wp post create \
    --post_type=post \
    --post_title="Test Blog Post for AI Enhancement" \
    --post_content="<h2>Introduction</h2><p>This is a test blog post to demonstrate the HMG AI Blog Enhancer plugin capabilities.</p><h2>Main Content</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><h2>Key Points</h2><p>Here are some important points that should be enhanced by our AI plugin:</p><ul><li>Point one about the topic</li><li>Point two with more details</li><li>Point three for comprehensive coverage</li></ul><h2>Conclusion</h2><p>This concludes our test content for the AI enhancement features.</p>" \
    --post_status=publish \
    --allow-root

# Create a test page
docker exec hmg-ai-wordpress wp post create \
    --post_type=page \
    --post_title="About Our AI Enhancement" \
    --post_content="<h2>About HMG AI Blog Enhancer</h2><p>This page demonstrates the AI enhancement capabilities of our plugin.</p><h3>Features</h3><p>Our plugin provides the following AI-powered features:</p><ul><li>Automatic key takeaways generation</li><li>FAQ creation based on content</li><li>Table of contents generation</li><li>Audio version creation</li></ul>" \
    --post_status=publish \
    --allow-root

print_success "Test content created"

# Install useful development plugins
print_status "Installing development plugins..."

docker exec hmg-ai-wordpress wp plugin install query-monitor --activate --allow-root || print_warning "Query Monitor installation failed"
docker exec hmg-ai-wordpress wp plugin install debug-bar --activate --allow-root || print_warning "Debug Bar installation failed"

# Set up permalinks
print_status "Setting up permalinks..."
docker exec hmg-ai-wordpress wp rewrite structure '/%postname%/' --allow-root

print_success "WordPress development environment is ready!"

echo
echo "🎉 Setup Complete!"
echo "================================"
echo "WordPress URL: $WP_URL"
echo "Admin URL: $WP_URL/wp-admin"
echo "Username: $WP_ADMIN_USER"
echo "Password: $WP_ADMIN_PASS"
echo "phpMyAdmin: http://localhost:8086"
echo
echo "🔧 Next Steps:"
echo "1. Visit $WP_URL to see your site"
echo "2. Login to admin at $WP_URL/wp-admin"
echo "3. Check if the HMG AI Blog Enhancer plugin is active"
echo "4. Test the plugin functionality"
echo
echo "🐛 Development Tools:"
echo "- Query Monitor: Installed for debugging"
echo "- Debug Bar: Installed for development"
echo "- WordPress Debug: Enabled"
echo
echo "📊 To check plugin status:"
echo "docker exec hmg-ai-wordpress wp plugin list --allow-root" 