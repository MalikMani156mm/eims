let currentSection = 'default';

function loadContent(section) {
    currentSection = section;
    const url = `content/${section}.php`;

    // Regular content load (no filters) - load HTML and execute inline scripts
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'html',
        success: function (response) {
            // Parse HTML and keep scripts
            const parsed = $.parseHTML(response, document, true);
            const $target = $("#content-area");
            $target.empty();

            parsed.forEach(function(node) {
                if (node && node.nodeName && node.nodeName.toLowerCase() === 'script') {
                    // Execute inline script content
                    const scriptText = node.text || node.textContent || node.innerHTML || '';
                    if (scriptText) {
                        $.globalEval(scriptText);
                    }
                } else if (node) {
                    $target.append(node);
                }
            });

            bindSectionLogic(section);
        },
        error: function () {
            console.error('Error loading content for:', section);
            Swal.fire('Error', 'An error occurred while loading content.', 'error');
        }
    });
}

function loadContentWithFilter(section, status) {
    currentSection = section;
    const url = `content/${section}.php`;

    // Load content with filter parameter (execute inline scripts)
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'html',
        success: function (response) {
            const parsed = $.parseHTML(response, document, true);
            const $target = $("#content-area");
            $target.empty();

            parsed.forEach(function(node) {
                if (node && node.nodeName && node.nodeName.toLowerCase() === 'script') {
                    const scriptText = node.text || node.textContent || node.innerHTML || '';
                    if (scriptText) {
                        $.globalEval(scriptText);
                    }
                } else if (node) {
                    $target.append(node);
                }
            });

            bindSectionLogic(section);

            // Apply the filter after content is loaded
            setTimeout(function() {
                if (status) {
                    // Show the filter section
                    $('#filterToggle').prop('checked', true);
                    $('#filterSection').addClass('show');
                    
                    // Set the status filter value
                    $('#filterStatus').val(status);
                    
                    // Trigger the filter
                    if (typeof applyFilters === 'function') {
                        applyFilters();
                    }
                }
            }, 100);
        },
        error: function () {
            console.error('Error loading content for:', section);
            Swal.fire('Error', 'An error occurred while loading content.', 'error');
        }
    });
}

function bindSectionLogic(section) {
    // Completely unbind all previous handlers from content area to prevent conflicts
    $('#content-area').off('submit');
    $('#content-area').off('change');
    $('#content-area').off('click');

    // Unbind Select2 handlers
    $('#content-area select').each(function () {
        if ($(this).data('select2')) {
            $(this).select2('destroy');
        }
    });

    switch (section) {

    }

}

// Use jQuery's ready to ensure DOM is loaded and jQuery is available
$(document).ready(function () {
    // Initial load of the default content
    loadContent('default');

    // Set an interval to reload the default content every 3 seconds (3000 milliseconds)
    setInterval(function () {
        if (currentSection === 'default') {
            loadContent('default');
        }
    }, 120000);
});

