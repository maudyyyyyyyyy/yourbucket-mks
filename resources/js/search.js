document.addEventListener('DOMContentLoaded', () => {
    const elements = {
        search: document.getElementById('searchInput'),
        products: document.querySelector('.products-section'),
        cards: Array.from(document.querySelectorAll('.product-card')),
        noResults: document.getElementById('noResults'),
        hero: document.getElementById('hero-section'),
        newsletter: document.querySelector('.bg-emerald-50')
    };

    class ProductSearch {
        constructor(elements) {
            this.elements = elements;
            this.debounceTimer = null;
            this.init();
        }

        init() {
            if (!this.elements.search) return;

            this.elements.search.addEventListener('input',
                this.debounce(e => this.handleSearch(e.target.value.toLowerCase().trim()))
            );
        }

        debounce(fn, delay = 300) {
            return (...args) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => fn(...args), delay);
            };
        }

        handleSearch(searchTerm) {
            if (!searchTerm) {
                this.resetView();
                return;
            }

            this.toggleSections(true);
            const searchResults = this.getSearchResults(searchTerm);
            this.updateDisplay(searchResults);
        }

        getSearchResults(searchTerm) {
            return this.elements.cards
                .map(card => ({
                    card,
                    score: this.calculateRelevance(
                        searchTerm,
                        card.querySelector('.product-name')?.textContent.toLowerCase() || '',
                        card.querySelector('.product-description')?.textContent.toLowerCase() || ''
                    )
                }))
                .filter(item => item.score > 0)
                .sort((a, b) => b.score - a.score);
        }

        calculateRelevance(searchTerm, name, description) {
            const terms = searchTerm.split(' ');
            return (name.includes(searchTerm) ? 3 : 0) +
                (terms.filter(term => name.includes(term)).length * 2) +
                (description.includes(searchTerm) ? 1 : 0);
        }

        updateDisplay(results) {
            const hasResults = results.length > 0;
            this.elements.noResults?.classList.toggle('hidden', hasResults);

            if (!hasResults) return;

            const container = this.elements.cards[0].parentElement;
            results.forEach(({ card }) => container.appendChild(card));
            this.elements.cards
                .filter(card => !results.find(r => r.card === card))
                .forEach(card => card.classList.add('hidden'));
        }

        toggleSections(hide) {
            [this.elements.hero, this.elements.newsletter].forEach(section =>
                section?.classList.toggle('hidden', hide)
            );
        }

        resetView() {
            this.toggleSections(false);
            this.elements.cards.forEach(card => card.classList.remove('hidden'));
            this.elements.noResults?.classList.add('hidden');
        }
    }

    new ProductSearch(elements);
});