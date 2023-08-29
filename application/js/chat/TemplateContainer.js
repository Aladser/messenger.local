class TemplateContainer {
    list = [];

    constructor(container, errorPrg, CSRFElement) {
        this.container = container;
        this.errorPrg = errorPrg;
        this.CSRFElement = CSRFElement;
    }

    /** очистить контейнер */
    clear() {
        this.container.innerHTML = '';
    }
}