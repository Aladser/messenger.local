class ChatDOMElementCreator {
    /** создать DOM-элемент сообщения чата*
     * @param {*} chatDOM DOM чата
     * @param {*} chatType типа чата: диалог или групповой чат 
     * @param {*} data данные сообщения
     * @param {*} clientUsername имя пользователя клиента
     */
    static message(chatDOM, chatType, data, clientUsername) {
        // показ местного времени
        // YYYY.MM.DD HH:ii:ss
        let timeInMs = Date.parse(data.time);
        let newDate = new Date(timeInMs);
        let localTime = newDate.toLocaleString("ru", {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric'
        }).replace(',', '');

        // показ переводов строки на странице
        let brIndex = data.message.indexOf('\n');
        while (brIndex > -1) {
            data.message = data.message.replace('\n', '<br>');
            brIndex = data.message.indexOf('\n');
        }

        let msgBlock = document.createElement('div');
        let msgTable = document.createElement('table');

        msgBlock.className = data.author !== clientUsername ? 'msg d-flex justify-content-end' : 'msg';
        msgTable.className = data.author !== clientUsername ? 'msg__table msg__table-contact' : 'msg__table';
        msgBlock.setAttribute('data-msg', data.msg);
        msgBlock.setAttribute('data-author', data.author);
        msgBlock.setAttribute('data-forward', data.forward);

        if (data.forward == 1 || data.messageType === 'FORWARD') {
            msgTable.innerHTML += `<tr><td class='msg__forward'>Переслано</td></tr>`;
        } // надпись о пересланном сообщении
        msgTable.innerHTML += `<tr><td class="msg__text">${data.message}</td></tr>`; // текст сообщения
        msgTable.innerHTML += `<tr><td class="msg__time">${localTime}</td></tr>`;   // время сообщения
        if (chatType === 'discussion') {
            // показ автора сообщения в групповом чате
            msgTable.innerHTML += `<tr class='msg__tr-author'><td class='msg__author'>${data.author}</td></tr>`;
        }

        msgBlock.append(msgTable);
        chatDOM.append(msgBlock);
    }

    
}