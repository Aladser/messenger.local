
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
        
        document.querySelector('#edit-msg').onclick = () => this.editMessage();
        document.querySelector('#remove-msg').onclick = () => this.removeMessage();
        document.querySelector('#resend-msg').onclick = () => this.forwardMessage();
    }

    /** контекстное меню: изменить сообщение */
    editMessage()
    {
        this.option = 'EDIT';
        this.messageInput.value = this.chatWS.getSelectedMessageText();
        this.messageInput.focus();
        this.hide();
    }

    /** контекстное меню: переотправить сообщение */
    forwardMessage()
    {
        this.option = 'FORWARD';
        this.forwardBtnBlock.classList.add('btn-resend-block_active');
        this.hide();
    }
        
    /** контекстное меню: удалить сообщение  */
    removeMessage()
    {
        let msg = this.chatWS.getSelectedMessageText();
        this.chatWS.sendData(msg, 'REMOVE');
        this.chatWS.selectedMessage = null;
        this.hide();
    }
}
