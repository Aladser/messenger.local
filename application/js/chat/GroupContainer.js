/** Контейнер контактов */
class GroupContainer extends TemplateContainer{
    /** список участников выбранной группы */
    currentGroupContacts = [];

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
    addGroupToList = group => this.list.push({'name': group.name, 'chat': group.chat, 'notice': group.notice});

    /** показать участников группового чата*/
    showGroupRecipients(domElement, discussionid) {
        let urlParams = new URLSearchParams();
        urlParams.set('discussionid', discussionid);
        urlParams.set('CSRF', this.CSRFElement.value);
        fetch('contact/get-group-contacts', {method: 'POST', body: urlParams}).then(r => r.text()).then(data => {
            data = parseJSONData(data);
            if (data === undefined) {
                return;
            }

            // создание DOM-списка участников группового чата
            let prtBlock = document.createElement('div'); // блок, где будут показаны участники группы
            prtBlock.className = 'group__contacts';
            domElement.append(prtBlock);
            this.currentGroupContacts = [];
            // создается список участников группового чата
            data.participants.forEach(prt => {
                prtBlock.innerHTML += `<p class='group__contact'>${prt.publicname}</p>`;
                this.currentGroupContacts.push(prt.publicname);
            });

            // добавить новые кнопки добавления в группу у контактов-неучастников выбранной группы
            contacts.container.querySelectorAll('.contact').forEach(cnt => {
                let cntName = cnt.lastChild.innerHTML;
                if (!this.currentGroupContacts.includes(cntName)) {
                    let plus = document.createElement('div');
                    plus.className = 'contact-addgroup';
                    plus.innerHTML = '+';
                    plus.title = 'добавить в групповой чат';

                    // добавить пользователя в группу
                    plus.onclick = e => {
                        let username = e.target.parentNode.childNodes[1].innerHTML; // имя пользователя
                        e.stopPropagation();    // прекратить всплытие событий

                        let urlParams2 = new URLSearchParams();
                        urlParams2.set('discussionid', discussionid);
                        urlParams2.set('username', username);
                        fetch('contact/create-group-contact', {method: 'POST', body: urlParams2}).then(r => r.text()).then(data => {
                            let isCreated = parseInt(data);
                            if (isCreated === 1) {
                                e.target.parentNode.lastChild.remove();
                                domElement.lastChild.innerHTML += `<p class='group__contact'>${username}</p>`;
                            } else {
                                alert(data);
                                console.log(data);
                            }
                        });
                    }
                    cnt.append(plus);
                }
            });
        });
    };

    /** удаление DOM узлов участников текущего выбранного группового чата */
    removeGroupPatricipants() {
        let groupContactsList = document.querySelector('.group__contacts');
        if (groupContactsList) {
            groupContactsList.remove();
        }
        document.querySelectorAll('.contact-addgroup').forEach(cnt => cnt.remove());
    }
}