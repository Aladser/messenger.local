
// ***** Контекстное меню *****
class MessageContexMenu extends ContexMenu
{
    messageInput = document.querySelector("#message-input");
    forwardBtnBlock = document.querySelector('#btn-resend-block');
    option = false;

    constructor(contexMenuDOM, chatWS)
    {
        super(contexMenuDOM);
        this.chatWS = chatWS;
        
        this.editBtn = contexMenuDOM.querySelector('#edit-msg');
        this.removeBtn = contexMenuDOM.querySelector('#remove-msg');
        this.forwardBtn = contexMenuDOM.querySelector('#resend-msg');
        this.editBtn.onclick = () => this.editMessage();
        this.removeBtn.onclick = () => this.removeMessage();
        this.forwardBtn.onclick = () => this.forwardMessage();
    }

    /** изменить сообщение */
    editMessage()
    {
        this.option = 'EDIT';
        this.messageInput.value = this.chatWS.getSelectedMessageText();
        this.messageInput.focus();
        this.hide();
    }

    /** переотправить сообщение */
    forwardMessage()
    {
        this.option = 'FORWARD';
        this.forwardBtnBlock.classList.add('btn-resend-block_active');
        this.hide();
    }
        
    /** удалить сообщение  */
    removeMessage()
    {
        let msg = this.chatWS.getSelectedMessageText();
        this.chatWS.sendData(msg, 'REMOVE');
        this.chatWS.selectedMessage = null;
        this.hide();
    }
}
