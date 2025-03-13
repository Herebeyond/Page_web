            </div>
            <a href="#" id="scrollToTop" title="Back to top">&#8679;To the top</a> <!-- Arrow pointing up -->
        </div>
        <script>
            // JavaScript to scroll to top
            document.getElementById('scrollToTop').addEventListener('click', function(event) {
                event.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        </script>
    </body>
</html>