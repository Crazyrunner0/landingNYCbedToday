# Changelog

All notable changes to the NYC Bed Today Same-Day Logistics Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-11-03

### Added
- Initial release of NYC Bed Today Same-Day Logistics Plugin
- ZIP code whitelist management with admin interface
- Seeded 100+ default NYC ZIP codes (Manhattan, Brooklyn, Queens, Bronx, Staten Island)
- 2-hour delivery time slot generator with configurable parameters
- Configurable order cut-off time for same-day delivery
- Slot capacity management (configurable maximum deliveries per slot)
- Blackout dates configuration for holidays and special days
- REST API endpoints:
  - POST `/wp-json/nycbedtoday-logistics/v1/check-zip` - Validate ZIP code
  - GET `/wp-json/nycbedtoday-logistics/v1/available-slots` - Get available time slots
  - POST `/wp-json/nycbedtoday-logistics/v1/reserve-slot` - Reserve a delivery slot
- WordPress shortcodes:
  - `[nycbt_check_zip]` - ZIP code availability checker
  - `[nycbt_available_slots]` - Display available delivery slots
- Gutenberg blocks for ZIP checking and slot display
- WooCommerce checkout integration:
  - Automatic slot selector display after shipping ZIP entry
  - Real-time slot availability checking
  - Slot reservation on order placement
  - Delivery information in order meta
  - Delivery details in customer and admin emails
  - Delivery information on thank you page
  - Delivery details in admin order view
- Admin settings page under Settings → Same-day Logistics with three tabs:
  - Settings: Configure time slots, capacity, cut-off time, and blackout dates
  - ZIP Codes: Manage whitelisted ZIP codes
  - Reservations: View all slot reservations
- Database table for storing slot reservations with order tracking
- Automatic reservation status updates based on order status changes
- Comprehensive unit tests for:
  - ZIP code management
  - Slot generation
  - Slot reservations
  - Capacity limits
- Full documentation:
  - README.md with installation and configuration instructions
  - EXAMPLES.md with code examples and usage patterns
  - PHPUnit configuration for testing
- Frontend assets:
  - Responsive CSS for all user-facing components
  - AJAX-powered JavaScript for seamless interaction
  - Admin interface styling
- Security features:
  - Nonce verification for AJAX requests
  - Capability checks for admin functions
  - Input sanitization and output escaping
  - Prepared SQL statements

### Technical Details
- Minimum WordPress version: 5.8
- Minimum PHP version: 7.4
- Compatible with WooCommerce 5.0+
- Uses WordPress REST API
- Uses WordPress Settings API
- Database schema with proper indexes for performance
- Bedrock-compatible plugin structure
