class TemplateContainer {
    /** массив элементов */
    list = [];

    constructor(container, errorPrg, CSRFElement) {
        this.container = container;
        this.errorPrg = errorPrg;
        this.CSRFElement = CSRFElement;
        this.baseSiteName= window.location.origin;
    }

    /** очистить контейнер */
    removeElements() {
        this.container.innerHTML = '';
    }

    /** CSRF-токен */
    getCSRF() {
        return this.CSRFElement.content;
    }
}