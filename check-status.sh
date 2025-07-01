#!/bin/bash

# HMG AI Blog Enhancer - Status Check Script
# Shows current progress and what's next in development

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Icons
CHECK="✅"
CROSS="❌"
WARNING="⚠️"
ROCKET="🚀"
GEAR="⚙️"
CHART="📊"

echo -e "${BLUE}${ROCKET} HMG AI Blog Enhancer - Development Status${NC}"
echo "=================================================="

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to check Docker service status
check_docker_status() {
    echo -e "\n${CYAN}${GEAR} Development Environment Status${NC}"
    echo "----------------------------------------"
    
    if command_exists docker; then
        echo -e "${GREEN}${CHECK} Docker installed${NC}"
        
        if docker info >/dev/null 2>&1; then
            echo -e "${GREEN}${CHECK} Docker daemon running${NC}"
            
            # Check if our containers are running
            if docker-compose ps | grep -q "hmg-ai-wordpress"; then
                echo -e "${GREEN}${CHECK} WordPress container running${NC}"
                
                # Check WordPress accessibility
                if curl -s http://localhost:8080 >/dev/null; then
                    echo -e "${GREEN}${CHECK} WordPress accessible (http://localhost:8080)${NC}"
                else
                    echo -e "${YELLOW}${WARNING} WordPress not accessible${NC}"
                fi
            else
                echo -e "${RED}${CROSS} WordPress container not running${NC}"
                echo -e "${YELLOW}   Run: docker-compose up -d${NC}"
            fi
            
            # Check Selenium Grid
            if docker-compose ps | grep -q "selenium-hub"; then
                echo -e "${GREEN}${CHECK} Selenium Grid running${NC}"
                
                if curl -s http://localhost:4444/status >/dev/null; then
                    echo -e "${GREEN}${CHECK} Selenium Grid accessible (http://localhost:4444)${NC}"
                else
                    echo -e "${YELLOW}${WARNING} Selenium Grid not accessible${NC}"
                fi
            else
                echo -e "${RED}${CROSS} Selenium Grid not running${NC}"
            fi
        else
            echo -e "${RED}${CROSS} Docker daemon not running${NC}"
        fi
    else
        echo -e "${RED}${CROSS} Docker not installed${NC}"
    fi
}

# Function to check testing framework status
check_testing_status() {
    echo -e "\n${CYAN}${GEAR} Testing Framework Status${NC}"
    echo "----------------------------------------"
    
    if [ -d "tests/visual" ]; then
        echo -e "${GREEN}${CHECK} Visual testing framework present${NC}"
        
        if command_exists pytest; then
            echo -e "${GREEN}${CHECK} pytest installed${NC}"
            
            # Run a quick test to verify framework
            if pytest tests/visual/test_setup_verification.py::TestSetupVerification::test_wordpress_is_accessible -v --tb=no -q >/dev/null 2>&1; then
                echo -e "${GREEN}${CHECK} Testing framework functional${NC}"
            else
                echo -e "${YELLOW}${WARNING} Testing framework needs verification${NC}"
                echo -e "${YELLOW}   Run: pytest tests/visual/test_setup_verification.py -v${NC}"
            fi
        else
            echo -e "${RED}${CROSS} pytest not installed${NC}"
            echo -e "${YELLOW}   Run: pip install -r tests/requirements.txt${NC}"
        fi
    else
        echo -e "${RED}${CROSS} Testing framework not set up${NC}"
        echo -e "${YELLOW}   Run: ./setup-testing.sh${NC}"
    fi
}

# Function to check current sprint progress
check_sprint_progress() {
    echo -e "\n${CYAN}${CHART} Current Sprint Progress${NC}"
    echo "----------------------------------------"
    
    # Determine current sprint based on existing files/features
    current_sprint="Sprint 1: Foundation & Standards"
    
    if [ -f "hmg-ai-blog-enhancer.php" ]; then
        echo -e "${GREEN}${CHECK} Main plugin file exists${NC}"
        current_sprint="Sprint 1: Foundation & Standards (In Progress)"
    else
        echo -e "${YELLOW}${WARNING} Main plugin file not created${NC}"
    fi
    
    if [ -d "includes" ]; then
        echo -e "${GREEN}${CHECK} Plugin structure created${NC}"
    else
        echo -e "${YELLOW}${WARNING} Plugin structure not created${NC}"
    fi
    
    # Check for brand compliance setup
    if [ -f "tests/visual/test_brand_compliance.py" ]; then
        echo -e "${GREEN}${CHECK} Brand compliance tests ready${NC}"
    else
        echo -e "${YELLOW}${WARNING} Brand compliance tests not set up${NC}"
    fi
    
    echo -e "\n${PURPLE}Current Sprint: ${current_sprint}${NC}"
}

