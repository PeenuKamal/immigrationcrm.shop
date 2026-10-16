<?php
$meta = meta(['title' => 'Sign in', 'description' => 'Sign in with your work email to write verified reviews.', 'path' => '/signin', 'noindex' => true]);
$step = get('step') === 'code' ? 'code' : 'email';
$email = strtolower(get('email', post('email')));
$next = safe_next(get('next', post('next', '/account')));
$error = null;
$info = null;

$hash = fn(string $em, string $code) => hash('sha256', "$em:$code:" . $GLOBALS['CONFIG']['secret']);

if (is_post()) {
    check_csrf();
    $action = post('action');
    if ($action === 'request') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter an email address like name@yourfirm.com.';
        } elseif (is_free_email_domain(email_domain($email)) && !in_array($email, admin_emails(), true)) {
            $error = 'Use the email address you have at work. Personal addresses like @' . email_domain($email) . " can't show where you work, so we can't verify reviews from them.";
        } else {
            $recent = all('SELECT created_at FROM otp_codes WHERE email = ? AND created_at > UTC_TIMESTAMP() - INTERVAL 1 HOUR ORDER BY created_at DESC', [$email]);
            if ($recent && time() - strtotime($recent[0]['created_at'] . ' UTC') < 60) {
                redirect('/signin?step=code&wait=1&email=' . rawurlencode($email) . '&next=' . rawurlencode($next));
            } elseif (count($recent) >= 5) {
                $error = 'Too many codes requested. Wait an hour, then try again.';
            } else {
                $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                q('INSERT INTO otp_codes (email, code_hash, expires_at) VALUES (?, ?, UTC_TIMESTAMP() + INTERVAL 10 MINUTE)', [$email, $hash($email, $code)]);
                $ok = send_mail($email, "$code is your " . SITE_NAME . ' code', "Your sign-in code is $code\n\nIt expires in 10 minutes. If you didn't request it, you can ignore this email.\n\n— " . SITE_NAME);
                if ($ok) redirect('/signin?step=code&email=' . rawurlencode($email) . '&next=' . rawurlencode($next));
                $error = "We couldn't send the code just now. Check the address and try again in a minute.";
            }
        }
        $step = 'email';
    } elseif ($action === 'verify') {
        $step = 'code';
        $code = preg_replace('/\D/', '', post('code'));
        $otp = one('SELECT * FROM otp_codes WHERE email = ? AND consumed = 0 AND expires_at > UTC_TIMESTAMP() ORDER BY id DESC LIMIT 1', [$email]);
        if (strlen($code) !== 6) $error = 'Enter the 6-digit code from the email.';
        elseif (!$otp) $error = 'That code has expired. Request a new one below.';
        elseif ($otp['attempts'] >= 5) $error = 'Too many wrong attempts. Request a new code below.';
        elseif (!hash_equals($otp['code_hash'], $hash($email, $code))) {
            q('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = ?', [$otp['id']]);
            $error = "That code doesn't match. Check the latest email and try again.";
        } else {
            q('UPDATE otp_codes SET consumed = 1 WHERE id = ?', [$otp['id']]);
            $domain = email_domain($email);
            $isWork = is_free_email_domain($domain) ? 0 : 1;
            $user = one('SELECT * FROM users WHERE email = ?', [$email]);
            if ($user) {
                q('UPDATE users SET email_verified_at = UTC_TIMESTAMP(), is_work_email = ? WHERE id = ?', [$isWork, $user['id']]);
                $uid = (int)$user['id'];
            } else {
                q('INSERT INTO users (email, email_domain, email_verified_at, is_work_email, role) VALUES (?, ?, UTC_TIMESTAMP(), ?, ?)',
                    [$email, $domain, $isWork, in_array($email, admin_emails(), true) ? 'ADMIN' : 'USER']);
                $uid = insert_id();
            }
            login_user($uid);
            refresh_user_reviews(current_user(true));
            redirect($next);
        }
    }
}
?>
<div class="wrap" style="max-width:560px">
  <div class="page-head">
    <h1><?= $step === 'code' ? 'Check your inbox' : 'Sign in with your work email' ?></h1>
    <p><?= $step === 'code' ? 'We sent a 6-digit code to ' . e($email) . '. It expires in 10 minutes.' : "We'll email you a one-time code. No password needed. Your work address is how we confirm you're a practitioner." ?></p>
  </div>
  <div class="panel">
  <?php if ($step === 'code'): ?>
    <?php if (get('wait')): ?><div class="notice warn"><p>A code was sent less than a minute ago. Use that one, or wait a moment to request another.</p></div><?php endif; ?>
    <form method="post" action="/signin?step=code">
      <?= csrf_field() ?><input type="hidden" name="action" value="verify"><input type="hidden" name="email" value="<?= e($email) ?>"><input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field">
        <label for="code">6-digit code</label>
        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" autofocus style="font-size:var(--step-2);letter-spacing:.3em;max-width:220px" <?= $error ? 'aria-invalid="true" aria-describedby="code-err"' : '' ?>>
        <?php if ($error): ?><p id="code-err" class="hint" style="color:var(--cons)"><?= e($error) ?></p><?php endif; ?>
      </div>
      <button class="btn">Sign in</button>
    </form>
    <form method="post" action="/signin" style="margin-top:24px">
      <?= csrf_field() ?><input type="hidden" name="action" value="request"><input type="hidden" name="email" value="<?= e($email) ?>"><input type="hidden" name="next" value="<?= e($next) ?>">
      <p class="small muted" style="margin-bottom:0">No email? Check spam, then <button class="linklike">send a new code</button> or <a href="/signin?next=<?= e(rawurlencode($next)) ?>">use a different address</a>.</p>
    </form>
  <?php else: ?>
    <form method="post" action="/signin">
      <?= csrf_field() ?><input type="hidden" name="action" value="request"><input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field">
        <label for="email">Work email</label>
        <input id="email" name="email" type="email" autocomplete="email" value="<?= e($email) ?>" required autofocus aria-describedby="email-hint" <?= $error ? 'aria-invalid="true"' : '' ?>>
        <p id="email-hint" class="hint" <?= $error ? 'style="color:var(--cons)"' : '' ?>><?= e($error ?? 'For example name@yourfirm.com. Personal Gmail, Yahoo and Outlook addresses can\'t be used.') ?></p>
      </div>
      <button class="btn">Email me a code</button>
      <p class="small muted" style="margin:16px 0 0">By continuing you agree to our <a href="/terms">Terms of Service</a> and confirm you've read our <a href="/privacy">Privacy Policy</a>. You must be 18 or older.</p>
    </form>
  <?php endif; ?>
  </div>
</div>
