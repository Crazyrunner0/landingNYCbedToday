# Redis Cache Setup & Health Checks

This document describes the Redis cache integration for WordPress object caching and performance optimization.

## Overview

Redis is an in-memory data store used to cache WordPress objects, reducing database queries and improving response times. This mu-plugin (`redis-cache.php`) handles connection management, health checks, and diagnostics.

## Configuration

### Environment Variables

Configure Redis connection in `.env`:

```bash
# Redis cache configuration
REDIS_HOST=redis          # Redis server hostname (default: redis)
REDIS_PORT=6379          # Redis server port (default: 6379)
REDIS_PASSWORD=          # Redis password (leave empty if no auth required)
REDIS_CACHE_DB=0         # Redis database number (default: 0)
```

### Docker Setup

Redis is already configured in `docker-compose.yml`:

```yaml
redis:
  image: redis:7-alpine
  ports:
    - "6379:6379"
  volumes:
    - redis-data:/data
  environment:
    - REDIS_PASSWORD=${REDIS_PASSWORD}
```

### Manual Connection Test

```bash
# Enter Redis container
docker-compose exec redis redis-cli

# Test connection (should return PONG)
PING

# Check database info
INFO stats

# Exit redis-cli
exit
```

## Health Checks

### WP-CLI Commands

All commands are available via `wp redis` namespace:

```bash
# Check Redis health status
wp redis health-check

# Get Redis statistics
wp redis stats
```

### Check Redis Health Status

```bash
make wp CMD='redis health-check'
```

**Expected output:**
```
Success: Redis cache is operational
Redis Version: 7.0.0
Used Memory: 2.5M
Connected Clients: 3
```

**If unhealthy:**
```
Error: Redis connection failed: Connection refused
```

### Get Redis Statistics

```bash
make wp CMD='redis stats'
```

**Example output:**
```
=== Redis Statistics ===
Version: 7.0.0
Used Memory: 2.5M
Peak Memory: 5.1M
Connected Clients: 3
Total Commands: 125430
Uptime: 3600 seconds
```

### Verify Cache is Working

```bash
# Set a test value
make wp CMD='transient set test_key "test_value" 3600'

# Retrieve the test value
make wp CMD='transient get test_key'

# Output should show: test_value

# Delete test value
make wp CMD='transient delete test_key'
```

## Monitoring & Debugging

### Check Cache Hit Rate

Monitor via Redis directly:

```bash
# Enter Redis CLI
docker-compose exec redis redis-cli

# Get stats
INFO stats

# Look for:
# - keyspace_hits: Number of successful reads
# - keyspace_misses: Number of failed reads
# - Hit rate = hits / (hits + misses)

# Example: 19000 hits, 1000 misses = 95% hit rate
```

### View Cached Keys

```bash
# Enter Redis CLI
docker-compose exec redis redis-cli

# Count keys
DBSIZE

# View all keys (careful in production!)
KEYS *

# View specific key type
KEYS wp_*

# Get key details
OBJECT IDLETIME wp_option:home
OBJECT REFCOUNT wp_option:home
OBJECT ENCODING wp_option:home
```

### Memory Usage Analysis

```bash
# Enter Redis CLI
docker-compose exec redis redis-cli

# Get memory stats
INFO memory

# Key information:
# - used_memory: Current memory usage
# - used_memory_human: Human readable format
# - used_memory_peak: Peak memory usage
# - maxmemory: Memory limit (if set)
# - eviction_policy: What happens when full
```

### Monitor in Real-Time

```bash
# Enter Redis CLI
docker-compose exec redis redis-cli

# Monitor all commands
MONITOR

# (Press Ctrl+C to exit)
```

## Troubleshooting

### Redis Not Connecting

**Symptom:** WordPress works but shows "Redis not available" notice.

**Diagnosis:**
```bash
# Check if Redis container is running
docker-compose ps | grep redis

# Should show redis container as "Up"

# If not running, check logs
docker-compose logs redis

# Try to connect manually
docker-compose exec redis redis-cli ping
```

**Fix:**
```bash
# Restart Redis
docker-compose restart redis

# Wait a moment then check
docker-compose exec redis redis-cli ping

# Should return: PONG
```

### High Memory Usage

**Symptom:** Redis memory keeps growing.

**Diagnosis:**
```bash
# Check memory usage
make wp CMD='redis stats' | grep Memory

# Check eviction policy
docker-compose exec redis redis-cli CONFIG GET maxmemory-policy
```

**Fix:**
1. Set memory limit:
```bash
docker-compose exec redis redis-cli CONFIG SET maxmemory 256mb
```

2. Set eviction policy (remove least recently used):
```bash
docker-compose exec redis redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

3. Or flush old cache:
```bash
# Flush entire cache
make wp CMD='cache flush'

# Or flush only expired keys
docker-compose exec redis redis-cli SCAN 0
```

### Cache Not Being Used

**Symptom:** WordPress queries increasing, but cache doesn't seem to help.

**Diagnosis:**
```bash
# Check if mu-plugin is loaded
make wp CMD='plugin list --mu-plugins'

# Should show redis-cache plugin

