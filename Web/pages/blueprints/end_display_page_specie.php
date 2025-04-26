
                        
    </div> <!-- End of mainTextList -->

    <!-- Display pagination links -->
    <div id="pagination">
        <?php if ($page > 1): ?> <!-- If page is greater than 1, display the first page link -->
            <a href="?page=1">&lt;&lt;</a>
        <?php endif; ?>

        <?php if ($page > 1): ?> <!-- If page is greater than 1, display the previous link -->
            <a href="?page=<?php echo $page - 1; ?>">&lt;</a>
        <?php endif; ?>

        <?php
        // Define the range of pages to display
        $range = 2; // Number of pages to show before and after the current page
        $start = max(1, $page - $range); // Start of the range
        $end = min($totalPages, $page + $range); // End of the range

        if ($start > 1): ?>
            <a href="?page=1">1</a>
            <?php if ($start > 2): ?>
                <span>...</span> <!-- Ellipsis for skipped pages -->
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?> <!-- Display the page numbers within the range -->
            <a href="?page=<?php echo $i; ?>"<?php if ($i == $page) {echo ' class="active"';} ?>><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?>
                <span>...</span> <!-- Ellipsis for skipped pages -->
            <?php endif; ?>
            <a href="?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?> <!-- If page is less than the total pages, display the next link -->
            <a href="?page=<?php echo $page + 1; ?>">&gt;</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?> <!-- If page is less than the total pages, display the last page link -->
            <a href="?page=<?php echo $totalPages; ?>">&gt;&gt;</a>
        <?php endif; ?>
    </div>


    <!-- Add a form to jump to a specific page -->
    <div id="jumpToPage">
        <form method="GET" action="">
            <label for="pageInput">Go to page:</label>
            <input type="number" id="pageInput" name="page" min="1" max="<?php echo $totalPages; ?>" value="<?php echo $page; ?>" required>
            <button type="submit">Go</button>
        </form>
    </div>
</div>


<?php
require_once "./blueprints/gl_ap_end.php";
?>