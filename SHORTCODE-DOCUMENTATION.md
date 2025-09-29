# AI Blog Enhancer - Summarize Shortcode Documentation

## Overview
The `[hmg_ai_summarize]` shortcode adds "Summarize this blog post with:" buttons to your posts, allowing readers to quickly summarize your content using popular AI services like ChatGPT, Perplexity, Claude, and Gemini.

## Basic Usage
```
[hmg_ai_summarize]
```

## Customization Options

### 1. Choose which services to display
```
[hmg_ai_summarize services="chatgpt,perplexity,claude,gemini"]
```

Or display only specific services:
```
[hmg_ai_summarize services="chatgpt,perplexity"]
```

### 2. Change the label text
```
[hmg_ai_summarize label="Get an AI summary:"]
```

Or remove the label entirely:
```
[hmg_ai_summarize label=""]
```

### 3. Change button alignment
```
[hmg_ai_summarize align="center"]
```

Options: `left` (default), `center`, `right`

### 4. Change display style
```
[hmg_ai_summarize style="links"]
```

Options: `buttons` (default), `links`

## Examples

### Example 1: Center-aligned with custom label
```
[hmg_ai_summarize label="Quick Summary Options:" align="center"]
```

### Example 2: Only ChatGPT and Claude as text links
```
[hmg_ai_summarize services="chatgpt,claude" style="links"]
```

### Example 3: Right-aligned with all services
```
[hmg_ai_summarize align="right" services="chatgpt,perplexity,claude,gemini"]
```

### Example 4: No label, centered buttons
```
[hmg_ai_summarize label="" align="center"]
```

## How It Works
When a reader clicks one of the summarize buttons, they are taken to the respective AI service with your article URL pre-filled in a prompt asking the AI to summarize the content. The reader can then modify the prompt if needed before getting their summary.

## Styling
The buttons automatically adapt to your theme and are mobile-responsive. They include hover effects and smooth animations for a professional appearance.

## Available Services
- **ChatGPT** - OpenAI's conversational AI
- **Perplexity** - AI-powered search and summarization  
- **Claude** - Anthropic's AI assistant
- **Gemini** - Google's AI model

## Notes
- The shortcode automatically uses the current post's URL
- Buttons open in a new tab to keep readers on your site
- The feature works on both posts and pages
- No API keys required - it uses the reader's own AI service accounts
