class ContexMenu 
{
    constructor(contextMenuDOM)
    {
        this.contextMenuDOM = contextMenuDOM;
    }

    /** показать контекстное меню */
    show(event)
    {
        this.contextMenuDOM.style.left = event.pageX + 'px';
        this.contextMenuDOM.style.top = event.pageY + 'px';
        this.contextMenuDOM.style.display = 'block';
    }

    /** скрыть контекстное меню*/
    hide()
    {
        this.contextMenuDOM.style.left = '0px';
        this.contextMenuDOM.style.top = '1000px';
        this.contextMenuDOM.style.display = 'none';
    }
}
