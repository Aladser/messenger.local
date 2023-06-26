const findContactsInput = document.querySelector('#find-contacts-input');
const contacts = document.querySelector('#contacts');


// ПОКАЗ КОНТАКТОВ-ЧАТОВ ПОЛЬЗОВАТЕЛЯ
function showContacts(findInput, contacts){
    fetch(`/get-contacts`, {method: 'get'}).then(r=>r.json()).then(data => {
        findInput.value = '';
        contacts.innerHTML = '';

        if(data != null){
            data.forEach(element => createContact(element));
        }
    }); 
}
showContacts(findContactsInput, contacts); // показ контактов-чатов при загрузке страницы
document.querySelector('#reset-find-contacts-input').onclick = () => showContacts(findContactsInput, contacts); // отмена поиска контакта и отображение контактов


// ДОБАВИТЬ КОНТАКТ-ЧАТ ПОЛЬЗОВАТЕЛЮ В БД
function setAddContact(contact){
    return function(){
        fetch(`/add-contact?contact=${contact}`, {method: 'get'}).then(r=>r.text()).then(data=>{
            console.log(data);
        });
    };
}


// ПОИСК КОНТАКТОВ В БД
findContactsInput.addEventListener('input', function(){
    fetch(`/find-contacts?userphrase=${this.value}`, {method: 'get'}).then(r=>r.json()).then(data => {
        contacts.innerHTML = '';

        //  отображение найденных контактов в списке контактов
        if(data != null){
            data.forEach(element => createContact(element));
        }
    });
});

// ОТРИСОВКА КОНТАКТА-ЧАТА
function createContact(element){
    // контейнер контакта
    let contact = document.createElement('div');
    contact.className = 'contact position-relative mb-2';

    // контейнер изображения
    let contactImgDiv = document.createElement('div');
    contactImgDiv.className = 'img-div';

    // фото профиля
    let img = document.createElement('img');
    img.className = 'pe-2 img';
    if(element['user_photo'] == 'ava_profile.png'){
        img.src = 'application/images/ava.png';
    }
    else if(element['user_photo'] == null){
        img.src = 'application/images/ava.png';
    }
    else{
        img.src = `application/data/profile_photos/${element['user_photo']}`; 
    }

    contactImgDiv.appendChild(img);
    contact.appendChild(contactImgDiv);

    // имя контакта
    let name = document.createElement('span');
    name.className = 'text-break';
    name.innerHTML = element['username'];
    contact.appendChild(name);

    contact.onclick = setAddContact(element['username']);

    contacts.appendChild(contact);
}