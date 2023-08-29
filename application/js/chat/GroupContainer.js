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

    /** удалить группу из списка групп пользователя */
    remove(group) {
        let urlParams = new URLSearchParams();
        urlParams.set('name', group.title);
        urlParams.set('type', group.className === 'group' ? 'group' : 'contact');
        urlParams.set('CSRF', this.CSRFElement.value);

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
    addGroupToList = group => this.groupList.push({'name': group.name, 'chat': group.chat, 'notice': group.notice});
}