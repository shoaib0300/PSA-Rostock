class EasePickCustom {
    constructor() {
        this.documentReady = this.documentReady.bind(this);
        this.getReadyState = this.getReadyState.bind(this);
        this.picker = null;
        this.seasons = [];
        this.currentMinStay = 1; // Track current minStay dynamically
    }

    getReadyState() {
        if (document.readyState === 'complete') {
            this.documentReady();
        } else {
            window.setTimeout(this.getReadyState, 100);
        }
    }

    // Load seasons from data attribute
    loadSeasons() {
        const bookingCalendar = document.querySelector('.booking-calendar');
        if (bookingCalendar && bookingCalendar.dataset.seasons) {
            try {
                this.seasons = JSON.parse(bookingCalendar.dataset.seasons);
                console.log('Loaded seasons:', this.seasons);
                
                // Debug: show each season's date range
                this.seasons.forEach((season, index) => {
                    const start = this.timestampToDate(season.startDate);
                    const end = this.timestampToDate(season.endDate);
                    console.log(`Season ${index}: ${season.seasonName}, Start: ${start.toDateString()}, End: ${end.toDateString()}, MinStay: ${season.minStay}`);
                });
            } catch (e) {
                console.error('Failed to parse seasons:', e);
                this.seasons = [];
            }
        }
        return this.seasons;
    }

    // Convert timestamp to Date object
    timestampToDate(timestamp) {
        if (typeof timestamp === 'string') {
            return new Date(timestamp);
        }
        // Handle both Unix timestamps (seconds) and milliseconds
        if (timestamp > 9999999999) {
            return new Date(timestamp);
        }
        return new Date(timestamp * 1000);
    }

    // Get season for a specific date
    getSeasonForDate(date) {
        if (!date || this.seasons.length === 0) return null;
        
        let checkDate;
        if (typeof date === 'string') {
            // Handle German date format DD.MM.YYYY
            if (date.includes('.')) {
                const parts = date.split('.');
                checkDate = new Date(parts[2], parts[1] - 1, parts[0], 12, 0, 0);
            } else {
                checkDate = new Date(date);
            }
        } else {
            checkDate = new Date(date);
        }
        
        checkDate.setHours(12, 0, 0, 0);
        const checkTime = checkDate.getTime();
        
        console.log('Checking date:', checkDate.toDateString(), 'Time:', checkTime);
        
        for (const season of this.seasons) {
            const startDate = this.timestampToDate(season.startDate);
            const endDate = this.timestampToDate(season.endDate);
            
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(23, 59, 59, 999);
            
            console.log(`Comparing with ${season.seasonName}: ${startDate.toDateString()} - ${endDate.toDateString()}`);
            console.log(`  Start: ${startDate.getTime()}, End: ${endDate.getTime()}, Check: ${checkTime}`);
            
            if (checkTime >= startDate.getTime() && checkTime <= endDate.getTime()) {
                console.log(`  ✓ MATCH! Season: ${season.seasonName}, MinStay: ${season.minStay}`);
                return season;
            }
        }
        
        console.log('  ✗ No season found for this date');
        return null;
    }

    // Get minimum stay for a specific date
    getMinStayForDate(date) {
        const season = this.getSeasonForDate(date);
        const minStay = season ? parseInt(season.minStay, 10) : 1;
        console.log('MinStay for date:', date, '=', minStay);
        return minStay;
    }

    documentReady() {
        const arrivalElement = document.getElementById('arrival');
        const headerArrivalElement = document.getElementById('header-qb-arrival');
        
        // Load seasons first
        this.loadSeasons();

        if (arrivalElement || headerArrivalElement) {
            const minstayElement = document.querySelector('.booking-calendar .minstay');
            const visibleElement = document.querySelector('.booking-calendar .visibledate');

            const minstayText = minstayElement ? minstayElement.textContent : '';
            const visibleText = visibleElement ? visibleElement.textContent : '';
            const visibleDateFrom = visibleText.match(/\d{4}-\d{2}-\d{2}/)?.[0] || new Date().toISOString().split('T')[0];
            const defaultMinstay = parseInt(minstayText.match(/\d+/)?.[0], 10) || 1;

            const minDate = new Date(visibleDateFrom);
            minDate.setDate(minDate.getDate() + 1);

            if (arrivalElement) {
                this.initializeDatePicker(arrivalElement, '#departure', minDate, defaultMinstay);
            }

            if (headerArrivalElement) {
                // Create hidden departure field for header form if it doesn't exist
                let headerDeparture = document.getElementById('header-qb-departure');
                if (!headerDeparture) {
                    headerDeparture = document.createElement('input');
                    headerDeparture.type = 'hidden';
                    headerDeparture.id = 'header-qb-departure';
                    headerDeparture.name = 'departure';
                    headerArrivalElement.parentNode.appendChild(headerDeparture);
                }
                this.initializeDatePicker(headerArrivalElement, '#header-qb-departure', minDate, defaultMinstay);
            }
        } else {
            console.warn("Required elements (#arrival or #header-qb-arrival) not found.");
        }
    }

    initializeDatePicker(element, elementEndSelector, minDate, defaultMinstay) {
        const self = this;
        
        if (typeof easepick !== 'undefined' && easepick.create) {
            this.picker = new easepick.create({
                element: element,
                css: [
                    'bundles/rapidskeleton/frontend/css/easypick.min.css',
                    'bundles/rapidskeleton/frontend/css/easypick.custom.css',
                    'bundles/customelements/frontend/css/bundle-easepick-custom.css',
                ],
                zIndex: 10,
                lang: 'de-DE',
                format: 'DD.MM.YYYY',
                LockPlugin: {
                    minDate: minDate,
                    minDays: 2, // Default, will be overridden dynamically
                    selectForward: true,
                    filter(date, picked) {
                        // If no seasons configured, allow all dates
                        if (self.seasons.length === 0) {
                            return false;
                        }
                        
                        const jsDate = date.toJSDate();
                        jsDate.setHours(12, 0, 0, 0);
                        
                        // DEPARTURE DATE SELECTION - when arrival is already picked
                        if (picked && picked.length === 1) {
                            const arrivalJsDate = picked[0].toJSDate();
                            arrivalJsDate.setHours(12, 0, 0, 0);
                            
                            // Find the season for the arrival date
                            const season = self.getSeasonForDate(arrivalJsDate);
                            
                            if (season) {
                                const minStay = parseInt(season.minStay, 10);
                                
                                // Calculate minimum departure date
                                const minDepartureDate = new Date(arrivalJsDate);
                                minDepartureDate.setDate(minDepartureDate.getDate() + minStay);
                                minDepartureDate.setHours(0, 0, 0, 0);
                                
                                console.log(`Filter check - Arrival: ${arrivalJsDate.toDateString()}, Season: ${season.seasonName}, MinStay: ${minStay}`);
                                console.log(`  Min departure: ${minDepartureDate.toDateString()}, Current date: ${jsDate.toDateString()}`);
                                console.log(`  Disable: ${jsDate < minDepartureDate}`);
                                
                                // Disable dates before minimum stay is reached
                                if (jsDate < minDepartureDate) {
                                    return true; // true = disabled
                                }
                            } else {
                                // No season - use default minStay of 1
                                const minDepartureDate = new Date(arrivalJsDate);
                                minDepartureDate.setDate(minDepartureDate.getDate() + 1);
                                
                                if (jsDate < minDepartureDate) {
                                    return true;
                                }
                            }
                        }
                        
                        // For arrival selection, allow all dates
                        return false;
                    }
                },
                RangePlugin: {
                    elementEnd: elementEndSelector,
                    locale: {
                        one: 'Tag',
                        other: 'Tage'
                    }
                },
                plugins: ['RangePlugin', 'LockPlugin'],
                locale: {
                    nextMonth: '',
                    previousMonth: '',
                },
                setup(picker) {
                    // When user clicks on arrival date
                    picker.on('click', (e) => {
                        // Check if this is the first click (selecting arrival)
                        const currentRange = picker.getStartDate();
                        if (!currentRange) {
                            // This will be the arrival date
                            const clickedDate = e.target.closest('.day');
                            if (clickedDate && clickedDate.dataset.time) {
                                const date = new Date(parseInt(clickedDate.dataset.time));
                                const season = self.getSeasonForDate(date);
                                
                                if (season) {
                                    self.currentMinStay = parseInt(season.minStay, 10);
                                    console.log(`Clicked on arrival date, Season: ${season.seasonName}, Setting minStay to: ${self.currentMinStay}`);
                                    self.updateMinStayDisplay(season);
                                } else {
                                    self.currentMinStay = 1;
                                }
                            }
                        }
                    });
                    
                    picker.on('select', (e) => {
                        // When arrival date is selected, show the minStay info
                        if (e.detail.start && !e.detail.end) {
                            const arrivalDate = e.detail.start.toJSDate();
                            const season = self.getSeasonForDate(arrivalDate);
                            
                            if (season) {
                                self.currentMinStay = parseInt(season.minStay, 10);
                                console.log(`Selected arrival in season: ${season.seasonName}, minStay: ${season.minStay}`);
                                self.updateMinStayDisplay(season);
                                
                                // Force re-render to apply new minStay filter
                                picker.renderAll();
                            } else {
                                self.currentMinStay = 1;
                                console.log('No season for selected date, using default minStay: 1');
                            }
                        }
                        
                        // Update the display to show range format
                        setTimeout(() => {
                            if (e.detail.start && e.detail.end) {
                                const startDate = e.detail.start.format('DD.MM.YYYY');
                                const endDate = e.detail.end.format('DD.MM.YYYY');
                                const rangeDisplay = `${startDate} - ${endDate}`;
                                
                                // Validate booking
                                const validation = self.validateBooking(startDate, endDate);
                                if (!validation.valid) {
                                    console.warn('Booking validation failed:', validation.message);
                                    self.showValidationError(validation.message);
                                    return;
                                }
                                
                                // Update the main input to show range
                                element.value = rangeDisplay;
                                
                                // Update all other date range inputs on the page
                                document.querySelectorAll('input[name="date-range"], #arrival, #header-qb-arrival').forEach(input => {
                                    if (input !== element && input.value !== rangeDisplay) {
                                        input.value = rangeDisplay;
                                    }
                                });
                                
                                // Update all hidden arrival/departure fields
                                document.querySelectorAll('input[name="arrival"]').forEach(input => {
                                    if (input !== element && input.value !== startDate) {
                                        input.value = startDate;
                                    }
                                });
                                
                                document.querySelectorAll('input[name="departure"]').forEach(input => {
                                    if (input.value !== endDate) {
                                        input.value = endDate;
                                    }
                                });
                                
                                // Store in session
                                storeInSession('arrival', startDate);
                                storeInSession('departure', endDate);
                            }
                            
                            if (typeof syncFormsAfterDateChange === 'function') {
                                syncFormsAfterDateChange();
                            }
                        }, 100);
                    });
                }
            });
        } else {
            console.error("EasePick library not found or not loaded.");
        }
    }

    // Update the minimum stay display
    updateMinStayDisplay(season) {
        // Update text display
        const minstayDisplay = document.querySelector('.current-minstay-display');
        if (minstayDisplay) {
            minstayDisplay.textContent = `${season.seasonName}: Mindestens ${season.minStay} Nächte`;
        }
        
        // Highlight active season in list
        const minstayElements = document.querySelectorAll('.booking-calendar .minstay');
        minstayElements.forEach(el => {
            el.classList.remove('active-season');
            
            const elStart = el.dataset.start;
            if (elStart) {
                const seasonStartDate = this.timestampToDate(season.startDate);
                const seasonStartStr = seasonStartDate.toISOString().split('T')[0];
                
                if (elStart === seasonStartStr) {
                    el.classList.add('active-season');
                }
            }
        });
    }

    // Validate booking dates
    validateBooking(arrivalDate, departureDate) {
        const parseGermanDate = (dateStr) => {
            const parts = dateStr.split('.');
            return new Date(parts[2], parts[1] - 1, parts[0], 12, 0, 0);
        };
        
        const arrival = parseGermanDate(arrivalDate);
        const departure = parseGermanDate(departureDate);
        
        const season = this.getSeasonForDate(arrival);
        
        // If no season, allow any stay length (default minStay = 1)
        if (!season) {
            return { valid: true, message: '', minStay: 1 };
        }
        
        const nights = Math.ceil((departure - arrival) / (1000 * 60 * 60 * 24));
        const minStay = parseInt(season.minStay, 10);
        
        console.log(`Validating: ${nights} nights, minStay: ${minStay}, Season: ${season.seasonName}`);
        
        if (nights < minStay) {
            return {
                valid: false,
                message: `Mindestaufenthalt in der Saison "${season.seasonName}": ${minStay} Nächte. Sie haben ${nights} Nacht(e) gewählt.`,
                minStay: minStay
            };
        }
        
        return { valid: true, message: '', minStay: minStay, season: season };
    }

    // Show validation error
    showValidationError(message) {
        const existingError = document.querySelector('.booking-validation-error');
        if (existingError) existingError.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'booking-validation-error';
        errorDiv.innerHTML = `<span class="error-icon">⚠️</span> ${message}`;
        
        const datePicker = document.querySelector('#arrival, #header-qb-arrival');
        if (datePicker) {
            datePicker.parentNode.insertBefore(errorDiv, datePicker.nextSibling);
            setTimeout(() => errorDiv.remove(), 5000);
        }
    }
}

