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
                this.groupOpened.querySelector('.group__contacts').classList.add('d-none');
                this.groupOpened = false;
                // удаление кнопок добавления
                let btnArray = document.querySelectorAll('.btn-add-to-group'); 
                for (let i=0; i<btnArray.length; i++) {
                    btnArray[i].remove();
                }
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
                    contactsDomList[i].innerHTML += "<span class='btn-add-to-group px-1 position-absolute end-0'>+</span>";
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
            // удаление кнопок добавления
            let btnArray = document.querySelectorAll('.btn-add-to-group'); 
            for (let i=0; i<btnArray.length; i++) {
                btnArray[i].remove();
            }
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

    /** установить функцию добавления пользователя в группу */
    setAddUserToGroup(userName, groupName) {
        return function () {
            console.log(userName);
            console.log(groupName);
        }
    }

    remove(group) {
        let urlParams = new URLSearchParams();
        urlParams.set('name', group.title);
        urlParams.set('type', 'group');
        urlParams.set('CSRF', this.CSRFElement.content);

        fetch('/contact/remove', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
            console.log(data);
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

    /** добавить в фронт-список групп */
    addGroupToList = group => this.list.push({'name': group.name, 'chat': group.chat, 'notice': group.notice});
}