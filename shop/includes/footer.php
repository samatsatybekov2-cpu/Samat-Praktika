<?php $sn=SHOP_NAME;$sl=SHOP_LETTER;$ss=SHOP_SLOGAN; ?>
<footer class="footer">
  <div class="container">
    <div class="foot-grid">
      <div class="foot-brand">
        <div class="logo"><span class="logo__icon"><?=$sl?></span><span class="logo__name"><?=htmlspecialchars($sn)?></span></div>
        <p><?=htmlspecialchars($ss)?></p>
      </div>
      <div class="foot-col"><h5>Покупателям</h5><a href="#">Как заказать</a><a href="#">Доставка и оплата</a><a href="#">Возврат товара</a></div>
      <div class="foot-col"><h5>Компания</h5><a href="#">О нас</a><a href="#">Контакты</a><a href="#">Вакансии</a></div>
      <div class="foot-col"><h5>Связаться</h5><a href="tel:+77001234567">+7 700 123-45-67</a><a href="mailto:info@shop.kz">info@shop.kz</a><p style="font-size:12px;opacity:.5;margin-top:8px">Пн–Вс 09:00–22:00</p></div>
    </div>
    <div class="foot-bot">© <?=date('Y')?> <?=htmlspecialchars($sn)?>. Все права защищены.</div>
  </div>
</footer>

<div class="modal-ov" id="authModal">
  <div class="modal">
    <button class="modal__x" id="authClose">✕</button>
    <div class="modal__tabs">
      <button class="mtab on" data-tab="login">Вход</button>
      <button class="mtab" data-tab="register">Регистрация</button>
    </div>
    <div id="tabLogin">
      <div class="mhead"><div class="mlogo"><?=$sl?></div><h2>Добро пожаловать!</h2><p>Войдите в <?=htmlspecialchars($sn)?></p></div>
      <div class="fg"><label>Email</label><input type="email" id="loginEmail" placeholder="example@mail.com" autocomplete="email"></div>
      <div class="fg"><label>Пароль</label><div class="eye-wrap"><input type="password" id="loginPassword" placeholder="Ваш пароль"><button class="eye-btn" data-target="loginPassword" type="button">👁</button></div></div>
      <div class="frow"><label class="chk"><input type="checkbox"> Запомнить</label><a href="#" class="flink">Забыли пароль?</a></div>
      <div class="fmsg" id="loginMsg"></div>
      <button class="btn btn-primary btn-full" id="loginBtn"><span>Войти</span><div class="spin"></div></button>
      <p class="mswitch">Нет аккаунта? <a href="#" data-switch="register">Зарегистрироваться</a></p>
    </div>
    <div id="tabRegister" style="display:none">
      <div class="mhead"><div class="mlogo"><?=$sl?></div><h2>Создать аккаунт</h2><p>Присоединяйтесь к <?=htmlspecialchars($sn)?></p></div>
      <div class="fg"><label>Имя</label><input type="text" id="regName" placeholder="Ваше имя"></div>
      <div class="fg"><label>Email</label><input type="email" id="regEmail" placeholder="example@mail.com"></div>
      <div class="fg"><label>Пароль</label><div class="eye-wrap"><input type="password" id="regPassword" placeholder="Минимум 6 символов"><button class="eye-btn" data-target="regPassword" type="button">👁</button></div><div class="pw-bar"><div class="pw-bar__fill" id="pwBar"></div></div></div>
      <div class="fg"><label>Повтор пароля</label><div class="eye-wrap"><input type="password" id="regConfirm" placeholder="Повторите пароль"><button class="eye-btn" data-target="regConfirm" type="button">👁</button></div></div>
      <div class="fmsg" id="regMsg"></div>
      <button class="btn btn-primary btn-full" id="registerBtn"><span>Зарегистрироваться</span><div class="spin"></div></button>
      <p class="mswitch">Есть аккаунт? <a href="#" data-switch="login">Войти</a></p>
    </div>
  </div>
</div>
<div class="toast" id="toast"></div>