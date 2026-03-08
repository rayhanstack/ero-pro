'use strict';

$(document).ready(function() {
    
    // 1. SIDEBAR TOGGLE
    $('.sidebar-toggle').on('click', function(e) {
        e.preventDefault();
        
        if ($(window).width() < 992) {
            // Mobile: slide sidebar, show overlay
            $('.sidebar').toggleClass('show');
            $('.sidebar-overlay').toggleClass('show');
        } else {
            // Desktop/Tablet: toggle collapsed class
            $('.sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('collapsed');
        }
    });

    // Close sidebar on overlay click
    $('.sidebar-overlay').on('click', function() {
        $('.sidebar').removeClass('show');
        $(this).removeClass('show');
    });

    // 2. ACTIVE MENU ITEM
    var path = window.location.pathname;
    var page = path.split("/").pop();
    if (page === "") page = "index.html"; // default link
    
    $('.sidebar-nav__item').each(function() {
        var href = $(this).attr('href');
        if (href === page) {
            $(this).addClass('active');
        }
    });

    // 3. COUNT-UP ANIMATION
    $('[data-countup]').each(function() {
        var $this = $(this);
        var countTo = parseInt($this.attr('data-countup'), 10);
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        },
        {
            duration: 1500,
            easing: 'swing',
            step: function() {
                var val = Math.floor(this.countNum);
                var isCurrency = $this.text().indexOf('$') !== -1;
                $this.text((isCurrency ? '$' : '') + val.toLocaleString());
            },
            complete: function() {
                var val = Math.floor(this.countNum);
                var isCurrency = $this.text().indexOf('$') !== -1;
                $this.text((isCurrency ? '$' : '') + countTo.toLocaleString());
            }
        });
    });

    // 5. NOTIFICATION DROPDOWN (handled largely by Bootstrap data attributes, closing managed by BS as well)
    // 6. SEARCH EXPAND (managed by CSS transitions on focus)

    // 7. TOAST NOTIFICATION
    if ($('#welcomeToast').length && window.location.pathname.includes('index.html') || window.location.pathname.endsWith('/')) {
        setTimeout(function() {
            var toastEl = document.getElementById('welcomeToast');
            var toast = new bootstrap.Toast(toastEl, {
                delay: 5000
            });
            toast.show();
        }, 1500);
    }

    // 8. KANBAN DRAG-DROP
    if ($('.kanban-tasks').length) {
        var draggedCard = null;

        $('.task-card').attr('draggable', 'true');

        $('.task-card').on('dragstart', function(e) {
            draggedCard = this;
            setTimeout(() => $(this).css('opacity', '0.5'), 0);
        });

        $('.task-card').on('dragend', function() {
            setTimeout(() => {
                $(this).css('opacity', '1');
                draggedCard = null;
            }, 0);
        });

        $('.kanban-tasks').on('dragover', function(e) {
            e.preventDefault();
        });

        $('.kanban-tasks').on('drop', function(e) {
            e.preventDefault();
            if (draggedCard) {
                $(this).append(draggedCard);
            }
        });
    }

    // 9. TABLE ROW HIGHLIGHT
    $('table tbody tr').hover(
        function() { $(this).addClass('bg-light'); },
        function() { $(this).removeClass('bg-light'); }
    );

    // 10. FORM VALIDATION
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

});
