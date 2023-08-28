/** Создатель DOM-элементов чата */
class ChatDOMElementCreator {
    static APP_PATH = "http://messenger.local/application/";

    /** создать DOM-элемент сообщения чата*
     * @param {*} chatDOM DOM чата
     * @param {*} chatType типа чата: диалог или групповой чат 
     * @param {*} data данные сообщения
     * @param {*} clientUsername имя пользователя клиента
     */
    static message(chatContainer, chatType, data, clientUsername) {
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
        chatContainer.append(msgBlock);
    }

    /** создать DOM-элемент контакта списка контактов*/
    static contact(contactContainer, contact)
    {
        // контейнер контакта
        let contactBlock = document.createElement('div');    // блок контакта
        let contactImgBlock = document.createElement('div'); // блок изображения профиля
        let img = document.createElement('img'); // фото профиля
        let name = document.createElement('span'); // имя контакта

        contactBlock.className = 'contact position-relative mb-2';
        contactBlock.title = contact.name;
        contactImgBlock.className = 'profile-img';
        img.className = 'contact__img img pe-2';
        name.className = 'contact__name';

        img.src = (contact.photo === 'ava_profile.png' || contact.photo == null) ? `${this.APP_PATH}images/ava.png` : `${this.APP_PATH}data/profile_photos/${contact.photo}`;
        name.innerHTML = contact.name;
        contactBlock.addEventListener('click', setContactOrGroupClick(contactBlock, contact.name, 'dialog'));
        contactBlock.setAttribute('data-notice', contact.notice);

        contactImgBlock.append(img);
        contactBlock.append(contactImgBlock);
        contactBlock.append(name);
        // добавление значка без уведомлений, если они отключены
        if (contact.notice == 0) {
            contactBlock.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
        }

        contactContainer.append(contactBlock);
    }

    /** создать DOM-элемент группового чата списка групповых чатов
     * @param {*} group БД данные группы
     * @param {*} place куда добавить: START - начало списка, END - конец
     */
    static group(groupsContainer, group, place = 'END')
    {
        let groupsItem = document.createElement('div');
        groupsItem.className = 'group';
        groupsItem.title = group.name;
        groupsItem.innerHTML = group.name;
        groupsItem.addEventListener('click', setContactOrGroupClick(groupsItem, group.chat, 'discussion'));
        groupsItem.setAttribute('data-notice', group.notice);

        if (place === 'START') {
            groupsContainer.prepend(groupsItem);
        } else {
            groupsContainer.append(groupsItem);
        }

        if (group.notice == 0) {
            groupsItem.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
        }
    }
}