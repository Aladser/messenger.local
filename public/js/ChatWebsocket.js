class ChatWebsocket
{
    errorPrg = document.querySelector("#message-system");
    publicUsername = document.querySelector('#clientuser').getAttribute('data-clientuser-publicname');
    chat = document.querySelector("#messages");
    messageInput = document.querySelector("#message-input");
    selectedMessage = null;
    forwardedMessageRecipientName = null;
    chatOpenedType = null;
    chatOpenedName = null;

    constructor(websocketAddr, contacts, groups, messages)
    {
        this.websocket = new WebSocket(websocketAddr);
        this.websocket.onerror = this.onError;
        this.websocket.onmessage = e => this.onMessage(e);
        this.contacts = contacts;
        this.groups = groups;
        this.messages = messages;
        this.baseSiteName= window.location.origin;
    }

    // получение ошибок вебсокета
    onError() {
        this.errorPrg.innerHTML = 'Ошибка подключения к серверу';
    }

    onMessage(e)
    {
        let data = JSON.parse(e.data);
        
        if (data.onconnection) {
            // --- сообщение от сервера о подключении текущего пользователя

            // Передача имени пользователя и ID подключения текущего пользователя серверу
            this.websocket.send(JSON.stringify({
                'messageOnconnection': 1,
                'author': clientUsername,
                'wsId': data.onconnection
            }));
        } else if (data.messageOnconnection) {
            // --- сообщение себе и контактам пользователя о подключении клиента

            if (data.author) {
                let username = data.author === this.publicUsername ? 'Вы' : data.author;
                this.errorPrg.textContent = `${username} в сети`;
            } else {
                // ошибки подключения
                this.errorPrg.textContent = `${data.systeminfo}`;
            }
        } else if (data.offconnection && data.user != null) {
            // --- сообщение контактам пользователя об отключении

            this.errorPrg.textContent = `${data.user} не в сети`;
        } else {
            // --- сообщение с сервера
            console.clear();
            console.log(data);

            // уведомления о новых сообщениях чатов
            if ((data.message_type === 'NEW' || data.message_type === 'FORWARD') && data.author_name !== this.publicUsername) {
                let senderDOMNode = document.querySelector(`article[title='${data.author_name}']`);
                // визуальное уведомление
                if (this.chatOpenedName !== data.chat_name) {
                    senderDOMNode.classList.add('isnewmessage');
                }
                // звуковое уведомление
                // сделано специально множественное создание объектов звука
                let senderChatNotice = senderDOMNode.getAttribute('data-notice');
                if (senderChatNotice == 1) {
                    let notice = new Audio(this.baseSiteName + '/public/notice.wav');
                    notice.autoplay = true;
                }
            }
    
            // сообщения открытого чата
            if (this.chatOpenedName === data.chat_name) {
                switch(data.message_type) {
                    case 'NEW':
                        // новое сообщение
                        this.messages.createDOMNode(data, this.publicUsername);
                    case 'EDIT':
                        // изменение сообщения
                        let messageDOMNode = document.querySelector(`article[data-msg="${data.message_id}"]`);
                        messageDOMNode.querySelector('.msg__text').textContent = data.message_text;
                        break;
                    case 'REMOVE':
                         // удаление сообщения
                        let messageDOMElem = document.querySelector(`article[data-msg="${data.message_id}"]`);
                        messageDOMElem.remove();
                        break;
                    case 'FORWARD':
                        console.log(data);
                    default:
                        throw 'Неверный тип сообщения';
                }
            }
        }
    }

    /** Отправить сообщение на сервер
     * @param message_text текст сообщения
     * @param message_type тип сообщения: NEW, EDIT, REMOVE или FORWARD
     */
    sendData(message_text, message_type, chat_type = false, chat_name = false) {
        // проверка сокета
        if (this.websocket.readyState !== 1) {
            alert('sendData(msgType): вебсокет не готов к обмену сообщениями');
            throw 'sendData(msgType): вебсокет не готов к обмену сообщениями';
        }
    
        // отправка сообщения на сервер
        if (message_text !== '') {
            let data = {
                'message_type': message_type,
                'message_text': message_text,
                'chat_type': this.chatOpenedType,
                'chat_name': this.chatOpenedName,
            };
            // для старых сообщений добавляется id сообщения
            if (['EDIT', 'REMOVE'].includes(message_type)) {
                data.message_id = parseInt(this.selectedMessage.getAttribute('data-msg'));
            }
    
            if (message_type === 'FORWARD') {
                data.message_id = message_text;
                data.chat_type = chat_type;
                data.chat_name = chat_name;
                delete data.message_text;
            }
            this.websocket.send(JSON.stringify(data));
        }
        this.messageInput.value = '';
    }

    // получить текст выбранного сообщения
    getSelectedMessageText = () => this.selectedMessage.querySelector('.msg__text').innerHTML;
    // получить автора выбранного сообщения
    getSelectedMessageAuthor = () => this.selectedMessage.getAttribute('data-author');
    // проверить - сообщение переслано?
    isForwardedSelectedMessage = () => this.selectedMessage.getAttribute('data-forward') == 1;
}