# Function to show what's next
show_whats_next() {
    echo -e "\n${CYAN}${ROCKET} What's Next?${NC}"
    echo "----------------------------------------"
    
    # Check current state and suggest next actions
    if [ ! -f "hmg-ai-blog-enhancer.php" ]; then
                 echo -e "${YELLOW}📋 NEXT: Create main plugin file${NC}"
         echo "   1. Create hmg-ai-blog-enhancer.php with proper headers"
         echo "   2. Implement basic plugin structure"
         echo "   3. Add Haley Marketing branding"
         echo ""
         echo -e "${BLUE}Command: Follow docs/guides/PROJECT_STRUCTURE.md guide${NC}"
        
    elif [ ! -d "includes/services" ]; then
        echo -e "${YELLOW}📋 NEXT: Set up plugin architecture${NC}"
        echo "   1. Create includes/ directory structure"
        echo "   2. Implement core classes"
        echo "   3. Set up authentication service"
        echo ""
        echo -e "${BLUE}Command: mkdir -p includes/{services,generators,shortcodes}${NC}"
        
    elif [ ! -f "includes/services/class-auth-service.php" ]; then
        echo -e "${YELLOW}📋 NEXT: Implement authentication${NC}"
        echo "   1. Create authentication service"
        echo "   2. Set up user tier management"
        echo "   3. Implement API key validation"
        echo ""
                 echo -e "${BLUE}Command: Follow docs/guides/TECHNICAL_IMPLEMENTATION.md${NC}"
         
     else
         echo -e "${GREEN}📋 NEXT: Continue with current sprint tasks${NC}"
         echo "   Check docs/roadmaps/INTEGRATED_ROADMAP.md for detailed tasks"
    fi
    
    echo -e "\n${PURPLE}Quick Commands:${NC}"
    echo "  ./setup-testing.sh          - Set up testing environment"
    echo "  pytest tests/visual/ -v     - Run visual tests"
    echo "  npm run lint                - Check code quality"
    echo "  docker-compose up -d        - Start development environment"
}

# Function to run quality checks
run_quality_checks() {
    echo -e "\n${CYAN}${CHART} Quality Checks${NC}"
    echo "----------------------------------------"
    
    # Check code quality tools
    if command_exists npm; then
        if [ -f "package.json" ]; then
            echo -e "${GREEN}${CHECK} npm configuration present${NC}"
            
            if npm list eslint >/dev/null 2>&1; then
                echo -e "${GREEN}${CHECK} ESLint configured${NC}"
            else
                echo -e "${YELLOW}${WARNING} ESLint not configured${NC}"
            fi
        else
            echo -e "${YELLOW}${WARNING} package.json not found${NC}"
        fi
    else
        echo -e "${YELLOW}${WARNING} npm not installed${NC}"
    fi
    
    # Check PHP code standards
    if command_exists composer; then
        if [ -f "composer.json" ]; then
            echo -e "${GREEN}${CHECK} Composer configuration present${NC}"
        else
            echo -e "${YELLOW}${WARNING} composer.json not found${NC}"
        fi
    else
        echo -e "${YELLOW}${WARNING} Composer not installed${NC}"
    fi
    
    # Check for brand compliance
    if [ -f "tests/visual/test_brand_compliance.py" ]; then
        echo -e "${GREEN}${CHECK} Brand compliance tests available${NC}"
        
        # Run brand compliance check if possible
        if command_exists pytest && docker-compose ps | grep -q "hmg-ai-wordpress"; then
            echo -e "${BLUE}Running brand compliance check...${NC}"
            if pytest tests/visual/test_brand_compliance.py::TestHaleyMarketingBrandCompliance::test_professional_polish_standards -v --tb=no -q >/dev/null 2>&1; then
                echo -e "${GREEN}${CHECK} Brand compliance check passed${NC}"
            else
                echo -e "${YELLOW}${WARNING} Brand compliance needs attention${NC}"
            fi
        fi
    fi
}

