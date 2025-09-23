# HMG AI Blog Enhancer - API Integration Guide (2025 Edition)

This guide covers integration with the latest AI models available in 2025, including pricing, capabilities, and best practices for each provider.

## Supported AI Providers (2025)

The HMG AI Blog Enhancer supports three major AI providers with their latest 2025 model lineups:

### 1. Google Gemini (Recommended for Most Use Cases)

Google has significantly advanced their Gemini lineup in 2025, introducing "thinking models" with enhanced reasoning capabilities:

#### Available Models:

**Gemini 2.5 Pro** - *Premium Thinking Model*
- **Cost**: $1.25/$10.00 per 1M tokens (input/output)
- **Context**: 1M tokens
- **Best For**: Complex reasoning, coding, premium content analysis
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐ (8/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)

**Gemini 2.5 Flash** - *Best Balance* ⭐ **RECOMMENDED**
- **Cost**: $0.30/$2.50 per 1M tokens (input/output)
- **Context**: 1M tokens
- **Best For**: Most content generation tasks, excellent quality/cost ratio
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐ (9/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐ (9/10)

**Gemini 2.5 Flash-Lite** - *Most Cost-Effective*
- **Cost**: $0.10/$0.40 per 1M tokens (input/output)
- **Context**: 1M tokens
- **Best For**: High-volume, cost-sensitive applications
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐ (8/10)

**Gemini 2.0 Flash** - *Agent-Optimized*
- **Cost**: $0.10/$0.40 per 1M tokens (input/output)
- **Context**: 1M tokens
- **Best For**: Agent workflows, multimodal tasks
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐ (9/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐ (8/10)

### 2. OpenAI (Best for Reasoning Tasks)

OpenAI has revolutionized their lineup in 2025 with the new o-series reasoning models and enhanced GPT-4 variants:

#### Available Models:

**o4-mini** - *Best Value Reasoning* ⭐ **RECOMMENDED**
- **Cost**: $0.15/$0.60 per 1M tokens (input/output)
- **Context**: 200K tokens
- **Best For**: Reasoning tasks, coding, cost-effective intelligence
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐ (9/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐ (9/10)

**GPT-4.1** - *Enhanced Coding*
- **Cost**: $2.00/$8.00 per 1M tokens (input/output)
- **Context**: 1M tokens
- **Best For**: Advanced coding, complex analysis
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐ (7/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)

**GPT-4o** - *Multimodal Flagship*
- **Cost**: $2.50/$10.00 per 1M tokens (input/output)
- **Context**: 128K tokens
- **Best For**: Multimodal tasks, premium content
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐ (7/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)

**GPT-4.5** - *Creative & Premium*
- **Cost**: $75.00/$150.00 per 1M tokens (input/output)
- **Context**: 200K tokens
- **Best For**: Creative writing, premium applications
- **Speed**: ⭐⭐⭐⭐⭐⭐ (6/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)

### 3. Anthropic Claude (Best for Safety & Reliability)

Anthropic has launched Claude 4 in 2025, featuring hybrid reasoning and extended thinking capabilities:

#### Available Models:

**Claude 3.5 Haiku** - *Fast & Affordable* ⭐ **RECOMMENDED**
- **Cost**: $0.80/$4.00 per 1M tokens (input/output)
- **Context**: 200K tokens
- **Best For**: Fast content generation, cost-effective quality
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐ (8/10)

**Claude Sonnet 4** - *High Performance*
- **Cost**: $3.00/$15.00 per 1M tokens (input/output)
- **Context**: 200K tokens
- **Best For**: Balanced performance, extended thinking
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐ (8/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)

**Claude 3.7 Sonnet** - *Extended Thinking*
- **Cost**: $3.00/$15.00 per 1M tokens (input/output)
- **Context**: 200K tokens
- **Best For**: Complex reasoning, hybrid thinking mode
- **Speed**: ⭐⭐⭐⭐⭐⭐⭐⭐ (8/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)

**Claude Opus 4** - *Most Intelligent*
- **Cost**: $15.00/$75.00 per 1M tokens (input/output)
- **Context**: 200K tokens
- **Best For**: Most complex tasks, premium intelligence
- **Speed**: ⭐⭐⭐⭐⭐⭐ (6/10)
- **Quality**: ⭐⭐⭐⭐⭐⭐⭐⭐⭐⭐ (10/10)

## 2025 API Compatibility Updates

### Critical Changes Made for 2025

Our plugin has been fully updated to handle the new 2025 API requirements for all three providers:

#### **Google Gemini API Changes**

1. **Endpoint Structure**: Updated to use `models/` prefix
   - Old: `/v1beta/gemini-1.5-flash/generateContent`
   - New: `/v1beta/models/gemini-2.5-flash:generateContent`

2. **Thinking Budget Parameter**: Added support for thinking models
   ```json
   {
     "generationConfig": {
       "thinkingBudget": "medium"
     }
   }
   ```

3. **Enhanced Token Limits**: Increased output tokens for 2025 models
   - Gemini 2.5 Pro: Up to 65,536 output tokens
   - Gemini 2.5 Flash: Up to 8,192 output tokens

4. **Dynamic Timeouts**: Increased timeout for thinking models (up to 120 seconds)

#### **OpenAI API Changes**

1. **Reasoning Effort Parameter**: Added for o-series models
   ```json
   {
     "reasoning_effort": "medium"
   }
   ```

2. **Enhanced Context Windows**: Support for larger contexts
   - o4-mini: 200K input tokens, 100K output tokens
   - GPT-4.1: 1M input tokens, 4K+ output tokens

3. **Dynamic Timeouts**: Reasoning models get up to 180 seconds
   - High effort reasoning: 3 minutes
   - Medium effort: 2 minutes
   - Standard models: 1 minute

4. **Model-Specific Token Limits**: Automatic adjustment based on model capabilities

#### **Claude API Changes**

1. **Extended Thinking Support**: Added for Claude 4 and 3.7 models
   ```json
   {
     "extended_thinking": {
       "enabled": true,
       "mode": "balanced"
     }
   }
   ```

2. **Higher Output Limits**: Support for longer content
   - Claude Opus 4: Up to 32K output tokens
   - Claude Sonnet 4/3.7: Up to 64K output tokens

3. **Model-Specific Timeouts**: Extended thinking models get longer timeouts
   - Claude Opus 4: 3 minutes
   - Claude Sonnet 4/3.7: 2 minutes

### Automatic Model Detection

The plugin automatically detects model capabilities and adjusts parameters accordingly:

- **Thinking Models**: Automatically enable thinking/reasoning features
- **Token Limits**: Dynamic adjustment based on model specifications
- **Timeouts**: Longer timeouts for reasoning and thinking models
- **Cost Tracking**: Accurate cost calculation using 2025 pricing

### Backward Compatibility

The plugin maintains backward compatibility with legacy models:

- **Gemini 1.5 Flash/Pro**: Still supported with original API structure
- **GPT-3.5 Turbo**: Legacy support maintained
- **Claude 3 Haiku**: Original API parameters preserved

## Best Practices for 2025

### Model Selection Strategy

1. **Start Cost-Effective**: Begin with Gemini 2.5 Flash-Lite or o4-mini
2. **Scale Quality**: Move to Gemini 2.5 Flash or Claude 3.5 Haiku for better quality
3. **Premium Tasks**: Use Claude Opus 4, GPT-4.1, or Gemini 2.5 Pro for complex work

### Performance Optimization

1. **Caching**: Enable content caching to reduce API costs
2. **Batch Processing**: Group similar requests when possible
3. **Model Switching**: Use different models for different content types
4. **Timeout Management**: Allow sufficient time for thinking models

### Cost Management

1. **Monitor Usage**: Track token consumption across all providers
2. **Set Limits**: Use spending limits to control costs
3. **Choose Wisely**: Match model capability to task complexity
4. **Cache Aggressively**: Reduce redundant API calls

## Integration Examples

### Basic Content Generation

```php
// The plugin automatically handles 2025 API requirements
$gemini_service = new HMG_AI_Gemini_Service();
$result = $gemini_service->generate_content('takeaways', $content, $post_id);

if ($result['success']) {
    echo $result['content']; // Formatted HTML output
}
```

### Model-Specific Features

The plugin automatically enables advanced features based on the selected model:

- **Gemini 2.5 Models**: Thinking budget automatically set
- **OpenAI o-series**: Reasoning effort configured
- **Claude 4/3.7**: Extended thinking enabled

### Error Handling

```php
// Enhanced error handling for 2025 APIs
if (!$result['success']) {
    // Automatic fallback to secondary provider
    $fallback_result = $service_manager->generate_with_fallback($content_type, $content);
}
```

## Migration from 2024

If you're upgrading from a 2024 version of the plugin:

1. **Update Model Names**: New models are automatically available in dropdowns
2. **Check API Keys**: Ensure all provider API keys are configured
3. **Review Settings**: New default models provide better value
4. **Test Functionality**: Use the "Test AI Providers" button to verify connections

## Troubleshooting 2025 APIs

### Common Issues

1. **Timeout Errors**: Increase timeout for thinking/reasoning models
2. **Token Limit Exceeded**: Check model-specific output limits
3. **Invalid Parameters**: Ensure API keys support 2025 features
4. **Cost Tracking**: Verify spending limits are set appropriately

### Support

For technical support with 2025 API integration:
- Check the WordPress admin error logs
- Use the built-in connection testing tools
- Review the plugin's debug information

---

*Last updated: January 2025 - Reflects current 2025 model availability and pricing* 