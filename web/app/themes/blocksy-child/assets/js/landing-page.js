/**
 * Landing Page Interactive Features
 * Handles smooth scrolling, sticky CTA, FAQ accordion, and anchor navigation
 */

(function() {
    'use strict';

    // Configuration
    const config = {
        smoothScrollDuration: 300,
        stickyCtaOffset: 500,
        debounceDelay: 100,
    };

    // Utility: Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Feature: Smooth scroll for anchor links
    function initSmoothScroll() {
        document.querySelectorAll('[data-scroll]').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        // Use native smooth scrolling if available
                        if ('scrollIntoView' in target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } else {
                            target.scrollIntoView();
                        }
                        // Update URL hash
                        window.history.pushState(null, null, href);
                    }
                }
            });
        });
    }

    // Feature: FAQ Accordion
    function initFAQAccordion() {
        const faqQuestions = document.querySelectorAll('.faq-question');

        if (faqQuestions.length === 0) return;

        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const targetId = this.getAttribute('data-toggle');
                const answer = document.getElementById(targetId);

                if (!answer) return;

                const isOpen = answer.style.display !== 'none';

                // Close all other answers
                document.querySelectorAll('.faq-answer').forEach(item => {
                    item.style.display = 'none';
                });

                // Remove active class from all questions
                document.querySelectorAll('.faq-question').forEach(q => {
                    q.classList.remove('active');
                });

                // Toggle current answer
                if (!isOpen) {
                    answer.style.display = 'block';
                    this.classList.add('active');
                }
            });

            // Keyboard accessibility
            question.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
    }

    // Feature: Sticky CTA visibility
    function initStickyCTA() {
        const stickyCta = document.getElementById('sticky-cta');
        const faqSection = document.getElementById('faq');

        if (!stickyCta || !faqSection) return;

        const updateStickyCta = debounce(function() {
            const faqRect = faqSection.getBoundingClientRect();
            const shouldShow = faqRect.top < window.innerHeight;

            if (shouldShow && !stickyCta.classList.contains('active')) {
                stickyCta.classList.add('active');
            } else if (!shouldShow && stickyCta.classList.contains('active')) {
                stickyCta.classList.remove('active');
            }
        }, config.debounceDelay);

        window.addEventListener('scroll', updateStickyCta, { passive: true });
        updateStickyCta(); // Initial check
    }

    // Feature: Anchor navigation visibility and active state
    function initAnchorNav() {
        const anchorNav = document.getElementById('anchor-nav');

        if (!anchorNav) return;

        const sections = ['hero', 'products', 'urgency', 'neighborhoods', 'reviews', 'faq', 'shop'];

        // Show/hide anchor nav based on hero visibility
        const updateAnchorNavVisibility = debounce(function() {
            const heroSection = document.getElementById('hero');
            if (!heroSection) return;

            const heroRect = heroSection.getBoundingClientRect();
            const showNav = heroRect.bottom < 0;

            if (showNav && !anchorNav.classList.contains('visible')) {
                anchorNav.classList.add('visible');
            } else if (!showNav && anchorNav.classList.contains('visible')) {
                anchorNav.classList.remove('visible');
            }
        }, config.debounceDelay);

        // Update active anchor link based on scroll position
        const updateActiveAnchorLink = debounce(function() {
            let currentSection = '';
            let maxIntersection = 0;

            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (!section) return;

                const rect = section.getBoundingClientRect();
                const viewportHeight = window.innerHeight;

                // Calculate how much of the section is in viewport
                const top = Math.max(0, rect.top);
                const bottom = Math.min(viewportHeight, rect.bottom);
                const intersection = bottom - top;

                if (intersection > maxIntersection && intersection > 0) {
                    maxIntersection = intersection;
                    currentSection = sectionId;
                }
            });

            // Update active state
            document.querySelectorAll('.anchor-nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${currentSection}`) {
                    link.classList.add('active');
                }
            });
        }, config.debounceDelay);

        window.addEventListener('scroll', updateAnchorNavVisibility, { passive: true });
        window.addEventListener('scroll', updateActiveAnchorLink, { passive: true });

        updateAnchorNavVisibility(); // Initial check
        updateActiveAnchorLink(); // Initial check
    }

    // Feature: Intersection Observer for lazy loading animations (optional)
    function initIntersectionObserver() {
        if (!window.IntersectionObserver) return;

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all section elements
        document.querySelectorAll('.landing-section').forEach(section => {
            observer.observe(section);
        });
    }

    // Feature: Scroll progress indicator (optional)
    function initScrollProgress() {
        const scrollProgressBar = document.querySelector('.scroll-progress-bar');
        if (!scrollProgressBar) return;

        const updateScrollProgress = debounce(function() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;

            scrollProgressBar.style.width = scrollPercent + '%';
        }, config.debounceDelay);

        window.addEventListener('scroll', updateScrollProgress, { passive: true });
    }

    // Feature: Handle focus management for accessibility
    function initAccessibility() {
        // Ensure all interactive elements are keyboard accessible
        document.querySelectorAll('a[href^="#"], button, .faq-question').forEach(element => {
            if (!element.hasAttribute('tabindex')) {
                element.setAttribute('tabindex', '0');
            }
        });

        // Handle escape key to close FAQ
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close all open FAQ items
                document.querySelectorAll('.faq-answer').forEach(item => {
                    item.style.display = 'none';
                });
                document.querySelectorAll('.faq-question').forEach(q => {
                    q.classList.remove('active');
                });
            }
        });
    }

    // Feature: Add scroll behavior class
    function initScrollBehaviorClass() {
        let scrollTimeout;

        const updateScrollClass = debounce(function() {
            clearTimeout(scrollTimeout);
            document.body.classList.add('scrolling');
            scrollTimeout = setTimeout(function() {
                document.body.classList.remove('scrolling');
            }, 1000);
        }, config.debounceDelay);

        window.addEventListener('scroll', updateScrollClass, { passive: true });
    }

    // Initialize all features when DOM is ready
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        initSmoothScroll();
        initFAQAccordion();
        initStickyCTA();
        initAnchorNav();
        initIntersectionObserver();
        initScrollProgress();
        initAccessibility();
        initScrollBehaviorClass();

        // Log initialization in development
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('Landing page features initialized');
        }
    }

    // Initialize
    init();

    // Expose API for external use if needed
    window.LandingPage = {
        scrollToSection: function(sectionId) {
            const target = document.getElementById(sectionId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
        toggleFAQ: function(faqId) {
            const question = document.querySelector(`[data-toggle="${faqId}"]`);
            if (question) {
                question.click();
            }
        }
    };

})();
