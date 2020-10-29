class searchFilter {
    constructor() {

        this.search_filter_message = document.querySelector('.js-search-results-message');
        this.count_message = document.querySelector('.js-search-results-count');

        // There is three filter dropdowns, each with different associated classses on the content cards
        this.filter_types = {
            'interest': {
                'dropdown': document.querySelector('.js-search-interest-filter'),
                'card_class': '.content-card--interest-',
                'active_filter': null,
            },
            'sector': {
                'dropdown': document.querySelector('.js-search-sector-filter'),
                'card_class': '.content-card--sector-',
                'active_filter': null,
            },
            'type': {
                'dropdown': document.querySelector('.js-search-type-filter'),
                'card_class': '.content-card--',
                'active_filter': null,
            }
        };

        this.setFilterFromUrl();
        this.setResultsCount();
        this.bindEvents();
    }

    /*
    *   Update the search results on change of any of the dropdowns
    */
    bindEvents() {
        for(let [type, data] of Object.entries(this.filter_types)) {
            data.dropdown.addEventListener('change', () => { this.updateSearchResults() });
        }
    }

    /*
    *   Sets the active filter values for each type in the filter_types object
    *   Sets all cards to be hidden, then gets the selected ones from all 3 filters and removes the hidden class
    */
    updateSearchResults() {
        this.setActiveFilters();

        let card_class_to_load = ['.content-card'];

        const filter_classes = this.getActiveFilterClasses();
        if(filter_classes.length > 0) {
            card_class_to_load = filter_classes;
        }

        const cards = document.querySelectorAll('.content-card');
        cards.forEach((card) => {
            card.parentNode.classList.add('content-card-wrapper--hidden');
        });

        const cards_to_load = document.querySelectorAll(card_class_to_load.join(''));

        if(cards_to_load.length > 0) {
            this.clearMessage();

            cards_to_load.forEach((card) => {
                card.parentNode.classList.remove('content-card-wrapper--hidden');
            });

        } else {
            this.setMessage('No results found');
        }

        this.setResultsCount();
        this.setQueryString();
    }  

    /*
    *   Update the filter_type object active values based on the dropdowns
    */
    setActiveFilters() {
        for(let [type, data] of Object.entries(this.filter_types)) {
            const selected_option = data.dropdown.value;
            this.filter_types[type].active_filter = selected_option;
        }
    }

    /*
    *   Get the content card classes for the currently active filters
    */
    getActiveFilterClasses() {
        let active_classes = [];

        for(let [type, data] of Object.entries(this.filter_types)) {
            if(data.active_filter !== null && data.active_filter !== 'all') {
                active_classes.push(this.filter_types[type].card_class + this.filter_types[type].active_filter);
            }
        }

        return active_classes;
    }

    /*
    *   Count the currently visible results display the count text
    */
    setResultsCount() {
        const visible_results = document.querySelectorAll('.js-search-results-list > .cell:not(.content-card-wrapper--hidden)');
        const result_text = visible_results == 1 ? 'result' : 'results';

        this.count_message.textContent = `Showing ${visible_results.length} ${result_text}`;
    }

    /*
    *   If the page is loaded with query parameters for the filters, use them to set the filters
    */
    setFilterFromUrl() {
        const url = new URL(window.location.href);

        for(let [type, data] of Object.entries(this.filter_types)) {
            const query_param_value = url.searchParams.get(type);
            if(query_param_value) {
                this.filter_types[type].active_filter = query_param_value
                const selected_option = data.dropdown.querySelector(`option[value="${query_param_value}"]`);
                selected_option.selected = true;

                this.updateSearchResults();
            }
        }
    }

    /*
    *   Update the query string of the current URL, and replace the current state with it
    */
    setQueryString() {
        const url = new URL(window.location.href);

        for(let [type, data] of Object.entries(this.filter_types)) {
            url.searchParams.set(type, data.active_filter);
        }

        const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + url.searchParams.toString();
        
        window.history.replaceState({path: newurl}, '', newurl);
    }

    /*
    *   Set a results message
    */
    setMessage(message) {
        this.search_filter_message.textContent = message;
    }

    /*
    *   Get rid of any results messages
    */
    clearMessage() {
        this.search_filter_message.textContent = '';
    }
}

export default searchFilter;