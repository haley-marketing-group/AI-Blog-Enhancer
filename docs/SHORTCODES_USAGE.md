# HMG AI Blog Enhancer - Shortcodes Usage Guide

This guide covers all available shortcodes and their usage options in the HMG AI Blog Enhancer plugin.

## Available Shortcodes

### 1. Key Takeaways - `[hmg_ai_takeaways]`

Display AI-generated key takeaways from your content.

#### Basic Usage
```
[hmg_ai_takeaways]
```

#### With Parameters
```
[hmg_ai_takeaways post_id="123" style="cards"]
```

#### Available Styles
- **default** - Clean list with checkmark icons
- **numbered** - Numbered list with circular badges
- **cards** - Grid layout with card design
- **highlights** - Left-aligned highlights with gradient markers

#### Parameters
- `post_id` - Target post ID (defaults to current post)
- `style` - Display style (default: "default")

#### Example Outputs
- **Default Style**: Professional list with green checkmarks
- **Cards Style**: Responsive grid with numbered cards
- **Highlights Style**: Vertical timeline with gradient markers

---

### 2. FAQ Section - `[hmg_ai_faq]`

Display AI-generated frequently asked questions.

#### Basic Usage
```
[hmg_ai_faq]
```

#### With Parameters
```
[hmg_ai_faq post_id="123" style="cards"]
```

#### Available Styles
- **accordion** - Interactive collapsible questions (default)
- **list** - Simple question and answer list
- **cards** - Card-based layout with Q&A pairs

#### Parameters
- `post_id` - Target post ID (defaults to current post)
- `style` - Display style (default: "accordion")

#### Features
- **Accordion Style**: Click to expand/collapse answers
- **SEO Optimization**: Includes structured data for search engines
- **Accessibility**: Full keyboard navigation support
- **Print Friendly**: Auto-expands all answers when printing

---

### 3. Table of Contents - `[hmg_ai_toc]`

Display AI-generated table of contents with navigation.

#### Basic Usage
```
[hmg_ai_toc]
```

#### With Parameters
```
[hmg_ai_toc post_id="123" style="sidebar"]
```

#### Available Styles
- **numbered** - Traditional numbered list (default)
- **horizontal** - Horizontal scrolling navigation
- **minimal** - Clean text-only links
- **sidebar** - Sticky sidebar with progress tracking

#### Parameters
- `post_id` - Target post ID (defaults to current post)
- `style` - Display style (default: "numbered")

#### Features
- **Smooth Scrolling**: Animated navigation to sections
- **Progress Tracking**: Visual progress bar (sidebar style)
- **Active States**: Highlights current section
- **Responsive**: Mobile-optimized layouts

---

### 4. Audio Player - `[hmg_ai_audio]`

Display AI-generated audio version of your content.

#### Basic Usage
```
[hmg_ai_audio]
```

#### With Parameters
```
[hmg_ai_audio post_id="123" style="card"]
```

#### Available Styles
- **player** - Full-featured audio player (default)
- **compact** - Minimal horizontal layout
- **minimal** - Custom play button with progress
- **card** - Rich media card with metadata

#### Parameters
- `post_id` - Target post ID (defaults to current post)
- `style` - Display style (default: "player")

#### Features
- **Speed Control**: Adjustable playback speed (0.75x to 2x)
- **Progress Tracking**: Visual progress indicators
- **Download Option**: Direct MP3 download links
- **Accessibility**: Full screen reader support

---

## Advanced Usage Examples

### Multiple Shortcodes in One Post
```
[hmg_ai_toc style="horizontal"]

Your blog content here...

[hmg_ai_takeaways style="cards"]

More content...

[hmg_ai_faq style="accordion"]

[hmg_ai_audio style="compact"]
```

### Styling Combinations
```
<!-- Professional layout -->
[hmg_ai_toc style="sidebar"]
[hmg_ai_takeaways style="highlights"]
[hmg_ai_faq style="cards"]
[hmg_ai_audio style="card"]

<!-- Minimal layout -->
[hmg_ai_toc style="minimal"]
[hmg_ai_takeaways style="default"]
[hmg_ai_faq style="list"]
[hmg_ai_audio style="minimal"]
```

### Specific Post Targeting
```
<!-- Display takeaways from another post -->
[hmg_ai_takeaways post_id="456" style="numbered"]

<!-- FAQ from a different post -->
[hmg_ai_faq post_id="789" style="accordion"]
```

---

## Data Format Requirements

### Takeaways Data Format
The plugin expects takeaways data in JSON format:
```json
[
    "First key takeaway point",
    "Second important insight",
    "Third actionable item"
]
```

### FAQ Data Format
FAQ data should be structured as:
```json
[
    {
        "question": "What is the main benefit?",
        "answer": "The main benefit is improved user engagement through AI-powered content."
    },
    {
        "question": "How does it work?",
        "answer": "The system analyzes your content and generates relevant questions and answers."
    }
]
```

