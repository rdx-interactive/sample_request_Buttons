# Security Notes

Version: 1.0.19

This plugin is intentionally small and does not make remote requests or execute dynamic code. Product PDFs are selected from the WordPress media library and validated as PDF attachments before display. Sample requests are protected with:

- WordPress nonce verification on frontend cart submissions.
- A hidden honeypot field to stop simple automated spam submissions.
- A per-user or per-IP rate limit of 8 sample requests per product per 10 minutes.
- Capability checks before saving the product admin checkbox.
- PDF MIME validation before saving and rendering Product Info downloads.
- Sanitized request input and escaped frontend/admin output.
- A hidden zero-cost WooCommerce placeholder product so sample requests do not alter source product inventory or price.

Recommended site-level hardening still applies: keep WordPress, WooCommerce, Elementor, themes, and plugins updated; use a reputable malware scanner or WAF; and keep regular backups.
