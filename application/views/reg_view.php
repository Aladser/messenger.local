<div class='container text-center'>
    <h3 class='mt-4 mb-4'>Регистрация нового пользователя</h3>

    <form class='reg-form' method="POST" action='/application/models/reg_model.php' id='reg-form'>
        <input type="hidden" name="registration">

        <div class='position-relative w-25 mx-auto'>
            <input type="email" class="w-100 mb-2" id="reg-form__email-input" name='email' placeholder="email">
            <p class='input-clue' id='reg-form__emai-clue'>введите адрес электронной почты</p>
        </div>

        <div class='position-relative w-25 mx-auto'>
            <input type="password" class="w-100 mb-2" id="reg-form__password1-input" name='password'
                   placeholder="пароль (минимум 6 символов)">
            <p class='input-clue' id='reg-form__password1-clue'>пароль должен обязательно содержать латинские заглавные
                и прописные буквы, цифры. Только латинские буквы</p>
        </div>

        <div class='position-relative w-25 mb-2 mx-auto'>
            <input type="password" class="w-100 mb-2" id="reg-form__password2-input" placeholder="подтвердите пароль">
            <p class='input-clue' id='reg-form__password2-clue'>пароли не совпадают</p>
        </div>

        <div>
            <input type="submit" class='btn w-25 mb-2 btn-bg-C4C4C4 text-white' value="Регистрация" disabled
                   id='reg-form__reg-btn'>
            <a href="/main" class='text-decoration-none'>
                <div class='mx-auto w-25 btn-bg-C4C4C4 text-white p-2'>Назад</div>
            </a>
        </div>

        <p class='w-25 mx-auto fw-bolder text-dark-red d-none' id='reg-error'>Пользователь уже существует</p>
        <input type="hidden" id="input-csrf" value=<?= $data['csrfToken'] ?>>
    </form>
</div>

<script type='text/javascript' src="http://messenger.local/application/js/validation.js"></script>