// Store in session helper
function storeInSession(name, value) {
    const formData = new FormData();
    formData.append('field_name', name);
    formData.append('field_value', value);
    formData.append('ajax_update', '1');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).catch(e => console.log('Session store error:', e));
}
// Expose helper functions globally
window.QuickBooker = {
    getSeasonForDate: (date) => easepickInstance.getSeasonForDate(date),
    getMinStayForDate: (date) => easepickInstance.getMinStayForDate(date),
    validateBooking: (arrival, departure) => easepickInstance.validateBooking(arrival, departure),
    seasons: () => easepickInstance.seasons
};

// Simple form sync without breaking existing functionality
(function() {
    'use strict';

    let formSyncInitialized = false;

    function initFormSync() {
        if (formSyncInitialized) return;
        formSyncInitialized = true;

        // Sync values between forms
        function syncValue(name, value, isCheckbox = false) {
            document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                if (isCheckbox) {
                    input.checked = value;
                } else {
                    input.value = value;
                }
            });
        }

        // Update guest display
        function updateGuestDisplays() {
            document.querySelectorAll('.number-of-guests-wrapper').forEach(wrapper => {
                const guestInput = wrapper.querySelector('.number-of-guests');
                const adults = parseInt(wrapper.querySelector('input[name="adults"]')?.value || 0);
                const toddlers = parseInt(wrapper.querySelector('input[name="toddlers"]')?.value || 0);
                const children = parseInt(wrapper.querySelector('input[name="children"]')?.value || 0);
                const total = adults + toddlers + children;
                
                if (guestInput) {
                    guestInput.value = total > 0 ? `${total} ` : '';
                }
            });
        }

        // Store in session
        function storeValue(name, value) {
            const formData = new FormData();
            formData.append('field_name', name);
            formData.append('field_value', value);
            formData.append('ajax_update', '1');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).catch(e => console.log('Session store:', e));
        }

        // Handle plus/minus button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('minus') || e.target.classList.contains('plus')) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                const isPlus = e.target.classList.contains('plus');
                const input = e.target.parentNode.querySelector('input[type="number"]');
                
                if (!input) return;
                
                const currentValue = parseInt(input.value) || 0;
                let newValue = isPlus ? currentValue + 1 : Math.max(0, currentValue - 1);
                
                // Special handling for adults - don't allow 0
                if (input.name === 'adults' && newValue === 0) {
                    newValue = 2;
                }
                
                input.value = newValue;
                
                // Sync across forms
                syncValue(input.name, newValue);
                
                // Store value
                storeValue(input.name, newValue);
                
                // Update guest displays
                updateGuestDisplays();
                
                return false;
            }
        }, true);

        // Handle checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox' && (e.target.name === 'dog-friendly' || e.target.name === 'sea-view')) {
                e.stopImmediatePropagation();
                
                syncValue(e.target.name, e.target.checked, true);
                storeValue(e.target.name, e.target.checked);
                
                return false;
            }
        }, true);

        // Handle guest modal display
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('number-of-guests')) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                const modal = e.target.parentNode.querySelector('.number-of-guests-modal');
                if (modal) {
                    // Hide all other modals
                    document.querySelectorAll('.number-of-guests-modal').forEach(m => {
                        if (m !== modal) m.style.display = 'none';
                    });
                    
                    // Toggle current modal
                    modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
                }
                
                return false;
            }
        }, true);

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.number-of-guests-wrapper')) {
                document.querySelectorAll('.number-of-guests-modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // Handle date sync
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('datepicker')) {
                syncValue(e.target.name, e.target.value);
                storeValue(e.target.name, e.target.value);
            }
        });

        // Prevent form submission except via submit button
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'quickbooker') {
                if (!e.submitter || !e.submitter.classList.contains('psa-hero__btn')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            }
        }, true);

        // Override any existing form change handlers
        setTimeout(() => {
            document.querySelectorAll('.quickbooker input').forEach(input => {
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
            });
            
            // Re-initialize date pickers after cloning
            if (window.easepickInstance) {
                window.easepickInstance.documentReady();
            }
            
            // Update displays
            updateGuestDisplays();
        }, 100);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFormSync);
    } else {
        initFormSync();
    }

    window.addEventListener('load', initFormSync);
})();