# Function to show recent test results
show_test_results() {
    echo -e "\n${CYAN}${CHART} Recent Test Results${NC}"
    echo "----------------------------------------"
    
    if [ -f "tests/reports/visual_test_report.html" ]; then
        echo -e "${GREEN}${CHECK} Visual test report available${NC}"
        echo -e "${BLUE}   View: open tests/reports/visual_test_report.html${NC}"
    else
        echo -e "${YELLOW}${WARNING} No recent test reports${NC}"
        echo -e "${BLUE}   Run: pytest tests/visual/ --html=tests/reports/visual_test_report.html${NC}"
    fi
    
    # Check for screenshots
    if [ -d "tests/screenshots/current" ] && [ "$(ls -A tests/screenshots/current)" ]; then
        screenshot_count=$(ls tests/screenshots/current | wc -l)
        echo -e "${GREEN}${CHECK} ${screenshot_count} recent screenshots available${NC}"
    else
        echo -e "${YELLOW}${WARNING} No recent screenshots${NC}"
    fi
    
    # Check for baseline screenshots
    if [ -d "tests/screenshots/baseline" ] && [ "$(ls -A tests/screenshots/baseline)" ]; then
        baseline_count=$(ls tests/screenshots/baseline | wc -l)
        echo -e "${GREEN}${CHECK} ${baseline_count} baseline screenshots established${NC}"
    else
        echo -e "${YELLOW}${WARNING} No baseline screenshots${NC}"
        echo -e "${BLUE}   Run tests to create baselines${NC}"
    fi
}

# Function to show performance metrics
show_performance_metrics() {
    echo -e "\n${CYAN}${CHART} Performance Status${NC}"
    echo "----------------------------------------"
    
    if curl -s http://localhost:8080 >/dev/null; then
        # Measure WordPress load time
        load_time=$(curl -o /dev/null -s -w '%{time_total}' http://localhost:8080)
        load_time_ms=$(echo "$load_time * 1000" | bc 2>/dev/null || echo "N/A")
        
        if [ "$load_time_ms" != "N/A" ]; then
            echo -e "${GREEN}${CHECK} WordPress load time: ${load_time_ms}ms${NC}"
            
            # Check if under target (500ms additional overhead)
            if (( $(echo "$load_time < 1.0" | bc -l 2>/dev/null || echo 0) )); then
                echo -e "${GREEN}${CHECK} Performance target met${NC}"
            else
                echo -e "${YELLOW}${WARNING} Performance needs optimization${NC}"
            fi
        else
            echo -e "${YELLOW}${WARNING} Could not measure load time${NC}"
        fi
    else
        echo -e "${RED}${CROSS} WordPress not accessible for performance testing${NC}"
    fi
}

# Main execution
main() {
    check_docker_status
    check_testing_status
    check_sprint_progress
    show_whats_next
    run_quality_checks
    show_test_results
    show_performance_metrics
    
    echo -e "\n${GREEN}${ROCKET} Status check complete!${NC}"
    echo "=================================================="
    echo ""
    echo -e "${PURPLE}For detailed guidance, check:${NC}"
    echo "  📖 docs/roadmaps/INTEGRATED_ROADMAP.md    - Complete development plan"
    echo "  🚀 docs/testing/QUICK_START_TESTING.md   - Testing quick start"
    echo "  🏗️  docs/guides/PROJECT_STRUCTURE.md    - Plugin architecture"
    echo "  🔧 docs/guides/TECHNICAL_IMPLEMENTATION.md - Code examples"
    echo "  📋 docs/DEVELOPMENT_LOG.md               - Work progress & decisions"
    echo ""
    echo -e "${BLUE}Need help? Just ask: 'What's next?'${NC}"
}

# Run main function
main 