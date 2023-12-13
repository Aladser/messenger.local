<div class='container text-center mt-4'>
    <form class='reg-form' method="POST" id='reg-form'>
        <input type="hidden" name="registration">
        <input type="hidden" name ='csrf' id="input-csrf" value=<?php echo $data['csrfToken']; ?>>

        <label for="login-form__email-input" class='text-start btn-width p-1 fw-bolder'>Почта</label>
        <input type="email" class="d-block btn-width mx-auto mb-2 p-2" id="reg-form__email-input" name='email' placeholder='адрес электронной почты' required>

        <label for="login-form__email-input" class='text-start btn-width p-1 fw-bolder'>Пароль</label>
        <input type="password" class="d-block btn-width mx-auto mb-2  p-2" id="reg-form__password1-input" name='password' placeholder="пароль минимум 8 символов: буквы и цифры" required>
        <input type="password" class="d-block btn-width mx-auto mb-2 p-2" id="reg-form__password2-input" name='password_confirm' placeholder='подтвердите пароль' required>
        
        <input type="submit" class='d-block btn-width btn mx-auto mb-2 btn-bg-C4C4C4 text-white p-3' value="Регистрация" id='reg-form__reg-btn'>
        <a href="/" class='text-decoration-none'><p class='d-block btn-width mx-auto btn-bg-C4C4C4 text-white p-3'>Назад</p></a>
        
        <p class='d-block btn-width mx-auto fw-bolder text-dark-red d-none' id='reg-error'>Пользователь уже существует</p>
    </form>
</div>