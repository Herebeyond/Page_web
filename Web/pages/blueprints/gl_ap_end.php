                <button id="scrollToTop" class="scroll-to-top-btn" title="Return to top" aria-label="Scroll to top">
                    <svg class="scroll-arrow" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 6L4 14L6.4 16.4L12 10.8L17.6 16.4L20 14L12 6Z" fill="currentColor"/>
                    </svg>
                    <span class="scroll-text">TOP</span>
                </button>
            </div>
        </div>
        <script>
            // JavaScript to scroll to top with enhanced functionality
            const scrollToTopBtn = document.getElementById('scrollToTop');
            
            // Show/hide button based on scroll position
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.add('visible');
                } else {
                    scrollToTopBtn.classList.remove('visible');
                }
            });
            
            // Smooth scroll to top
            scrollToTopBtn.addEventListener('click', function(event) {
                event.preventDefault();
                window.scrollTo({ 
                    top: 0, 
                    behavior: 'smooth' 
                });
            });
        </script>
    </body>
</html>

