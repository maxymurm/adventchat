# AdventChat — Cross-Environment Testing Matrix

## WordPress & PHP Compatibility

| Test                        | WP 6.5 / PHP 8.1 | WP 6.6 / PHP 8.2 | WP 6.7 / PHP 8.3 |
|-----------------------------|:-----------------:|:-----------------:|:-----------------:|
| Plugin activates cleanly    | ☐                 | ☐                 | ☐                 |
| DB tables created           | ☐                 | ☐                 | ☐                 |
| Roles/caps registered       | ☐                 | ☐                 | ☐                 |
| Settings page renders       | ☐                 | ☐                 | ☐                 |
| Widget loads on frontend    | ☐                 | ☐                 | ☐                 |
| Console loads in admin      | ☐                 | ☐                 | ☐                 |
| Chat session works          | ☐                 | ☐                 | ☐                 |
| Plugin deactivates cleanly  | ☐                 | ☐                 | ☐                 |
| No PHP deprecation notices  | ☐                 | ☐                 | ☐                 |

## Plugin Compatibility

| Plugin                 | Version | Activates Together | Feature Works |
|------------------------|---------|--------------------|---------------|
| WooCommerce            | 9.x     | ☐                  | ☐ Cart context, customer ID |
| WPML                   | 4.x     | ☐                  | ☐ String translations |
| Polylang               | 3.x     | ☐                  | ☐ String translations |
| Elementor              | 3.x     | ☐                  | ☐ Widget registration |
| Twenty Twenty-Five     | 2.x     | ☐                  | ☐ Theme compatible |

## Browser Testing

| Browser            | Widget Renders | Chat Works | File Upload | Sound |
|--------------------|:--------------:|:----------:|:-----------:|:-----:|
| Chrome (latest)    | ☐              | ☐          | ☐           | ☐     |
| Firefox (latest)   | ☐              | ☐          | ☐           | ☐     |
| Safari (latest)    | ☐              | ☐          | ☐           | ☐     |
| Edge (latest)      | ☐              | ☐          | ☐           | ☐     |
| Mobile Chrome      | ☐              | ☐          | ☐           | ☐     |
| Mobile Safari      | ☐              | ☐          | ☐           | ☐     |

## Multisite

| Test                         | Pass |
|------------------------------|:----:|
| Network activate              | ☐    |
| Per-site settings             | ☐    |
| Per-site Firebase config      | ☐    |
| Subsite widget loads          | ☐    |

## Plugin Check (PCP)

| Check                         | Pass |
|-------------------------------|:----:|
| No direct DB queries w/o prepare | ☐ |
| All outputs escaped            | ☐    |
| All inputs sanitized           | ☐    |
| ABSPATH check on all files     | ☐    |
| Proper text domain usage       | ☐    |
| No PHP errors/warnings         | ☐    |
| Valid readme.txt format        | ☐    |
| Proper enqueue (no inline)     | ☐    |
| GPL-compatible license         | ☐    |

## Performance

| Metric                                | Target   | Actual |
|---------------------------------------|----------|--------|
| Widget JS (minified, gzip)            | < 30KB   |        |
| Widget CSS (minified)                 | < 10KB   |        |
| Console JS (minified, gzip)           | < 60KB   |        |
| Firebase CDN scripts (crossorigin)    | ✓        |        |
| Admin assets only on AC pages         | ✓        |        |
| Widget deferred loading               | ✓        |        |
| No render-blocking scripts            | ✓        |        |
