<?php

$sent = isset($_GET['sent']) ? $_GET['sent'] : null;
?>

<section class="section section--last" id="contact">
    <div class="container-x">
        <div class="contact-card-wrap">
            <div class="contact-card">
                <svg class="contact-card__corner contact-card__corner--tl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <svg class="contact-card__corner contact-card__corner--tr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <svg class="contact-card__corner contact-card__corner--bl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <svg class="contact-card__corner contact-card__corner--br" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>

                <div class="contact-card__main">
                    <div class="contact-card__body">
                        <div class="contact-card__media">
                            <img class="contact-card__avatar" src="/assets/img/avatar.webp"
                                alt="<?= e($site['name']) ?>" width="400" height="400" loading="lazy">
                            <div>
                                <p class="contact-card__who"><?= e($site['name']) ?></p>
                                <p class="contact-card__what"><?= e($site['role']) ?> · <?= e($site['company']) ?></p>
                            </div>
                        </div>

                        <h2 class="contact-card__title">Contact me</h2>

                        <p class="contact-card__desc">
                            Advertisers, publishers, partnerships — if it touches AdTech, I want to hear it.
                            Everything sent here reaches me directly, and I answer within a business day.
                        </p>

                        <div class="contact-card__info">
                            <a class="contact-row contact-link" href="https://t.me/<?= e($site['telegram']) ?>"
                                target="_blank" rel="noopener noreferrer">
                                <span class="contact-row__tile">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                </span>
                                <span>
                                    <span class="contact-row__label">Telegram</span>
                                    <span class="contact-row__value">@<?= e($site['telegram']) ?></span>
                                </span>
                            </a>

                            <a class="contact-row contact-link" href="<?= e($site['linkedin']) ?>"
                                target="_blank" rel="noopener noreferrer">
                                <span class="contact-row__tile">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.07 2.07 0 1 1 0-4.14 2.07 2.07 0 0 1 0 4.14Zm1.78 13.02H3.55V9h3.57v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0Z"/></svg>
                                </span>
                                <span>
                                    <span class="contact-row__label">LinkedIn</span>
                                    <span class="contact-row__value"><?= e($site['linkedin_label']) ?></span>
                                </span>
                            </a>

                            <div class="contact-row">
                                <span class="contact-row__tile">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </span>
                                <span>
                                    <span class="contact-row__label">Based in</span>
                                    <span class="contact-row__value"><?= e($site['location']) ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-card__side">
                    <form class="contact-form" id="contact-form" action="/contact" method="post" novalidate>
                        <div class="field">
                            <label class="field__label" for="cf-name">Name</label>
                            <input class="input" id="cf-name" name="name" type="text"
                                autocomplete="name" maxlength="100" placeholder="Jane Doe">
                            <p class="field__error" data-error-for="name" hidden></p>
                        </div>

                        <div class="field">
                            <label class="field__label" for="cf-contact">Email or Telegram</label>
                            <input class="input" id="cf-contact" name="contact" type="text"
                                autocomplete="email" maxlength="100" placeholder="jane@company.com">
                            <p class="field__error" data-error-for="contact" hidden></p>
                        </div>

                        <div class="field">
                            <label class="field__label" for="cf-message">Message</label>
                            <textarea class="textarea" id="cf-message" name="message" rows="4"
                                maxlength="2000" placeholder="A line or two about what you have in mind."></textarea>
                            <p class="field__error" data-error-for="message" hidden></p>
                        </div>

                        <div class="honeypot" aria-hidden="true">
                            <label for="cf-website">Website</label>
                            <input id="cf-website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <button class="button" type="submit">Send message</button>

                        <p class="form-status<?= $sent === '1' ? ' form-status--ok' : ($sent === '0' ? ' form-status--error' : '') ?>"
                            id="form-status" role="status"><?php
                            if ($sent === '1') {
                                echo 'Thanks — your message is on its way.';
                            } elseif ($sent === '0') {
                                echo 'Something went wrong. Please try again.';
                            }
                        ?></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