### TOC Data Format
Table of contents data structure:
```json
[
    {
        "title": "Introduction",
        "anchor": "#introduction",
        "level": 1,
        "subsections": [
            {
                "title": "Overview",
                "anchor": "#overview"
            }
        ]
    },
    {
        "title": "Main Content",
        "anchor": "#main-content",
        "level": 1
    }
]
```

---

## Customization Options

### CSS Classes for Custom Styling
Each shortcode generates specific CSS classes for customization:

#### Takeaways Classes
- `.hmg-ai-takeaways` - Main container
- `.hmg-ai-takeaway-item` - Individual takeaway
- `.hmg-ai-takeaways-default` - Default style
- `.hmg-ai-takeaways-cards` - Cards style
- `.hmg-ai-takeaways-numbered` - Numbered style
- `.hmg-ai-takeaways-highlights` - Highlights style

#### FAQ Classes
- `.hmg-ai-faq` - Main container
- `.hmg-ai-faq-accordion` - Accordion container
- `.hmg-ai-faq-accordion-button` - Question buttons
- `.hmg-ai-faq-accordion-content` - Answer content
- `.hmg-ai-faq-cards` - Cards layout
- `.hmg-ai-faq-list` - List layout

#### TOC Classes
- `.hmg-ai-toc` - Main container
- `.hmg-ai-toc-numbered` - Numbered style
- `.hmg-ai-toc-horizontal` - Horizontal style
- `.hmg-ai-toc-minimal` - Minimal style
- `.hmg-ai-toc-sidebar` - Sidebar style

#### Audio Classes
- `.hmg-ai-audio` - Main container
- `.hmg-ai-audio-player` - Default player
- `.hmg-ai-audio-compact` - Compact style
- `.hmg-ai-audio-minimal` - Minimal style
- `.hmg-ai-audio-card` - Card style

### JavaScript Events
The plugin triggers custom events for advanced integrations:
- `hmg-ai-faq-opened` - When FAQ item opens
- `hmg-ai-toc-navigated` - When TOC link is clicked
- `hmg-ai-audio-played` - When audio starts playing
- `hmg-ai-takeaway-highlighted` - When takeaway is clicked

---

## Accessibility Features

### Keyboard Navigation
- **Tab Navigation**: All interactive elements are keyboard accessible
- **Enter/Space**: Activate buttons and links
- **Arrow Keys**: Navigate between FAQ items

### Screen Reader Support
- **ARIA Labels**: Comprehensive labeling for all components
- **Semantic HTML**: Proper heading hierarchy and landmarks
- **Live Regions**: Dynamic content announcements

### Visual Accessibility
- **High Contrast**: Meets WCAG AA standards
- **Focus Indicators**: Clear visual focus states
- **Reduced Motion**: Respects user motion preferences

---

## Performance Optimization

### Lazy Loading
Audio files are loaded only when needed to improve page speed.

### Caching
Generated shortcode content is cached for improved performance.

### Responsive Images
All icons and graphics are optimized for different screen sizes.

---

## Troubleshooting

### Common Issues

#### Shortcode Not Displaying
1. Check if AI content has been generated for the post
2. Verify the post_id parameter is correct
3. Ensure the plugin is activated

#### Styling Issues
1. Clear any caching plugins
2. Check for theme CSS conflicts
3. Verify custom CSS hasn't overridden plugin styles

#### Audio Not Playing
1. Check audio file URL is valid
2. Verify browser audio support
3. Check for JavaScript errors in console

### Debug Mode
Add `?hmg_ai_debug=1` to your URL to see debug information.

---

## Integration Examples

### With Popular Page Builders

#### Elementor
Add shortcodes in Text Editor widgets or use the Shortcode widget.

#### Gutenberg
Use the Shortcode block or add directly in HTML blocks.

#### Classic Editor
Insert shortcodes directly in the content editor.

### With Themes
Most themes support shortcodes automatically. For custom integration, use:
```php
echo do_shortcode('[hmg_ai_takeaways]');
```

---

## Best Practices

### Content Strategy
1. **Takeaways**: Use 3-5 key points for optimal engagement
2. **FAQ**: Include 5-10 most relevant questions
3. **TOC**: Ensure proper heading structure in your content
4. **Audio**: Keep audio files under 10MB for best performance

### User Experience
1. **Mobile First**: Test all styles on mobile devices
2. **Loading Speed**: Monitor page load times with audio content
3. **Accessibility**: Always test with keyboard navigation
4. **Print Friendly**: Verify content prints correctly

### SEO Optimization
1. **Structured Data**: FAQ shortcodes automatically include schema markup
2. **Heading Hierarchy**: TOC respects your content's heading structure
3. **Meta Descriptions**: Use takeaways content for meta descriptions
4. **Internal Linking**: TOC improves page structure for search engines 