# Check if WP_REDIS_ENABLED
make wp CMD='eval "var_dump( defined( \"WP_REDIS_ENABLED\" ) ? WP_REDIS_ENABLED : \"not_defined\" );"'
```

**Fix:**
1. Verify mu-plugin is in correct location: `web/app/mu-plugins/redis-cache.php`
2. Check PHP Redis extension is installed: `php -m | grep redis`
3. Verify environment variables: `cat .env | grep REDIS`
4. Restart PHP: `docker-compose restart php-fpm`

## Performance Verification

### Before/After Benchmark

1. **Baseline (without Redis):**
```bash
# Disable Redis by moving mu-plugin
mv web/app/mu-plugins/redis-cache.php web/app/mu-plugins/redis-cache.php.bak

# Clear cache
make wp CMD='cache flush'

# Run Lighthouse or measure metrics
# Note response times and database queries
```

2. **With Redis:**
```bash
# Re-enable Redis
mv web/app/mu-plugins/redis-cache.php.bak web/app/mu-plugins/redis-cache.php

# Restart containers
docker-compose restart

# Wait for cache to warm up
# Run Lighthouse or measure metrics
# Compare improvements
```

### Expected Performance Improvements

With Redis cache enabled:
- **Page load time**: 40-60% faster (with warm cache)
- **Database queries**: 70-90% reduction (repeated visits)
- **Server CPU**: 30-50% reduction (less query processing)
- **Memory**: Higher peak (Redis) but overall reduced database memory
- **Concurrent users**: 2-3x more users with same resources

## Cache Invalidation

### Automatic Invalidation

WordPress automatically clears relevant cache when:
- Post/page is published or updated
- Menus are modified
- Settings are changed
- Plugins are activated/deactivated

### Manual Cache Flushing

```bash
# Flush entire object cache
make wp CMD='cache flush'

# Or via Redis CLI
docker-compose exec redis redis-cli FLUSHDB

# FLUSHALL clears all databases (be careful!)
docker-compose exec redis redis-cli FLUSHALL
```

### Selective Flushing

```bash
# Flush specific cache groups
make wp CMD='cache flush --group=posts'
make wp CMD='cache flush --group=options'
make wp CMD='cache flush --group=terms'
```

## Production Considerations

### Security

1. **Enable authentication:**
```bash
# Set password in .env
REDIS_PASSWORD=your_secure_password

# Update Redis config
docker-compose.yml should use password
```

2. **Network access:**
```bash
# Ensure Redis only accessible from PHP container
docker-compose.yml should NOT expose port unless needed

# Verify:
docker-compose ps | grep redis
# Should NOT show ports like 6379:6379
```

3. **Data persistence:**
```bash
# Redis data is volatile by default
# Enable persistence if needed:
docker-compose.yml: volumes to persist data
```

### Monitoring

1. **Set up alerts** for:
   - Redis connection failures
   - Memory usage > 80% of limit
   - Eviction rate increasing

2. **Regular checks:**
```bash
# Weekly health check
make wp CMD='redis health-check'

# Monitor cache hit rate
docker-compose exec redis redis-cli INFO stats | grep keyspace
```

### Backup Strategy

```bash
# Redis data location (in container)
# /data/dump.rdb (if persistence enabled)

# Backup Redis data
docker-compose exec redis redis-cli BGSAVE

# Verify backup
docker-compose exec redis ls -lah /data/
```

## Integration with Other Caching

### Page Caching
- Configure separate from Redis object cache
- Use different cache drivers (HTTP caching headers, Varnish, etc.)
- Redis handles object cache (database query caching)

### Full Page Caching
```bash
# WordPress handles this separately
# Redis caches database queries within page
# Full page cache would cache entire HTML
# See wp-config or .htaccess for page caching setup
```

## Customization

### Add Custom Cache Keys

```php
// Set a cache value
wp_cache_set('my_key', 'my_value', 'my_group', 3600);

// Get cached value
$value = wp_cache_get('my_key', 'my_group');

// Delete from cache
wp_cache_delete('my_key', 'my_group');
```

### Cache Warming

Create a scheduled job to pre-populate cache:

```php
// In your plugin or theme
function warm_cache() {
    $posts = get_posts(['numberposts' => 100]);
    foreach ($posts as $post) {
        setup_postdata($post);
        wp_cache_set('post_' . $post->ID, $post);
    }
    wp_reset_postdata();
}
```

## Maintenance Tasks

### Monthly
- Check cache hit rate
- Monitor memory usage
- Review error logs

### Quarterly
- Performance audit
- Cache strategy review
- Update Redis if needed

### Annually
- Full system audit
- Capacity planning
- Security review

## Getting Help

### Check Logs

```bash
# PHP error logs
tail -f storage/logs/php-fpm.log

# WordPress debug log
tail -f web/wp-content/debug.log

# Redis logs
docker-compose logs -f redis
```

### Common Issues

| Issue | Solution |
|-------|----------|
| Connection refused | Check Redis container running and port exposed |
| Permission denied | Check Redis password if auth enabled |
| Memory exceeded | Set memory limit and eviction policy |
| Cache not working | Verify mu-plugin loaded and PHP Redis extension |
| High latency | Check Redis memory and eviction rate |

## References

- [Redis Documentation](https://redis.io/documentation)
- [WordPress Cache API](https://developer.wordpress.org/plugins/caching/object-cache/)
- [PHP Redis Extension](https://github.com/phpredis/phpredis)
- [Docker Redis](https://hub.docker.com/_/redis)
