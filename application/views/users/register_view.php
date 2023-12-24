<div class='container text-center mt-4'>
    <form class='reg-form' method="POST" action='user/store' id='reg-form'>
        <input type="hidden" name="registration">
        <input type="hidden" name ='csrf' id="input-csrf" value=<?php echo $data['csrf']; ?>>

        <label for="login-form__email-input" class='text-start btn-width p-1 fw-bolder'>Почта</label>
        <input type="email" class="d-block btn-width mx-auto mb-2 p-2" 
            id="reg-form__email-input" name='email' 
            placeholder='адрес электронной почты' 
            required>

        <label for="login-form__email-input" class='text-start btn-width p-1 fw-bolder'>Пароль</label>
        <input type="password" class="d-block btn-width mx-auto mb-2  p-2" 
            id="reg-form__password1-input" name='password' 
            placeholder="пароль: минимум 8 символов (буквы и цифры)" 
            required>
        <input type="password" class="d-block btn-width mx-auto mb-2 p-2" 
            id="reg-form__password2-input" name='password_confirm' 
            placeholder='подтвердите пароль' 
            required>
        
        <input type="submit" class='d-block btn-width btn mx-auto mb-2 btn-bg-theme text-white p-3 rounded' 
            value="Регистрация" id='reg-form__reg-btn'>
        <a href="/" class='text-decoration-none'>
            <p class='d-block btn-width mx-auto btn-bg-theme text-white p-3 rounded'>Назад</p>
        </a>
        
        <?php if (isset($data['error'])) { ?>
            <p class='w-50 mx-auto fw-bolder text-dark-red pt-2 mb-0' id='login-error'><?php echo $data['error']; ?></p>
        <?php } ?>
    </form>
</div>