<div class='position-relative'>
    <div class='container chat-container d-flex p-0 bg-c4c4c4 font-roboto'>
        <div class='contacts pt-2 ps-2 pe-2'>
            <div class='position-relative mb-2'>
                <input type="text" class='find-contacts-input w-100 form-control' placeholder='поиск контакта'
                    id='find-contacts-input'>
                <button class='position-absolute bg-white text-dark top-0 end-0 border-0 mt-1 me-2'
                        id='reset-find-contacts-btn' title='сбросить фильтр'>&#9747;
                </button>
            </div>
            <div class='h-50 border-bottom border-dark overflow-auto'>
                <div id='contacts'></div>
                <div class='btn-resend-block' id='btn-resend-block'>
                    <button class='btn-resend' id='btn-resend' disabled>
                        <div class='btn-resend__img-block'><img src="application/images/resend.png" class='img' title='Переслать'></div>
                        <span class='btn-resend__name'>Переслать</span>
                    </button>
                    <button class='btn-resend' id='btn-resend-reset'>
                        <div class='btn-resend__img-block'><img src="application/images/cancel.png" class='img' title='Отменить'></div>
                        <span class='btn-resend__name'>Отменить</span>
                    </button>
                </div>
            </div>
            <div class='groups pt-2 text-center' id='group-chats'></div>
        </div>

        <div class='messages-container border-start border-end border-dark d-flex flex-column pe-2'>
            <p class='messages-container__title'>
                <span id='chat-title'>Выберите чат</span>
                <span class='chat-username' id='chat-username'></span>
            </p>

            <div class='messages' id='messages'></div>

            <p class="messages-container__system" id="message-system">Проверка подключения</p>
            <div class='input-group d-flex justify-content-between pb-2'>
                <textarea class="messages-container__message-input input-group-prepend border-0 form-control" rows='2'placeholder='Сообщение' id='message-input' disabled></textarea>
                <button type="submit" class='send-btn-img' title='Отправить' id="send-msg-btn" disabled><img src="application/images/sendbtn.png"></button>
            </div>
        </div>

        <div class='options-container pt-2'>
            <div class='option'>
                <a href="/profile" class='option-link'>
                    <div class='option-img-block'><img src="application/images/profile.png" class='img' title='Профиль'>
                    </div>
                    <span class='option-name'>Профиль</span>
                </a>
            </div>
            <div class='option'>
                <div class='option-link'>
                    <div class='option-img-block'><img src="application/images/settings.png" class='img' title='Настройки'>
                    </div>
                    <span class='option-name'>Настройки</span>
                </div>
            </div>
            <div class='option'>
                <div class='option-link' id='create-group-option'>
                    <div class='option-img-block'><img src="application/images/group.png" class='img'
                                                    title='Создать группу'></div>
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

    <!-- окно ошибки -->
    <div class='frame-error position-absolute top-0 left-0 p-2 ms-2 mt- text-wrap text-break text-danger fw-bold' id='frame-error'></div>
</div>

<!-- контектсное меню сообщения -->
<div class='context-menu' id='msg-context-menu'>
    <ul class='list-group m-0'>
        <li class='list-group-item' id='edit-msg'>Редактировать</li>
        <li class='list-group-item' id='resend-msg'>Переслать</li>
        <li class='list-group-item' id='remove-msg'>Удалить</li>
    </ul>
</div>

<!-- контекстное меню контакта -->
<div class='context-menu' id='contact-context-menu'>
    <ul class='list-group m-0'>
        <li class='list-group-item' id='contact-notice-edit'>Отключить уведомления</li>
        <li class='list-group-item' id='contact-remove-contact'>Удалить группу</li>
    </ul>
</div>

<!-- CSRF страницы -->
<input type="hidden" id="input-csrf" value=<?= $data['csrfToken'] ?>>

<script type='text/javascript' src="http://messenger.local/application/js/ChatWebsocket.js"></script>

<script type='text/javascript' src="http://messenger.local/application/js/contex-menu/ContexMenu.js"></script>
<script type='text/javascript' src="http://messenger.local/application/js/contex-menu/MessageContexMenu.js"></script>
<script type='text/javascript' src="http://messenger.local/application/js/contex-menu/ContactContexMenu.js"></script>

<script type='text/javascript' src="http://messenger.local/application/js/chat/TemplateContainer.js"></script>
<script type='text/javascript' src="http://messenger.local/application/js/chat/MessageContainer.js"></script>
<script type='text/javascript' src="http://messenger.local/application/js/chat/ContactContainer.js"></script>
<script type='text/javascript' src="http://messenger.local/application/js/chat/GroupContainer.js"></script>
<script type='text/javascript' src="http://messenger.local/application/js/chat/chat.js"></script>