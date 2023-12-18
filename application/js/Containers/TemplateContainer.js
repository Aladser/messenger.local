class TemplateContainer {
    list = [];

    constructor(container, errorPrg, CSRFElement) {
        this.container = container;
        this.errorPrg = errorPrg;
        this.CSRFElement = CSRFElement;
        this.baseSiteName= window.location.origin;
    }

    /** очистить контейнер */
    clear() {
        this.container.innerHTML = '';
    }
}