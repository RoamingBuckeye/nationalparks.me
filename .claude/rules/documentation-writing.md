---
paths:
  - README.md
  - docs/**/*.md
---

# Documentation Writing Rules

Write documentation as if explaining to a colleague over coffee — focused, professional, but not rigid.

## Voice and Tone

- Address the reader directly with "you"
- Use contractions naturally: you'll, we're, let's, it's
- Write confidently: "This does X" not "This should do X"
- Active voice always: "Create the file" not "The file should be created"
- Never use condescending language: "simply", "just", "obviously", "clearly"
- No excessive exclamation points (one per section maximum)
- No emojis

```markdown
❌ "The configuration file must be published prior to utilization."
✅ "First, publish your config file so you can customize these options."

❌ "It is recommended that validation be implemented using the provided methods."
✅ "Validate your inputs using the validate() method."
```

## Structure

- Front-load important information — get to the point quickly
- Build from simple to complex (progressive disclosure)
- Use clear, descriptive headers: "Configuring Authentication" not "Overview"
- Keep paragraphs to 3-4 sentences maximum
- Use bullet lists for related items, numbered lists for sequential steps, tables for comparisons

## Code Examples

- Show real-world, practical, complete examples with meaningful variable names
- Include error handling in examples
- Comments explain "why", not "what"
- Show both basic and advanced usage

```php
// ❌ Abstract
$result = $service->process($data);

// ✅ Practical and complete
try {
    $payment = new Payment(amount: 99.99, currency: 'USD');
    $result = $paymentService->process($payment);
} catch (PaymentException $e) {
    logger()->error('Payment failed', ['error' => $e->getMessage()]);
}
```

## Callouts

Use GitHub-style alerts for important information:

- **NOTE**: General context or clarifications
- **TIP**: Shortcuts or optional optimizations
- **IMPORTANT**: Critical steps that must be followed
- **WARNING**: Security risks, breaking changes, or dangerous operations
- **CAUTION**: Things that might cause problems if not handled carefully

```markdown
> [!TIP]
> Optional information to help users be more successful.

> [!WARNING]
> Critical content demanding immediate user attention due to potential risks.
```

## Anti-Patterns

- ❌ Passive voice
- ❌ Jargon without explanation — define terms on first use
- ❌ Walls of text without breaks
- ❌ Vague or incomplete code examples
- ❌ Missing context — explain why, not just how
- ❌ Assuming reader knowledge — build concepts progressively
