/*
<div class="group" title="Групповой чат 31108" data-notice="1">Групповой чат 31108</div>
 */

/** Контейнер контактов */
class GroupContainer extends TemplateContainer{
    /** список участников выбранной группы */
    currentGroupContacts = [];

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
    }

    get() {
        return this.container.querySelectorAll('.group');
    }

    show() {
        fetch('chat/get-groups').then(r => r.text()).then(data => {
        data = parseJSONData(data);
            if (data !== undefined) {
                this.list = [];
                data.forEach(group => {
                    this.addGroupToList({'name': group.name, 'chat': group.chat, 'notice': group.notice});
                    this.add(group);
                });
            }
        });
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
        urlParams.set('type', group.className === 'group' ? 'group' : 'contact');
        urlParams.set('CSRF', this.CSRFElement.content);

        fetch('/contact/remove-contact', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
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