class ContexMenu 
{
    constructor(contextMenuDOM, option)
    {
        this.contextMenuDOM = contextMenuDOM;
        this.option = option;
    }

    /** показать контекстное меню */
    show(event)
    {
        this.contextMenuDOM.style.left = event.pageX + 'px';
        this.contextMenuDOM.style.top = event.pageY + 'px';
        this.contextMenuDOm.style.display = 'block';
    }

    /** скрыть контекстное меню*/
    hide()
    {
        this.contextMenuDOM.style.left = '0px';
        this.contextMenuDOM.style.top = '1000px';
        this.contextMenuDOM.style.display = 'none';
    }
}
