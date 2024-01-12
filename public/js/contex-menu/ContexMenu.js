class ContexMenu 
{
    constructor(contexMenuHTMLElement)
    {
        this.contexMenuHTMLElement = contexMenuHTMLElement;
        // элемент клика мыши
        this.clickDOMNode = false;
    }

    /** показать контекстное меню */
    show(event)
    {
        this.contexMenuHTMLElement.style.left = event.pageX + 'px';
        this.contexMenuHTMLElement.style.top = event.pageY + 'px';
        this.contexMenuHTMLElement.style.display = 'block';
        this.clickDOMNode = event.target;
    }

    /** скрыть контекстное меню*/
    hide()
    {
        this.contexMenuHTMLElement.style.left = '0px';
        this.contexMenuHTMLElement.style.top = '1000px';
        this.contexMenuHTMLElement.style.display = 'none';
        this.clickDOMNode = false;
    }
}