// Instantiate the date picker
const easepickInstance = new EasePickCustom();
window.easepickInstance = easepickInstance;
easepickInstance.getReadyState();

window.addEventListener('load', () => {
    easepickInstance.getReadyState();
});

// SECRA Priority Sync System with Availability Check
(function() {
    'use strict';

    let isUpdatingFromQuickbooker = false;
    let hasSecraContent = false;

    // Simple storage and retrieval
    function storeValues(values) {
        const cleanValues = {
            arrival: values.arrival || '',
            departure: values.departure || '',
            adults: parseInt(values.adults) || 0,
            children: parseInt(values.children) || 0,
            toddlers: parseInt(values.toddlers) || 0,
            pets: parseInt(values.pets) || 0,
            dogFriendly: values.dogFriendly || false,
            seaView: values.seaView || false
        };
        
        localStorage.setItem('quickbooker_values', JSON.stringify(cleanValues));
        sessionStorage.setItem('quickbooker_values', JSON.stringify(cleanValues));
    }

    function getStoredValues() {
        let values = null;
        
        // Try localStorage first, then sessionStorage
        const stored = localStorage.getItem('quickbooker_values') || sessionStorage.getItem('quickbooker_values');
        if (stored) {
            try {
                values = JSON.parse(stored);
            } catch (e) {
                console.warn('Failed to parse stored values');
            }
        }
        
        // Also check URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('arrival') || urlParams.get('adults')) {
            const urlValues = {
                arrival: urlParams.get('arrival') || '',
                departure: urlParams.get('departure') || '',
                adults: parseInt(urlParams.get('adults')) || 0,
                children: parseInt(urlParams.get('children')) || 0,
                toddlers: parseInt(urlParams.get('toddlers')) || 0,
                pets: parseInt(urlParams.get('pets')) || 0,
                dogFriendly: urlParams.get('dog_friendly') === '1',
                seaView: urlParams.get('sea_view') === '1'
            };
            
            storeValues(urlValues);
            return urlValues;
        }
        
        return values;
    }

    // Check if SECRA widgets have content
    function checkSecraContent() {
        const dateWidget = document.querySelector('.op-frontend-picker-traveltime-12');
        const personsWidget = document.querySelector('.op-frontend-picker-persons-13');
        
        const hasDateContent = dateWidget && 
                              !dateWidget.classList.contains('js--is-empty') && 
                              dateWidget.querySelector('.widget-content');
        
        const hasPersonsContent = personsWidget && 
                                 !personsWidget.classList.contains('js--is-empty') && 
                                 personsWidget.querySelector('.widget-content');
        
        // Check for any checked filters
        const hasFilters = document.querySelector('input[name="ausstattung_76470"]:checked') ||
                          document.querySelector('input[name="14_Hund"]:checked') ||
                          document.querySelector('input[name="ausstattung_76591"]:checked');
        
        hasSecraContent = hasDateContent || hasPersonsContent || hasFilters;
        
        return hasSecraContent;
    }

    function ensureDefaultAdults(values) {
        if (!values.adults || values.adults === 0) {
            values.adults = 2;
        }
        return values;
    }

    // Extract values from quickbooker form
    function extractQuickbookerValues() {
        const form = document.querySelector('#quickbooker, #quickbooker-home')
        if (!form) return null;

        const values = {
            arrival: form.querySelector('input[name="arrival"]')?.value || '',
            departure: form.querySelector('input[name="departure"]')?.value || '',
            adults: parseInt(form.querySelector('input[name="adults"]')?.value || 0),
            toddlers: parseInt(form.querySelector('input[name="toddlers"]')?.value || 0),
            children: parseInt(form.querySelector('input[name="children"]')?.value || 0),
            pets: parseInt(form.querySelector('input[name="pets"]')?.value || 0),
            dogFriendly: form.querySelector('input[name="dog-friendly"]')?.checked || false,
            seaView: form.querySelector('input[name="sea-view"]')?.checked || false
        };
        return ensureDefaultAdults(values);
    }

    // Extract current values from SECRA filters
    function extractSecraValues() {
        const values = {
            arrival: '',
            departure: '',
            adults: 0,
            children: 0,
            toddlers: 0,
            pets: 0,
            dogFriendly: false,
            seaView: false
        };

        // Get date values from SECRA widget
        const dateWidget = document.querySelector('.op-frontend-picker-traveltime-12');
        if (dateWidget) {
            const content = dateWidget.querySelector('.widget-content');
            if (content) {
                const dateText = content.textContent.trim();
                const dateMatch = dateText.match(/(\d{2}\.\d{2}\.\d{4})\s*-\s*(\d{2}\.\d{2}\.\d{4})/);
                if (dateMatch) {
                    values.arrival = dateMatch[1];
                    values.departure = dateMatch[2];
                }
            }
        }

        // Get person values from SECRA widget
        const personsWidget = document.querySelector('.op-frontend-picker-persons-13');
        if (personsWidget) {
            const content = personsWidget.querySelector('.widget-content');
            if (content) {
                const guestText = content.textContent.trim();
                
                const adultsMatch = guestText.match(/(\d+)\s+Erwachsene/);
                if (adultsMatch) values.adults = parseInt(adultsMatch[1]);
                
                const childrenMatch = guestText.match(/(\d+)\s+Kinder/);
                if (childrenMatch) {
                    const totalChildren = parseInt(childrenMatch[1]);
                    values.children = Math.floor(totalChildren / 2);
                    values.toddlers = Math.ceil(totalChildren / 2);
                }
            }
        }

        // Get filter values
        const petFilter = document.querySelector('input[name="ausstattung_76470"]:checked') ||
                         document.querySelector('input[value="65000"]:checked');
        if (petFilter) {
            values.pets = 1;
        }

        const dogFilter = document.querySelector('input[name="14_Hund"]:checked') ||
                         document.querySelector('input[name="ausstattung_76470"]:checked');
        if (dogFilter) {
            values.dogFriendly = true;
        }

        const seaViewFilter = document.querySelector('input[name="ausstattung_76591"]:checked') ||
                             document.querySelector('input[value="65121"]:checked');
        if (seaViewFilter) {
            values.seaView = true;
        }

        return values;
    }

    // Reload/refresh SECRA results
    function reloadSecraResults() {
        
        // Method 1: Trigger SECRA search/filter mechanism
        if (triggerSecraSearch()) {
            return true;
        }
        
        // Method 2: Try to reload the page with current filter values if search fails
        const currentValues = extractSecraValues();
        if (currentValues.arrival || currentValues.adults > 0) {
            
            const params = new URLSearchParams();
            if (currentValues.arrival) params.set('arrival', currentValues.arrival);
            if (currentValues.departure) params.set('departure', currentValues.departure);
            if (currentValues.adults > 0) params.set('adults', currentValues.adults);
            if (currentValues.children > 0) params.set('children', currentValues.children);
            if (currentValues.toddlers > 0) params.set('toddlers', currentValues.toddlers);
            if (currentValues.pets > 0) params.set('pets', currentValues.pets);
            if (currentValues.dogFriendly) params.set('dog_friendly', '1');
            if (currentValues.seaView) params.set('sea_view', '1');
            
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = newUrl;
            return true;
        }
        
        return false;
    }

    // Trigger SECRA search/filter update
    function triggerSecraSearch() {
        
        // Method 1: Try to find and click the search/filter button
        const searchButton = document.querySelector('.filter_switchbox_ok_button');
        if (searchButton) {
            searchButton.click();
            return true;
        }
        
        // Method 2: Try to trigger filter change event on any checked filter
        const checkedFilters = document.querySelectorAll('input[type="checkbox"]:checked');
        if (checkedFilters.length > 0) {
            checkedFilters.forEach(filter => {
                filter.dispatchEvent(new Event('change', { bubbles: true }));
            });
            return true;
        }
        
        // Method 3: Try to find any search-related buttons
        const searchButtons = document.querySelectorAll('button, input[type="submit"], .search-button, .filter-button');
        for (let button of searchButtons) {
            const buttonText = button.textContent.toLowerCase();
            if (buttonText.includes('suchen') || 
                buttonText.includes('search') || 
                buttonText.includes('anzeigen') || 
                buttonText.includes('filter') ||
                buttonText.includes('aktualisieren') ||
                buttonText.includes('refresh')) {
                button.click();
                return true;
            }
        }
        
        // Method 4: Try to trigger any form submissions related to filters
        const filterForms = document.querySelectorAll('form');
        for (let form of filterForms) {
            if (form.querySelector('input[type="checkbox"]') || 
                form.classList.contains('filter') ||
                form.id.includes('filter') ||
                form.className.includes('search')) {
                form.submit();
                return true;
            }
        }
        return false;
    }

    // SECRA widget update - only send compatible values
    function updateSecraWidgets(values, triggerSearch = false) {
        if (!values) return;
        let widgetUpdated = false;
                
        // Update date widget
        const dateWidget = document.querySelector('.op-frontend-picker-traveltime-12');
        if (dateWidget && (values.arrival && values.departure)) {
            const content = dateWidget.querySelector('.op-frontend-picker-content');
            if (content) {
                const parseGermanDate = (dateStr) => {
                    const parts = dateStr.split('.');
                    return new Date(parts[2], parts[1] - 1, parts[0]);
                };

                const formatShort = (date) =>
                    `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.`;

                const getWeekdayName = (date) => {
                    const days = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
                    return days[date.getDay()];
                };

                const arrivalDate = parseGermanDate(values.arrival);
                const departureDate = parseGermanDate(values.departure);
                const nights = Math.ceil((departureDate - arrivalDate) / (1000 * 60 * 60 * 24));

                const arrivalShort = formatShort(arrivalDate);
                const arrivalDay = getWeekdayName(arrivalDate);
                const departureDay = getWeekdayName(departureDate);

                const dateHTML =
                    `<div class="info-row-1"><span>${arrivalShort}</span> -<span>${values.departure}</span></div>` +
                    `<div class="info-row-2"><span data-i18n="pickerWidget.traveltime.weekdays.${arrivalDate.getDay()}">${arrivalDay}</span> - ` +
                    `<span data-i18n="pickerWidget.traveltime.weekdays.${departureDate.getDay()}">${departureDay}</span>` +
                    `<span>(` +
                    `${nights} <span data-i18n="pickerWidget.traveltime.night" data-i18n-options="{&quot;count&quot;:${nights} }">${nights === 1 ? 'Nacht' : 'Nächte'}</span>` +
                    `)</span></div>`;

                isUpdatingFromQuickbooker = true;
                content.innerHTML = dateHTML;
                dateWidget.classList.remove('js--is-empty');

                const resetIcon = dateWidget.parentElement?.querySelector('.icon-reset');
                if (resetIcon) resetIcon.style.display = 'block';

                widgetUpdated = true;
                hasSecraContent = true;
                setTimeout(() => { isUpdatingFromQuickbooker = false; }, 500);
            }
        }
        
        // Update persons widget - combine toddlers + children for SECRA
        const personsWidget = document.querySelector('.op-frontend-picker-persons-13');
        if (personsWidget) {
            const content = personsWidget.querySelector('.op-frontend-picker-content');
            if (content) {
                const adults = values.adults || 0;
                const totalChildren = (values.children || 0) + (values.toddlers || 0);
                const totalGuests = adults + totalChildren;
                
                isUpdatingFromQuickbooker = true;
                
                if (totalGuests > 0) {
                    let guestText = '';
                    if (adults > 0) guestText += `${adults} Erwachsene`;
                    if (totalChildren > 0) guestText += `${guestText ? ', ' : ''}${totalChildren} Kinder`;
                    
                    content.innerHTML = `<div class="widget-content">${guestText}</div>`;
                    personsWidget.classList.remove('js--is-empty');
                    
                    const resetIcon = personsWidget.parentElement?.querySelector('.icon-reset');
                    if (resetIcon) resetIcon.style.display = 'block';
                    
                    widgetUpdated = true;
                    hasSecraContent = true;
                    
                    // Also update SECRA modal if it exists
                    updateSecraModal(adults, totalChildren);
                } else {
                    content.innerHTML = '<div class="widget-placeholder"><span>Gäste</span></div>';
                    personsWidget.classList.add('js--is-empty');
                    
                    const resetIcon = personsWidget.parentElement?.querySelector('.icon-reset');
                    if (resetIcon) resetIcon.style.display = 'none';
                    
                    widgetUpdated = true;
                }
                
                setTimeout(() => { isUpdatingFromQuickbooker = false; }, 500);
            }
        }
        
        // Update filters - only those that exist in SECRA
        if (values.pets > 0) {
            const petSelectors = [
                'input[name="ausstattung_76470"]', // Haustiere erlaubt
                'input[value="65000"]'
            ];
            
            petSelectors.forEach(selector => {
                const element = document.querySelector(selector);
                if (element && !element.checked) {
                    isUpdatingFromQuickbooker = true;
                    element.checked = true;
                    element.dispatchEvent(new Event('change', { bubbles: true }));
                    widgetUpdated = true;
                    hasSecraContent = true;
                    setTimeout(() => { isUpdatingFromQuickbooker = false; }, 500);
                }
            });
        }
        
        if (values.dogFriendly) {
            const dogSelectors = [
                'input[name="14_Hund"]', // Urlaubsthema Hund
                'input[name="ausstattung_76470"]' // Also Haustiere erlaubt for dogs
            ];
            
            dogSelectors.forEach(selector => {
                const element = document.querySelector(selector);
                if (element && !element.checked) {
                    isUpdatingFromQuickbooker = true;
                    element.checked = true;
                    element.dispatchEvent(new Event('change', { bubbles: true }));
                    widgetUpdated = true;
                    hasSecraContent = true;
                    setTimeout(() => { isUpdatingFromQuickbooker = false; }, 500);
                }
            });
        }
        
        if (values.seaView) {
            const seaViewSelectors = [
                'input[name="ausstattung_76591"]',
                'input[value="65121"]'
            ];
            
            seaViewSelectors.forEach(selector => {
                const element = document.querySelector(selector);
                if (element && !element.checked) {
                    isUpdatingFromQuickbooker = true;
                    element.checked = true;
                    element.dispatchEvent(new Event('change', { bubbles: true }));
                    widgetUpdated = true;
                    hasSecraContent = true;
                    setTimeout(() => { isUpdatingFromQuickbooker = false; }, 500);
                }
            });
        }
        
        // Trigger search after widgets are updated
        if (widgetUpdated && triggerSearch) {
            setTimeout(() => {
                triggerSecraSearch();
            }, 1000);
        }
    }

    // Update SECRA modal with quickbooker values
    function updateSecraModal(adults, totalChildren) {
        // Update adults in modal
        if (adults <= 4) {
            // Use radio buttons for 1-4 adults
            const adultRadios = document.querySelectorAll('input[name="adults"]');
            adultRadios.forEach((radio, index) => {
                if (index + 1 === adults) {
                    radio.checked = true;
                    radio.parentElement.classList.add('active');
                } else {
                    radio.checked = false;
                    radio.parentElement.classList.remove('active');
                }
            });
            
            // Clear dropdown
            const adultsDropdown = document.querySelector('#adults-more select');
            if (adultsDropdown) adultsDropdown.selectedIndex = 0;
        } else {
            // Use dropdown for 5+ adults
            const adultRadios = document.querySelectorAll('input[name="adults"]');
            adultRadios.forEach(radio => {
                radio.checked = false;
                radio.parentElement.classList.remove('active');
            });
            
            const adultsDropdown = document.querySelector('#adults-more select');
            if (adultsDropdown) {
                const option = adultsDropdown.querySelector(`option[value="${adults}"]`);
                if (option) {
                    adultsDropdown.value = adults;
                }
            }
        }
        
        // Update children in modal
        if (totalChildren <= 3) {
            // Use radio buttons for 0-3 children
            const childRadios = document.querySelectorAll('input[name="childs"]');
            childRadios.forEach((radio, index) => {
                if (index === totalChildren) {
                    radio.checked = true;
                    radio.parentElement.classList.add('active');
                } else {
                    radio.checked = false;
                    radio.parentElement.classList.remove('active');
                }
            });
            
            // Clear dropdown
            const childsDropdown = document.querySelector('#childs-more select');
            if (childsDropdown) childsDropdown.selectedIndex = 0;
        } else {
            // Use dropdown for 4+ children
            const childRadios = document.querySelectorAll('input[name="childs"]');
            childRadios.forEach(radio => {
                radio.checked = false;
                radio.parentElement.classList.remove('active');
            });
            
            const childsDropdown = document.querySelector('#childs-more select');
            if (childsDropdown) {
                const option = childsDropdown.querySelector(`option[value="${totalChildren}"]`);
                if (option) {
                    childsDropdown.value = totalChildren;
                }
            }
        }
    }

    // Update SECRA from quickbooker modal submission
    function handleQuickbookerModalSubmission() {
        // Listen for quickbooker modal form submissions or updates
        document.addEventListener('click', function(e) {
            // Check if it's a modal close/save button or form submission
            if (e.target.classList.contains('number-of-guests') ||
                e.target.closest('.number-of-guests-modal') ||
                (e.target.type === 'button' && e.target.closest('.number-of-guests-wrapper'))) {
                
                // Wait a bit for the modal values to update
                setTimeout(() => {
                    const quickbookerValues = extractQuickbookerModalValues();
                    if (quickbookerValues) {                        
                        // Store the values
                        storeValues(quickbookerValues);
                        
                        // Update SECRA widgets
                        updateSecraWidgets(quickbookerValues, false);
                        
                        // Also sync with SECRA modal structure
                        syncToSecraModal(quickbookerValues);
                    }
                }, 300);
            }
        });
        
        // Also listen for input changes in the quickbooker modal
        document.addEventListener('change', function(e) {
            if (e.target.closest('.number-of-guests-modal')) {
                setTimeout(() => {
                    const quickbookerValues = extractQuickbookerModalValues();
                    if (quickbookerValues) {
                        storeValues(quickbookerValues);
                        updateSecraWidgets(quickbookerValues, false);
                        syncToSecraModal(quickbookerValues);
                    }
                }, 100);
            }
        });
    }

    // Extract values from quickbooker modal
    function extractQuickbookerModalValues() {
        const modal = document.querySelector('.number-of-guests-modal');
        if (!modal) return null;

        const values = {
            arrival: '',
            departure: '',
            adults: parseInt(modal.querySelector('input[name="adults"]')?.value || 0),
            toddlers: parseInt(modal.querySelector('input[name="toddlers"]')?.value || 0),
            children: parseInt(modal.querySelector('input[name="children"]')?.value || 0),
            pets: parseInt(modal.querySelector('input[name="pets"]')?.value || 0),
            dogFriendly: false,
            seaView: false
        };

        // Also get dates from main form if available
        const form = document.querySelector('#quickbooker, #quickbooker-home')
        if (form) {
            values.arrival = form.querySelector('input[name="arrival"]')?.value || '';
            values.departure = form.querySelector('input[name="departure"]')?.value || '';
            values.dogFriendly = form.querySelector('input[name="dog-friendly"]')?.checked || false;
            values.seaView = form.querySelector('input[name="sea-view"]')?.checked || false;
        }

        return ensureDefaultAdults(values);
    }

    // Sync quickbooker values to SECRA modal structure
    function syncToSecraModal(values) {
        if (!values) return;
        
        // For SECRA modal: combine toddlers + children into total children
        const totalChildren = (values.toddlers || 0) + (values.children || 0);
        
        // Update SECRA modal radio buttons and dropdowns
        updateSecraModal(values.adults || 0, totalChildren);
        
        // Also set child ages if SECRA has age selection
        if (totalChildren > 0) {
            updateSecraChildAges(values.toddlers || 0, values.children || 0);
        }
    }

    // Update child ages in SECRA modal
    function updateSecraChildAges(toddlers, children) {
        const childAgeSelects = document.querySelectorAll('.child-age-select select');
        let ageIndex = 0;
        
        // Set toddler ages (0-5 years)
        for (let i = 0; i < toddlers && ageIndex < childAgeSelects.length; i++) {
            if (childAgeSelects[ageIndex]) {
                // Set a random age between 0-5 for toddlers, or default to 3
                const toddlerAge = Math.floor(Math.random() * 6); // 0-5
                childAgeSelects[ageIndex].value = toddlerAge;
                ageIndex++;
            }
        }
        
        // Set children ages (6-17 years)
        for (let i = 0; i < children && ageIndex < childAgeSelects.length; i++) {
            if (childAgeSelects[ageIndex]) {
                // Set a random age between 6-17 for children, or default to 10
                const childAge = 6 + Math.floor(Math.random() * 12); // 6-17
                childAgeSelects[ageIndex].value = childAge;
                ageIndex++;
            }
        }
    }

    // Only restore from stored values if SECRA is completely empty AND on initial load
    function checkAndRestoreIfEmpty() {
        // Only restore during initial load, not periodically
        if (!checkSecraContent()) {
            const storedValues = getStoredValues();
            if (storedValues) {
                updateSecraWidgets(storedValues, false);
            }
        } else {
        }
    }

    // Handle "Verfügbarkeit prüfen" (Check Availability) button click
    function handleAvailabilityCheck() {
        document.addEventListener('click', function(e) {
            // Look for the availability check button
            const button = e.target;
            const buttonText = button.textContent?.toLowerCase() || '';
            
            // Check for various possible button texts
            if (buttonText.includes('verfügbarkeit') && buttonText.includes('prüfen') ||
                buttonText.includes('check availability') ||
                buttonText.includes('availability') ||
                button.classList.contains('availability-check') ||
                button.id === 'check-availability' ||
                (button.type === 'submit' && button.closest('#quickbooker, #quickbooker-home'))) {
                
                // Prevent default form submission
                e.preventDefault();
                e.stopPropagation();
                
                // Get current quickbooker values
                let quickbookerValues = extractQuickbookerValues();
                
                if (quickbookerValues) {
                    // Ensure adults default to 2 if empty/0
                    if (!quickbookerValues.adults || quickbookerValues.adults === 0) {
                        quickbookerValues.adults = 2;
                        // Update the form field
                        const adultsInput = document.querySelector('#quickbooker input[name="adults"], #quickbooker-home input[name="adults"]');
                        if (adultsInput) {
                            adultsInput.value = 2;
                        }
                    }
                    
                    // Convert values to URL parameters
                    const urlParams = buildSearchURLParams(quickbookerValues);
                    
                    // Store the values for later use
                    storeValues(quickbookerValues);
                    
                    // Redirect to ferienobjekte with parameters
                    const targetURL = '/ferienobjekte' + (urlParams ? '?' + urlParams : '') + '#scroll-to-object';
                    
                    window.location.href = targetURL;
                }
            }
        }, true);
    }

    function buildSearchURLParams(values) {
        const params = new URLSearchParams();
        
        // Convert dates from DD.MM.YYYY to YYYY-MM-DD
        if (values.arrival) {
            const arrivalEN = convertDateFormat(values.arrival);
            if (arrivalEN) params.set('arrival', arrivalEN);
        }
        
        if (values.departure) {
            const departureEN = convertDateFormat(values.departure);
            if (departureEN) params.set('departure', departureEN);
        }
        
        // Add adults
        if (values.adults > 0) {
            params.set('adults', values.adults.toString());
        }
        
        // Convert children: combine toddlers + children into age array
        const childrenAges = [];
        
        // Add toddler ages (0-5 years)
        for (let i = 0; i < (values.toddlers || 0); i++) {
            childrenAges.push(Math.floor(Math.random() * 6)); // 0-5 years
        }
        
        // Add children ages (6-17 years)  
        for (let i = 0; i < (values.children || 0); i++) {
            childrenAges.push(6 + Math.floor(Math.random() * 12)); // 6-17 years
        }
        
        // Add children parameter if we have any
        if (childrenAges.length > 0) {
            params.set('children', '[' + childrenAges.join(',') + ']');
        }
        
        // Add properties (equipment features)
        const properties = [];
        
        if (values.dogFriendly || values.pets > 0) {
            properties.push('Haustiere erlaubt'); // This might need to be adjusted to match your actual property names
        }
        
        if (values.seaView) {
            properties.push('Meerblick'); // This might need to be adjusted to match your actual property names
        }
        
        if (properties.length > 0) {
            params.set('properties', properties.join(','));
        }
        
        // Add topics if needed (vacation themes)
        const topics = [];
        
        if (values.dogFriendly) {
            topics.push('14'); // Assuming topic ID for dog-friendly vacations - adjust as needed
        }
        
        if (topics.length > 0) {
            params.set('topics', topics.join(','));
        }
        
        // Set online booking preference
        params.set('onlinebooking', 'all');
        
        return params.toString();
    }

    // Add this helper function to convert dates
    function convertDateFormat(germanDate) {
        // Convert DD.MM.YYYY to YYYY-MM-DD
        if (!germanDate || typeof germanDate !== 'string') return null;
        
        const parts = germanDate.split('.');
        if (parts.length !== 3) return null;
        
        const day = parts[0].padStart(2, '0');
        const month = parts[1].padStart(2, '0');
        const year = parts[2];
        
        // Validate date parts
        if (day.length !== 2 || month.length !== 2 || year.length !== 4) return null;
        
        return `${year}-${month}-${day}`;
    }

    // Handle form submission - ONLY time we update SECRA from quickbooker
    function handleQuickbookerSubmission() {
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'quickbooker') {
                const values = extractQuickbookerValues();
                if (values) {
                    // storeValues(values);
                    
                    const secraWidget = document.querySelector('.op-frontend-picker-traveltime-12');
                    if (secraWidget) {
                        setTimeout(() => updateSecraWidgets(values, true), 100);
                        setTimeout(() => updateSecraWidgets(values, true), 1000);
                        
                        // Also reload results after form submission
                        setTimeout(() => {
                            reloadSecraResults();
                        }, 5000);
                    }
                }
            }
        });
    }

    // Initialize everything with SECRA priority
    function init() {
        
        handleQuickbookerSubmission();
        handleQuickbookerModalSubmission(); // Add this new handler
        handleAvailabilityCheck();
        // Only restore once during initial load
        setTimeout(() => {
            checkAndRestoreIfEmpty();
        }, 3000);
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.addEventListener('load', () => {
        setTimeout(init, 2000);
    });

    // Test functions
    window.testSecraSync = function() {
        const testValues = {
            arrival: '25.12.2024',
            departure: '01.01.2025',
            adults: 2,
            children: 1,
            toddlers: 0,
            pets: 1,
            dogFriendly: true,
            seaView: true
        };
        storeValues(testValues);
        updateSecraWidgets(testValues, true);
        
        setTimeout(() => {
            reloadSecraResults();
        }, 2000);
    };

    window.testQuickbookerModal = function() {
        const modalValues = extractQuickbookerModalValues();
        
        if (modalValues) {
            syncToSecraModal(modalValues);
            updateSecraWidgets(modalValues, false);
        }
    };

    window.testAvailabilityCheck = function() {
        const quickbookerValues = extractQuickbookerValues();
        
        if (quickbookerValues) {
            updateSecraWidgets(quickbookerValues, false);
            setTimeout(() => reloadSecraResults(), 1500);
        }
    };

    window.clearSecraValues = function() {
        localStorage.removeItem('quickbooker_values');
        sessionStorage.removeItem('quickbooker_values');
        hasSecraContent = false;
    };

    window.checkSecraContent = checkSecraContent;
    window.triggerSearch = triggerSecraSearch;
    window.reloadResults = reloadSecraResults;
    window.extractSecraValues = extractSecraValues;
    window.extractQuickbookerModal = extractQuickbookerModalValues;

})();

  const homeQuickbooker = document.querySelector('.quickbooker-wrapper-home form');
  if (homeQuickbooker) {
    homeQuickbooker.id = 'quickbooker-home';
  }

  // Fix Date Picker Synchronization - Add this to your JavaScript

