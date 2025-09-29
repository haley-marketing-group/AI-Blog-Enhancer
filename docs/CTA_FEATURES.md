# CTA Manager Features Documentation

## Overview
The CTA Manager is a powerful feature integrated into the HMG AI Blog Enhancer plugin (v1.1.0) that allows you to add professional call-to-action (CTA) boxes at the end of your blog posts.

## Features

### 1. Pre-built CTA Templates
The plugin comes with 10 professionally designed CTA templates:

- **Search Jobs** - Direct visitors to your job search functionality
- **Sign Up for Job Alerts** - Encourage job alert subscriptions
- **Send Us Your Resume** - Promote resume submissions
- **Check Out Our Talent Showcase** - Highlight your talent pool
- **View Our Top Talent** - Feature exceptional candidates
- **Sign Up for Talent Alerts** - Build your talent alert list
- **Contact Us** - General contact prompt
- **Request an Employee** - Direct hiring inquiries
- **Follow Us** - Social media engagement
- **Join Our Team** - Recruitment/careers promotion

### 2. Custom CTAs
Create unique CTAs for individual posts with full customization options.

### 3. Customization Options

#### Content Settings
- **Title** - Eye-catching headline
- **Content** - Detailed description with rich text support
- **Button Text** - Compelling action text
- **Button URL** - Link destination
- **Button Target** - New window/same window control
- **Button CSS Classes** - Custom styling options

#### Image Options
- **Image URL** - Add visual elements
- **Image Alignment**:
  - Left - Image on the left, content on right
  - Right - Image on the right, content on left
  - Top - Image above content
  - Bottom - Image below content
  - Background - Full CTA background image

#### Styling Options
- **Text Color** - Customize text appearance
- **Background Color** - Set CTA background
- **Border Color** - Define border appearance
- **Border Width** - Adjust border thickness
- **Border Radius** - Create rounded corners
- **Padding** - Control internal spacing
- **Custom CSS** - Advanced styling per CTA

## Usage

### Global Settings
1. Navigate to **HMG AI Blog Enhancer → CTA Manager** in your WordPress admin
2. Click on the **General Settings** tab
3. Configure default styling that applies to all CTAs
4. Save your settings

### Setting Up CTA Templates
1. Go to **HMG AI Blog Enhancer → CTA Manager**
2. Click on any template tab (e.g., "Search Jobs")
3. Enable the template by checking "Enable this CTA"
4. Fill in the content fields:
   - Title
   - Content (supports rich text)
   - Button text and URL
   - Optional image
5. Save the template

### Adding CTAs to Posts
1. Edit any blog post
2. Look for the **CTA Manager** metabox below the content editor
3. Select a CTA from the dropdown:
   - Choose a pre-configured template
   - Select "Custom" to create a unique CTA for this post
4. If using Custom:
   - Fill in all content fields
   - Optionally override default styling
   - Add custom CSS if needed
5. Update/Publish the post

### Custom Styling
The plugin provides several pre-defined button classes:
- `hmg-cta-button hmg-cta-btn-default` - Default blue button
- `hmg-cta-button hmg-cta-btn-primary` - Green primary button
- `hmg-cta-button hmg-cta-btn-secondary` - White outlined button
- `hmg-cta-button hmg-cta-btn-outline` - Blue outlined button

## Responsive Design
All CTAs are fully responsive and adapt to different screen sizes:
- Desktop: Full layouts with side-by-side images
- Tablet: Optimized spacing and font sizes
- Mobile: Stacked layouts with full-width buttons

## Best Practices

### Content Tips
1. Keep titles short and action-oriented (5-8 words)
2. Use clear, benefit-focused content (2-3 sentences)
3. Make button text specific ("View Open Positions" vs "Click Here")
4. Test different CTAs on similar posts to find what works

### Design Tips
1. Use contrasting colors for better visibility
2. Keep consistent styling across similar post types
3. Use images that reinforce your message
4. Don't overwhelm - one CTA per post is usually enough

### Performance Tips
1. Optimize images before uploading (recommended: < 100KB)
2. Use web-friendly formats (JPEG for photos, PNG for graphics)
3. Consider using background images sparingly on mobile-heavy sites

## Technical Details

### Database Storage
- Template settings: Stored in WordPress options table
- Post-specific CTAs: Stored as post meta data
- Global settings: Single option entry for efficiency

### Hooks & Filters
The CTA content is added via the `the_content` filter at priority 20, ensuring it appears after the main content but before most other plugins' additions.

### CSS Classes
- `.hmg-cta-box` - Main container
- `.hmg-cta-box-title` - Title element
- `.hmg-cta-box-content` - Content area
- `.hmg-cta-box-footer` - Button container
- `.hmg-cta-button` - Button element

## Troubleshooting

### CTA Not Appearing
1. Check that the CTA type is selected in the post
2. Ensure the template is enabled in settings
3. Verify the post type is "post" (not page)

### Styling Issues
1. Clear any caching plugins
2. Check for theme conflicts
3. Use browser inspector to identify CSS conflicts

### Image Problems
1. Verify image URL is correct
2. Check image permissions
3. Ensure proper file format

## Updates & Compatibility
- **Version**: 1.1.0
- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Browser Support**: All modern browsers + IE11

## Support
For additional help or feature requests, please contact Haley Marketing support.
