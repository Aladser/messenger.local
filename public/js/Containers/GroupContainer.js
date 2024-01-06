/** Контейнер контактов */
class GroupContainer extends TemplateContainer{
    /** список участников выбранной группы */
    currentGroupParticipants = [];

    constructor(container, errorPrg, CSRFElement) {
        super(container, errorPrg, CSRFElement);
        this.get().forEach(contact => {
            let element = {
                'name': contact.title, 
                'chat': contact.id.substring(contact.id.lastIndexOf('-')+1), 
                'notice': contact.getAttribute('data-notice')
            };
            this.list.push(element);
        });
        this.groupOpened = false;
    }

    get() {
        return this.container.querySelectorAll('.group');
    }

    /** переключить видимость членов группы */
    click(group, contactContainer) {
        // dom-элементы контактов
        let personalChatDomList = contactContainer.get();
        // скрыть членов другой открытой группы
        if(this.groupOpened) {
            if (this.groupOpened.title != group.title) {
                this.switchGroupUsersVisibility(this.groupOpened);
                this.removeAddUserToGroupButtons();
                this.groupOpened = false;
            }
        }
        // dom-элемент списка участников группы
        let paricipantList = group.querySelector('.group__contacts');
        
        if (paricipantList.classList.contains('d-none')) {
            // ----- если группа была закрытой -----
            this.groupOpened = group;
            paricipantList.classList.remove('d-none');
            // список участников открытой группы
            paricipantList.querySelectorAll('.group__contact').forEach(contact => {
                this.currentGroupParticipants.push(contact.textContent);
            });
            // поиск контактов, которых нет в открытой группе
            for (let i = 0; i < personalChatDomList.length; i++) {
                if (!this.currentGroupParticipants.includes(personalChatDomList[i].title)) {
                    personalChatDomList[i].innerHTML += "<span class='btn-add-to-group px-1 position-absolute end-0' title='добавить в группу'>+</span>";
                }
            }
            // события для кнопок добавления
            contactContainer.container.querySelectorAll('.btn-add-to-group').forEach(btn => {
                let userName = btn.closest('.contact').title;
                let groupName = this.groupOpened.title;
                btn.onclick = this.setAddUserToGroup(userName, groupName);
            });
        } else {
            // ----- если группа была открытой -----
            this.groupOpened = false;
            paricipantList.classList.add('d-none');
            this.currentGroupParticipants = [];
            this.removeAddUserToGroupButtons();
        }
    }

    add() {
        let requestData = new URLSearchParams();
        requestData.set('CSRF', this.CSRFElement.content);
        requestData.set('type', 'group');

        let process = (data) => {
            console.clear();
            console.log(data);
            try {
                let group = JSON.parse(data);
                this.createDOM(group, 'START');
                this.addGroupToList({'name': group.name, 'chat':group.chat, 'notice': 1});
                return group.name;
            } catch (err) {
                console.log(err);
                console.log();
                console.log(data);
                return null;
            }
        };

        ServerRequest.execute(
            'chat/add',
            process,
            'post',
            null,
            requestData
        );
    }

    remove(group) {
        let urlParams = new URLSearchParams();
        urlParams.set('CSRF', this.CSRFElement.content);
        urlParams.set('type', 'group');
        urlParams.set('group_name', group.title);

        let process = (data) => {
            console.clear();
            console.log(data);
            try {
                data = JSON.parse(data);
                if (parseInt(data.result) > 0) {
                    group.remove();
                }
            } catch (err) {
                console.log(err);
                this.errorPrg.innerHTML = data;
            }
        };

        ServerRequest.execute(
            'chat/remove',
            process,
            'post',
            null,
            urlParams
        );
    }

    // создать DOM-элемент группы
    createDOM(group) {
        let groupDOMElement = `
            <article class="group text-white" id="group-${group.id}" title="${group.name}" data-notice="1">
                ${group.name}                      
                <div class="group__contacts d-none">
                    <p class="group__contact">${group.author}</p>
                </div>
            </article>`;
        this.container.innerHTML += groupDOMElement;
    }

    /** возвращает функцию добавления пользователя в группу
     * 
     * @param {*} userName имя контакта
     * @param {*} groupName название группы
     * @param {*} removeButtonsFunction функция Удалить кнопки добавления пользователей в группу
     * @returns 
     */
    setAddUserToGroup(username, groupName) {
        let csrf = this.CSRFElement.content;
        let container = this.container;
        return function () {
            let requestData = new URLSearchParams();
            requestData.set('username', username);
            requestData.set('chat_name', groupName);
            requestData.set('CSRF', csrf);

            let process = (data) => {
                data = JSON.parse(data);
                if (data.result == 1) {
                    let group = container.querySelector(`article[title='${data.group}']`);
                    console.log(group);
                    // добавление в список участников группы
                    group.querySelector('.group__contacts').innerHTML += `<p class="group__contact">${data.user}</p>`;
                    // удаление кнопки добавления в группу у пользователя
                    document.querySelector(`article[title="${data.user}"]`).querySelector('.btn-add-to-group').remove();
                } else {
                    this.errorPrg.innerHTML = data;
                }
            }

            ServerRequest.execute(
                '/chat/create-group-contact',
                process,
                "post",
                null,
                requestData
            );
        }
    }

    /** удалить кнопки добавления пользователей в группу */
    removeAddUserToGroupButtons() {
        // удаление кнопок добавления
        let btnArray = document.querySelectorAll('.btn-add-to-group'); 
        for (let i=0; i<btnArray.length; i++) {
            btnArray[i].remove();
        }
    }

    /** переключить видимость пользователей группы */
    switchGroupUsersVisibility(group = this.groupOpened) {
        if (group.classList.contains('d-none')) {
            group.querySelector('.group__contacts').classList.remove('d-none');
        } else {
            group.querySelector('.group__contacts').classList.add('d-none');
        }
    }

    /** добавить в фронт-список групп */
    addGroupToList(group) {
        this.list.push({'name': group.name, 'chat': group.chat, 'notice': group.notice})
    };
}