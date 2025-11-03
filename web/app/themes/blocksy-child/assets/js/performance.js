/**
 * Performance optimizations
 */

(function() {
  'use strict';

  // Mark body as loaded when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      document.body.classList.add('loaded');
    });
  } else {
    document.body.classList.add('loaded');
  }

  // Lazy load images with Intersection Observer
  if ('IntersectionObserver' in window) {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    const imageObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
          }
          img.classList.add('loaded');
          imageObserver.unobserve(img);
        }
      });
    });

    lazyImages.forEach(function(img) {
      imageObserver.observe(img);
    });
  }

  // Preconnect to external domains
  function addPreconnect(url) {
    const link = document.createElement('link');
    link.rel = 'preconnect';
    link.href = url;
    link.crossOrigin = 'anonymous';
    document.head.appendChild(link);
  }

  // Add preconnects for common CDNs if needed
  const hasGoogleFonts = document.querySelector('link[href*="fonts.googleapis.com"]');
  if (hasGoogleFonts) {
    addPreconnect('https://fonts.googleapis.com');
    addPreconnect('https://fonts.gstatic.com');
  }

  // Report Web Vitals to console in development
  if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    if ('PerformanceObserver' in window) {
      // CLS - Cumulative Layout Shift
      const clsObserver = new PerformanceObserver(function(list) {
        for (const entry of list.getEntries()) {
          if (!entry.hadRecentInput) {
            console.log('CLS:', entry.value);
          }
        }
      });
      clsObserver.observe({ type: 'layout-shift', buffered: true });

      // LCP - Largest Contentful Paint
      const lcpObserver = new PerformanceObserver(function(list) {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        console.log('LCP:', lastEntry.renderTime || lastEntry.loadTime);
      });
      lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });

      // FID - First Input Delay
      const fidObserver = new PerformanceObserver(function(list) {
        for (const entry of list.getEntries()) {
          console.log('FID:', entry.processingStart - entry.startTime);
        }
      });
      fidObserver.observe({ type: 'first-input', buffered: true });
    }
  }
})();
