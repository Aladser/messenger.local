// ***** Контекстное меню *****
class MessageContexMenu 
{
    /** показать контекстное меню */
    showContextMenu(contextMenu, event)
    {
        contextMenu.style.left = event.pageX + 'px';
        contextMenu.style.top = event.pageY + 'px';
        contextMenu.style.display = 'block';
    }

    /** скрыть контекстное меню*/
    hideContextMenu()
    {
        msgContextMenu.style.left = '0px';
        msgContextMenu.style.top = '1000px';
        msgContextMenu.style.display = 'none';
        contactContextMenu.style.left = '100px';
        contactContextMenu.style.top = '1000px';
        contactContextMenu.style.display = 'none';
    }

    /** контекстное меню: изменить сообщение */
    editMessageContextMenu()
    {
        isEditMessage = true;
        hideContextMenu();
        messageInput.value = websocket.getSelectedMessageText();
        messageInput.focus();
    }

    /** контекстное меню: удалить сообщение  */
    removeMessageContextMenu()
    {
        let msg = websocket.getSelectedMessageText();
        websocket.sendData(msg, 'REMOVE');
        websocket.setSelectedMessage(null);
        hideContextMenu();
    }

    /** контекстное меню: переотправить сообщение */
    forwardMessageContextMenu()
    {
        hideContextMenu();
        isForwaredMessage = true;
        forwardBtnBlock.classList.add('btn-resend-block_active');
    }

    /** контекстное меню: включить/отключить уведомления */
    editNoticeShowContextMenu()
    {
        // создание пакета с id чата, значением статуса показа уведомлений
        let data = {};

        if (selectedContact.className === 'group') {
            // поиск выбранного группового чата
            data.chat = groupList.find(el => el.name === selectedContact.title).chat;
        } else {
            //  поиск выбранного контакта
            let name = selectedContact.querySelector('.contact__name').innerHTML;
            data.chat = contactList.find(el => el.name === name).chat;
        }
        data.notice = selectedContact.getAttribute('data-notice') == 1 ? 0 : 1; //инвертирование значения. Это значение будет записано в БД
        hideContextMenu();

        // отправка данных на сервер
        let urlParams = new URLSearchParams();
        urlParams.set('chat_id', data.chat);
        urlParams.set('notice', data.notice);
        urlParams.set('username', clientUsername);
        urlParams.set('CSRF', inputCsrf.value);
        // изменяет установленный флаг получения уведомлений
        fetch('/chat/edit-notice-show', {method: 'post', body: urlParams}).then(r => r.text()).then(notice => {
            notice = parseJSONData(notice);
            if (notice === undefined) {
                return;
            } else {
                notice = notice.responce;
            }
            console.log(notice);

            notice = parseInt(notice);
            selectedContact.setAttribute('data-notice', notice);  // меняем атрибут
            let elem;
            if (selectedContact.classList.contains('contact')) {
                // если контакт, то изменяем значение в массиве контактов

                elem = contactList.find(el => el.name === selectedContact.title);
            } else if (selectedContact.className === 'group') {
                // если групповой чат, то изменяем значение в массиве групповых чатов

                elem = groupList.find(el => el.name === selectedContact.title);
            }
            elem.notice = notice;

            // изменение визуального уведомления
            if (notice === 1) {
                selectedContact.querySelector('.notice-soundless').remove();
            } else {
                selectedContact.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
            }
        });
    }
}