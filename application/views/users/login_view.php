<div class='container text-center'>
    <h3 class='mt-4 mb-4'>Войти</h3>
    <form class='login-form mx-auto' method="POST" action='' id='login-form'>
        <input type="email" class="d-block mx-auto btn-width mb-2 p-2" id="login-form__email-input" name='email' placeholder="email">
        <input type="password" class="d-block mx-auto btn-width mb-2 p-2" id="login-form__password-input" name='password' placeholder="пароль">
        <input type="submit" class='d-block mx-auto btn-width btn mb-2 btn-bg-C4C4C4 text-white p-3' value="Войти" disabled id='login-form__login-btn'>
        <a href="/"><p class='d-block btn-width mx-auto btn-bg-C4C4C4 text-white p-3'>Назад</p></a>
        <input type="hidden" name="login">
        <input type="hidden" id="input-csrf" value=<?php echo $data['csrfToken']; ?>>
    </form>
    <p class='w-50 mx-auto fw-bolder text-dark-red d-none pt-2 mb-0' id='login-error'>Пользователь уже существует</p>
</div>
