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
        let contactsDomList = contactContainer.get();
        // скрыть членов другой открытой группы
        if(this.groupOpened) {
            if (this.groupOpened.id != group.id) {
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
            for (let i = 0; i < contactsDomList.length; i++) {
                if (!this.currentGroupParticipants.includes(contactsDomList[i].title)) {
                    contactsDomList[i].innerHTML += "<span class='btn-add-to-group px-1 position-absolute end-0' title='добавить в группу'>+</span>";
                }
            }
            // события для кнопок добавления
            contactContainer.container.querySelectorAll('.btn-add-to-group').forEach(btn => {
                let userName = btn.closest('.contact').title;
                let groupName = this.groupOpened.id;
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

    add(group, place = 'END') {
        let groupsItem = document.createElement('div');
        groupsItem.className = 'group text-white';
        groupsItem.title = group.name;
        groupsItem.innerHTML = group.name;
        groupsItem.addEventListener('click', setClick(groupsItem, group.chat, 'discussion'));
        groupsItem.setAttribute('data-notice', group.notice);
        if (group.notice == 0) {
            groupsItem.innerHTML += "<div class='notice-soundless'>&#128263;</div>";
        }

        if (place === 'START') {
            this.container.prepend(groupsItem);
        } else {
            this.container.append(groupsItem);
        }
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
        return function () {
            let discussionId = groupName.substring(groupName.indexOf('-')+1);
            let requestData = new URLSearchParams();
            requestData.set('username', username);
            requestData.set('discussionid', discussionId);
            requestData.set('CSRF', csrf);

            let process = (data) => {
                console.log(data);
                data = JSON.parse(data);
                if (data.result == 1) {
                    let group = document.querySelector('#'+data.group);
                    // добавление в список участников группы
                    group.querySelector('.group__contacts').innerHTML += `<p class="group__contact">${data.user}</p>`;
                    // удаление кнопки добавления в группу у пользователя
                    document.querySelector(`article[title="${data.user}"]`).querySelector('.btn-add-to-group').remove();
                } else {
                    this.errorPrg.innerHTML = data;
                }
            }

            ServerRequest.execute(
                '/contact/create-group-contact',
                process,
                "post",
                null,
                requestData
            );
        }
    }

    remove(group) {
        let urlParams = new URLSearchParams();
        urlParams.set('name', group.title);
        urlParams.set('type', 'group');
        urlParams.set('CSRF', this.CSRFElement.content);

        fetch('/contact/remove', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
            try {
                data = JSON.parse(data);
                if (parseInt(data.response) > 0) {
                    group.remove();
                }
            } catch (err) {
                this.errorPrg.innerHTML = data;
            }
        });
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
    addGroupToList = group => this.list.push({'name': group.name, 'chat': group.chat, 'notice': group.notice});
}