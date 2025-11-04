/**
 * Performance optimizations and Web Vitals monitoring
 */

(function () {
  'use strict';

  // Mark body as loaded when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      document.body.classList.add('loaded');
    });
  } else {
    document.body.classList.add('loaded');
  }

  // Lazy load images with Intersection Observer
  if ('IntersectionObserver' in window) {
    const lazyImages = document.querySelectorAll('img[loading="lazy"], picture img');

    const imageObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          const img = entry.target;
          
          // Handle picture elements
          const picture = img.closest('picture');
          if (picture) {
            const sources = picture.querySelectorAll('source');
            sources.forEach(function (source) {
              if (source.dataset.srcset) {
                source.srcset = source.dataset.srcset;
              }
            });
          }
          
          // Load the actual image
          if (img.dataset.src) {
            img.src = img.dataset.src;
          }
          
          // Remove loading attribute to trigger native lazy loading behavior
          if (img.hasAttribute('loading')) {
            img.removeAttribute('loading');
          }
          
          img.classList.add('loaded');
          imageObserver.unobserve(img);
        }
      });
    }, {
      rootMargin: '50px'
    });

    lazyImages.forEach(function (img) {
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

  // Dynamically preload critical resources
  if ('requestIdleCallback' in window) {
    requestIdleCallback(function () {
      // Preload above-the-fold images
      const criticalImages = document.querySelectorAll('[data-preload-image]');
      criticalImages.forEach(function (img) {
        const tempImg = new Image();
        tempImg.src = img.src;
      });
    });
  }

  // Report Web Vitals to console in development
  const isDevelopment = window.location.hostname === 'localhost' || 
                        window.location.hostname === '127.0.0.1' ||
                        window.location.hostname === 'staging.local';

  if (isDevelopment) {
    if ('PerformanceObserver' in window) {
      // CLS - Cumulative Layout Shift (target: <0.1)
      let clsValue = 0;
      const clsObserver = new PerformanceObserver(function (list) {
        for (const entry of list.getEntries()) {
          if (!entry.hadRecentInput) {
            clsValue += entry.value;
            console.log('CLS Update:', clsValue);
          }
        }
      });
      clsObserver.observe({ type: 'layout-shift', buffered: true });

      // LCP - Largest Contentful Paint (target: <2.5s)
      const lcpObserver = new PerformanceObserver(function (list) {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        const lcpValue = lastEntry.renderTime || lastEntry.loadTime;
        console.log('LCP:', lcpValue, 'ms', lcpValue <= 2500 ? '✓' : '✗');
      });
      lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });

      // FID - First Input Delay (now INP - Interaction to Next Paint)
      const fidObserver = new PerformanceObserver(function (list) {
        for (const entry of list.getEntries()) {
          const delay = entry.processingStart - entry.startTime;
          console.log('INP:', delay, 'ms');
        }
      });
      fidObserver.observe({ type: 'first-input', buffered: true });

      // TTFB - Time to First Byte
      if ('PerformanceNavigationTiming' in window) {
        const navTiming = performance.getEntriesByType('navigation')[0];
        if (navTiming) {
          const ttfb = navTiming.responseStart - navTiming.fetchStart;
          console.log('TTFB:', ttfb, 'ms');
        }
      }

      // FCP - First Contentful Paint
      const fcpObserver = new PerformanceObserver(function (list) {
        for (const entry of list.getEntries()) {
          console.log('FCP:', entry.startTime, 'ms');
        }
      });
      fcpObserver.observe({ type: 'paint', buffered: true });

      // Log all performance metrics when page fully loaded
      window.addEventListener('load', function () {
        console.log('Page fully loaded');
        
        // Log JavaScript bundle sizes (estimate from script tags)
        let jsSize = 0;
        document.querySelectorAll('script[src]').forEach(function (script) {
          // Note: actual sizes would require server-side collection
          console.log('Script:', script.src);
        });
      });
    }
  }

  // Performance monitoring data object for analytics integration
  window.performanceMetrics = {
    cls: 0,
    lcp: 0,
    fid: 0,
    ttfb: 0,
    fcp: 0
  };

  // Listen for visibility change to pause tracking when tab is not visible
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      console.log('Page hidden - pausing Web Vitals monitoring');
    } else {
      console.log('Page visible - resuming Web Vitals monitoring');
    }
  });
})();