// First, define the missing syncFormsAfterDateChange function
window.syncFormsAfterDateChange = function() {
    // console.log('=== SYNCING FORMS AFTER DATE CHANGE ===');
    
    // Find all date inputs and sync their values
    const arrivalInputs = document.querySelectorAll('input[name="arrival"]');
    const departureInputs = document.querySelectorAll('input[name="departure"]');
    
    let arrivalValue = '';
    let departureValue = '';
    
    // Get the current values from any filled input
    arrivalInputs.forEach(input => {
        if (input.value && !arrivalValue) {
            arrivalValue = input.value;
        }
    });
    
    departureInputs.forEach(input => {
        if (input.value && !departureValue) {
            departureValue = input.value;
        }
    });
    
    // console.log('Found date values:', { arrivalValue, departureValue });
    
    // Sync to all forms
    if (arrivalValue) {
        arrivalInputs.forEach(input => {
            if (input.value !== arrivalValue) {
                input.value = arrivalValue;
                // console.log('Synced arrival to:', input.id || input.className);
            }
        });
    }
    
    if (departureValue) {
        departureInputs.forEach(input => {
            if (input.value !== departureValue) {
                input.value = departureValue;
                // console.log('Synced departure to:', input.id || input.className);
            }
        });
    }
    
    // Store in session
    if (arrivalValue) {
        storeValueInSession('arrival', arrivalValue);
    }
    if (departureValue) {
        storeValueInSession('departure', departureValue);
    }
};

