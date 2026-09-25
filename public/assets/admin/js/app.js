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

    // 11. CASCADING LOCATION DROPDOWNS HELPER
    window.initLocationCascade = function(options) {
        options = $.extend({
            country: '#country_id',
            state: '#state_id',
            city: '#city_id',
            stateUrl: '/admin/ajax/states/',
            cityUrl: '/admin/ajax/cities/',
            selectedState: null,
            selectedCity: null,
            placeholderState: 'Select State / Division',
            placeholderCity: 'Select City'
        }, options);

        var $country = $(options.country);
        var $state = $(options.state);
        var $city = options.city ? $(options.city) : null;

        function loadStates(countryId, callback) {
            if (!countryId) {
                $state.empty().append('<option value="">' + options.placeholderState + '</option>').prop('disabled', true);
                if ($state.hasClass('select2-hidden-accessible')) {
                    $state.trigger('change.select2');
                }
                if ($city && $city.length) {
                    $city.empty().append('<option value="">' + options.placeholderCity + '</option>').prop('disabled', true);
                    if ($city.hasClass('select2-hidden-accessible')) {
                        $city.trigger('change.select2');
                    }
                }
                return;
            }

            var url = options.stateUrl + countryId;
            $state.prop('disabled', true);

            $.getJSON(url, function(data) {
                $state.empty().append('<option value="">' + options.placeholderState + '</option>');
                $.each(data, function(index, item) {
                    var selected = options.selectedState && options.selectedState == item.id ? ' selected' : '';
                    $state.append('<option value="' + item.id + '"' + selected + '>' + item.name + '</option>');
                });
                $state.prop('disabled', false);

                if ($state.hasClass('select2-hidden-accessible')) {
                    $state.trigger('change.select2');
                }

                if (options.selectedState) {
                    $state.val(options.selectedState);
                    if ($state.hasClass('select2-hidden-accessible')) {
                        $state.trigger('change.select2');
                    }
                }

                if (typeof callback === 'function') {
                    callback();
                } else if ($state.val()) {
                    $state.trigger('change');
                }
            });
        }

        function loadCities(stateId, callback) {
            if (!$city || !$city.length) return;

            if (!stateId) {
                $city.empty().append('<option value="">' + options.placeholderCity + '</option>').prop('disabled', true);
                if ($city.hasClass('select2-hidden-accessible')) {
                    $city.trigger('change.select2');
                }
                return;
            }

            var url = options.cityUrl + stateId;
            $city.prop('disabled', true);

            $.getJSON(url, function(data) {
                $city.empty().append('<option value="">' + options.placeholderCity + '</option>');
                $.each(data, function(index, item) {
                    var selected = options.selectedCity && options.selectedCity == item.id ? ' selected' : '';
                    $city.append('<option value="' + item.id + '"' + selected + '>' + item.name + '</option>');
                });
                $city.prop('disabled', false);

                if ($city.hasClass('select2-hidden-accessible')) {
                    $city.trigger('change.select2');
                }

                if (options.selectedCity) {
                    $city.val(options.selectedCity);
                    if ($city.hasClass('select2-hidden-accessible')) {
                        $city.trigger('change.select2');
                    }
                }

                if (typeof callback === 'function') {
                    callback();
                }
            });
        }

        $country.on('change', function() {
            var countryId = $(this).val();
            options.selectedState = null;
            options.selectedCity = null;
            loadStates(countryId);
        });

        $state.on('change', function() {
            var stateId = $(this).val();
            loadCities(stateId);
        });

        // Initialize if country is already selected on page load
        var initialCountry = $country.val() || $country.attr('data-selected');
        if (initialCountry) {
            options.selectedState = options.selectedState || $state.attr('data-selected');
            options.selectedCity = options.selectedCity || ($city ? $city.attr('data-selected') : null);
            loadStates(initialCountry, function() {
                var initialState = $state.val() || options.selectedState;
                if (initialState) {
                    loadCities(initialState);
                }
            });
        }
    };

    $.fn.locationCascade = function(opts) {
        return this.each(function() {
            var $container = $(this);
            var merged = $.extend({
                country: $container.find('[data-cascade="country"]'),
                state: $container.find('[data-cascade="state"]'),
                city: $container.find('[data-cascade="city"]')
            }, opts);
            window.initLocationCascade(merged);
        });
    };

    // Auto-init for elements with data-location-cascade container
    $('[data-location-cascade]').locationCascade();
});

