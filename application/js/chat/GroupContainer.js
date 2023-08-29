/** Контейнер контактов */
class GroupContainer extends TemplateContainer{
    show() {
        fetch('chat/get-groups').then(r => r.text()).then(data => {
        data = parseJSONData(data);
            if (data !== undefined) {
                this.groupList = [];
                data.forEach(group => {
                    this.addGroupToList({'name': group.name, 'chat': group.chat, 'notice': group.notice});
                    this.add(group);
                });
            }
        });
    }

    add(group, place = 'END') {
        let groupsItem = document.createElement('div');
        groupsItem.className = 'group';
        groupsItem.title = group.name;
        groupsItem.innerHTML = group.name;
        groupsItem.addEventListener('click', setContactOrGroupClick(groupsItem, group.chat, 'discussion'));
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

    /** удалить контакт из списка контактов */
    remove() {
    }

    /** очистить контейнер */
    clear = () => this.container.innerHTML = '';
    /** добавить в фронт-список групп */
    addGroupToList = group => this.groupList.push({'name': group.name, 'chat': group.chat, 'notice': group.notice});
}