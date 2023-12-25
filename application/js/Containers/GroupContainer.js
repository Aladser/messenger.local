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
    click(group, contactList) {
        // скрыть членов другой открытой группы
        if(this.groupOpened) {
            if (this.groupOpened.id != group.id) {
                this.groupOpened.querySelector('.group__contacts').classList.add('d-none');
                this.groupOpened = false;
            }
        }
        // dom-элемент списка участников группы
        let paricipantList = group.querySelector('.group__contacts');
        
        if (paricipantList.classList.contains('d-none')) {
            paricipantList.classList.remove('d-none');
            paricipantList.querySelectorAll('.group__contact').forEach(contact => {
                this.currentGroupParticipants.push(contact.textContent);
            });
            this.groupOpened = group;
        } else {
            paricipantList.classList.add('d-none');
            this.currentGroupParticipants = [];
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