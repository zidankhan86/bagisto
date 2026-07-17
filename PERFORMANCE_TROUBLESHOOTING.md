# Bagisto Performance Troubleshooting Guide

## Critical Issues Found

### 1. `dd(1)` Debug Statement in Visitor.php (CRITICAL BUG)

**File:** `packages/Webkul/Core/src/Visitor.php` (Line 20)

```php
public function visit(?Model $model = null)
{
    foreach ($this->except as $path) {
        if ($this->request->is($path)) {
            dd(1);  // <-- THIS IS A DEBUG STATEMENT LEFT IN PRODUCTION CODE
            return;
        }
    }
    UpdateCreateVisitIndex::dispatch($model, $this->prepareLog());
}
```

**Impact:** When a request path matches an "except" pattern (e.g., 'login', 'register'), instead of gracefully returning, the `dd(1)` statement halts execution and dumps "1" to the screen. This causes the page to crash with just "1" displayed.

**Fix:** Remove the `dd(1)` line.

---

### 2. Visitor Tracking on Every Page Load

**File:** `packages/Webkul/Shop/src/Http/Controllers/HomeController.php` (Line 31)

```php
public function index()
{
    visitor()->visit();  // Dispatches a job on EVERY page load
    ...
}
```

**Impact:** Every home page load dispatches the `UpdateCreateVisitIndex` job, which:
- Queries the database to prepare log data
- Dispatches a queued job (if using sync driver, this runs synchronously)
- Calls `core()->getCurrentChannel()` which queries the database

**Fix Options:**
- Disable visitor tracking if not needed
- Use a proper queue driver (database/redis) instead of sync
- Add rate limiting to visitor tracking

---

### 3. Response Caching Disabled

**File:** `.env` (Line 35)

```
RESPONSE_CACHE_ENABLED=false
```

**Impact:** Every page load is a full dynamic render. No pages are cached, causing repeated database queries and view rendering on every request.

**Fix:** Enable response caching for public pages:
```
RESPONSE_CACHE_ENABLED=true
```

---

### 4. File-Based Cache on Windows/XAMPP (SLOW)

**File:** `.env` (Line 26-27)

```
CACHE_DRIVER=file
SESSION_DRIVER=file
```

**Impact:** File-based cache and sessions on Windows (especially XAMPP) are extremely slow due to:
- High I/O latency on Windows filesystem
- No opcode caching for file reads
- File locking overhead for concurrent requests

**Fix:** Switch to faster drivers:
```
CACHE_DRIVER=array     # For development
SESSION_DRIVER=array   # For development
```
Or use Redis/MySQL for production.

---

### 5. Debug Mode Enabled (Adds Overhead)

**File:** `.env` (Line 4)

```
APP_DEBUG=true
```

**Impact:** Laravel's debug mode:
- Logs all queries (if Debugbar is active)
- Displays detailed error pages
- Disables some optimizations
- Adds overhead to every request

**Fix:** Disable in production:
```
APP_DEBUG=false
```

---

### 6. CacheResponse Middleware Queries on Every Request

**File:** `packages/Webkul/Shop/src/Http/Middleware/CacheResponse.php`

**Impact:** Even when response caching is disabled, this middleware still runs and queries:
- `SearchTermRepository` on search pages
- `URLRewriteRepository` on product/category pages (3 separate queries)
- `URLRewriteRepository` on CMS pages

Each query hits the database, adding latency to every page load.

**Fix:** The middleware should check if caching is enabled before running these queries.

---

### 7. `core()->getCurrentChannel()` Called Multiple Times

**File:** `packages/Webkul/Core/src/Core.php` (Line 133-150)

**Impact:** `getCurrentChannel()` queries the database on first call. It's called multiple times per request from:
- Visitor tracking
- Theme customization
- URL rewrite checks
- Product/category resolution

**Fix:** The method already caches in `$this->currentChannel` after first call, but ensure it's called early in the request lifecycle to avoid repeated DB misses.

---

## Quick Performance Checklist

### Immediate Fixes (5 minutes)

- [ ] **Remove `dd(1)`** from `packages/Webkul/Core/src/Visitor.php` line 20
- [ ] **Disable visitor tracking** temporarily by commenting out `visitor()->visit()` in `HomeController.php`
- [ ] **Set `APP_DEBUG=false`** in `.env`
- [ ] **Clear config cache**: `php artisan config:cache`

### Development Environment Fixes

- [ ] **Switch to array cache**: Set `CACHE_DRIVER=array` and `SESSION_DRIVER=array` in `.env`
- [ ] **Use database queue**: Set `QUEUE_DRIVER=database` and run `php artisan queue:table` + migrate
- [ ] **Enable response cache**: Set `RESPONSE_CACHE_ENABLED=true` and run `php artisan responsecache:clear`

### Production Environment Fixes

- [ ] **Use Redis** for cache, sessions, and queues
- [ ] **Enable OPCache** in PHP
- [ ] **Use a CDN** for static assets
- [ ] **Enable HTTP/2** on the web server
- [ ] **Use PHP-FPM** instead of mod_php
- [ ] **Enable gzip compression** on the web server

### Database Optimizations

- [ ] **Add indexes** to frequently queried columns:
  - `product_flat.url_key`
  - `product_flat.sku`
  - `categories._lft`, `categories._rgt`
  - `url_rewrites.request_path`
- [ ] **Run `ANALYZE TABLE`** on all tables to update index statistics
- [ ] **Check slow query log** for problematic queries

### Laravel Optimizations

- [ ] **Run `php artisan optimize`** to cache routes, config, and events
- [ ] **Run `php artisan view:cache`** to cache compiled Blade templates
- [ ] **Run `composer dump-autoload -o`** for optimized autoloading
- [ ] **Use eager loading** for relationships in repositories

---

## How to Diagnose Performance Issues

### 1. Check Laravel Debugbar
If Debugbar is installed, check:
- **Queries tab**: Number of queries per page (should be < 50)
- **Time tab**: Total page load time (should be < 500ms)
- **Memory tab**: Memory usage (should be < 50MB)

### 2. Check Database Queries
```sql
-- Check slow queries
SHOW FULL PROCESSLIST;

-- Check table sizes
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'vagisto'
ORDER BY (data_length + index_length) DESC;
```

### 3. Check PHP Configuration
- `memory_limit`: Should be at least 256M
- `max_execution_time`: Should be at least 300
- `opcache.enable`: Should be 1 in production
- `opcache.memory_consumption`: Should be at least 128

### 4. Check XAMPP Configuration
- **Apache**: Enable `mod_deflate` for compression
- **MySQL**: Increase `innodb_buffer_pool_size` to at least 256M
- **PHP**: Increase `max_execution_time` and `memory_limit`

---

## Common XAMPP Performance Issues

1. **MySQL is slow on Windows** - Increase `innodb_buffer_pool_size` in `my.ini`
2. **File I/O is slow** - Use `array` driver for cache/sessions in development
3. **No opcode caching** - Enable `opcache` in `php.ini`
4. **Apache is single-threaded** - Consider using Laravel's built-in server: `php artisan serve`