<div class='container chat-container d-flex p-0 bg-c4c4c4 font-roboto'>
    <div class='contacts pt-2 ps-2 pe-2'>
        <div class='position-relative mb-2'>
            <input type="text" class='find-contacts-input w-100 form-control' placeholder='поиск контакта' id='find-contacts-input'>
            <button class='position-absolute bg-white text-dark top-0 end-0 border-0 mt-1 me-2' id='reset-find-contacts-btn' title='сбросить фильтр'>&#9747;</button>
        </div>
        <div class='h-50 border-bottom border-dark overflow-auto' id='contacts'></div>

        <div id='group-chats' class='groups pt-2 text-center'>
            <p class='groups__item'>Групповой чат 1</p>
            <p class='groups__item'>Групповой чат 2</p>
            <p class='groups__item'>Групповой чат 3</p>
            <p class='groups__item'>Групповой чат 4</p>
            <p class='groups__item'>Групповой чат 5</p>
        </div>
    </div>
    
    <div class='messages-container border-start border-end border-dark d-flex flex-column pe-2'>
        <p class='messages-container__title'>
            <span id='contact-title'>Выберите чат</span>
            <span id='contact-username'></span>
        </p>
        <input type="hidden" id='id-chat'>
        <div class='messages overflow-auto' id='messages'></div>
        <p class="message-system" id="message-system">Проверка подключения</p>                                   
        <div class='input-group d-flex justify-content-between pb-2'>
            <textarea class="input-group-prepend resize-none border-0 form-control" rows='2' placeholder='Наберите ваше сообщение здесь' id='message-input' disabled></textarea>
            <button type="submit" class='send-btn-img' title='Отправить' id="send-msg-btn" disabled><img src="application/images/sendbtn.png"></button>
        </div>
    </div>

    <div class='options-container pt-2'>
        <div class='option'>
            <a href="/profile" class='option-link'>
            <div class='option-img-block'><img src="application/images/profile.png" class='img' title='Профиль'></div>
            <span class='option-name'>Профиль</span>
            </a>
        </div>
        <div class='option'>
            <a href="" class='option-link'>
            <div class='option-img-block'><img src="application/images/settings.png" class='img' title='Настройки'></div>
            <span class='option-name'>Настройки</span>
            </a>
        </div>
        <div class='option'>
            <div class='option-link'>
                <div class='option-img-block'><img src="application/images/group.png" class='img' title='Создать группу'></div>
                <span class='option-name'>Создать группу</span>
            </div>
        </div>
        <div class='option'>
            <a href="/Main?logout=true" class='option-link'>
                <div class='option-img-block'><img src="application/images/exit.png" class='img' title='Выход'></div>
                <span class='option-name'>Выйти</span>
            </a>
        </div>

    </div>
</div>

<input type="hidden" id='publicUsername' value=<?=$data['publicUsername']?>>