// Helper function to store values in session
function storeValueInSession(name, value) {
    const formData = new FormData();
    formData.append('field_name', name);
    formData.append('field_value', value);
    formData.append('ajax_update', '1');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(() => {
        // console.log(`Stored ${name} = ${value} in session`);
    }).catch(e => {
        console.log('Session store error:', e);
    });
}

// Enhanced date picker initialization to ensure proper syncing
(function() {
    'use strict';
    
    // Override the EasePick setup to add better change handling
    function enhanceDatePickerSync() {
        
        // Listen for any changes to date inputs
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('datepicker') || 
                e.target.name === 'arrival' || 
                e.target.name === 'departure') {
                
                // console.log('Date input changed:', e.target.name, '=', e.target.value);
                
                // Trigger sync after a short delay
                setTimeout(() => {
                    syncFormsAfterDateChange();
                }, 100);
            }
        });
        
        // Also listen for change events
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('datepicker') || 
                e.target.name === 'arrival' || 
                e.target.name === 'departure') {
                
                // console.log('Date change event:', e.target.name, '=', e.target.value);
                
                setTimeout(() => {
                    syncFormsAfterDateChange();
                }, 100);
            }
        });
        
        // Monitor for EasePick date selection
        document.addEventListener('click', function(e) {
            // Check if clicked inside a date picker
            if (e.target.closest('.easepick-wrapper') || e.target.classList.contains('day')) {
                // console.log('EasePick calendar clicked');
                
                // Check for date changes after a delay
                setTimeout(() => {
                    // console.log('Checking for date changes after calendar click...');
                    
                    const allDateInputs = document.querySelectorAll('input[name="arrival"], input[name="departure"]');
                    allDateInputs.forEach(input => {
                        if (input.value) {
                            // console.log('Found date value:', input.name, '=', input.value);
                        }
                    });
                    
                    syncFormsAfterDateChange();
                }, 500);
            }
        });
    }
    
    // Debug function to check current date values
    window.checkCurrentDates = function() {
        // console.log('=== CURRENT DATE VALUES ===');
        
        const allDateInputs = document.querySelectorAll('input[name="arrival"], input[name="departure"]');
        allDateInputs.forEach(input => {
            const form = input.closest('form');
            const formId = form ? (form.id || form.className) : 'unknown';
            // console.log(`${input.name} in ${formId}:`, input.value);
        });
    };
    
    // Debug function to manually set dates
    window.setTestDates = function() {
        const testArrival = '25.12.2024';
        const testDeparture = '01.01.2025';
        
        // console.log('Setting test dates...');
        
        document.querySelectorAll('input[name="arrival"]').forEach(input => {
            input.value = testArrival;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        document.querySelectorAll('input[name="departure"]').forEach(input => {
            input.value = testDeparture;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        setTimeout(() => {
            window.checkCurrentDates();
            syncFormsAfterDateChange();
        }, 100);
    };
    
    // Force sync all forms periodically (for debugging)
    window.forceSyncDates = function() {
        // Get dates from any picker that might have them
        const easepickInputs = document.querySelectorAll('.easepick-input');
        
        easepickInputs.forEach(input => {
            if (input.value) {
                // console.log('Found EasePick value:', input.value, 'for', input.name);
                
                // Copy to all inputs with the same name
                document.querySelectorAll(`input[name="${input.name}"]`).forEach(targetInput => {
                    if (targetInput !== input) {
                        targetInput.value = input.value;
                        // console.log('Copied to:', targetInput.id || targetInput.className);
                    }
                });
            }
        });
        
        syncFormsAfterDateChange();
    };
    
    // Initialize enhanced date picker sync
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceDatePickerSync);
    } else {
        enhanceDatePickerSync();
    }

    window.addEventListener('load', function() {
        enhanceDatePickerSync();
        
        // Check dates after everything loads
        setTimeout(() => {
            // console.log('Checking dates after page load...');
            window.checkCurrentDates();
        }, 1000);
    });
})();