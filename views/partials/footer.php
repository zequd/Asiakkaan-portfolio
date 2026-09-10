<footer class="footer">
    <div class="container-x footer__inner">
        <p class="footer__name"><?= e($site['name']) ?></p>

        <ul class="footer__links">
            <li>
                <a class="footer__link" href="https://t.me/<?= e($site['telegram']) ?>"
                    target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true"><path d="M21.94 4.3 18.6 20.05c-.25 1.11-.91 1.39-1.84.86l-5.09-3.75-2.46 2.36c-.27.27-.5.5-1.03.5l.37-5.2 9.47-8.56c.41-.37-.09-.57-.64-.2L5.68 13.42.63 11.85c-1.1-.34-1.12-1.1.23-1.63l19.74-7.6c.91-.34 1.71.2 1.34 1.68Z"/></svg>
                    <span>@<?= e($site['telegram']) ?></span>
                </a>
            </li>
            <li>
                <a class="footer__link" href="<?= e($site['linkedin']) ?>"
                    target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.07 2.07 0 1 1 0-4.14 2.07 2.07 0 0 1 0 4.14Zm1.78 13.02H3.55V9h3.57v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0Z"/></svg>
                    <span>LinkedIn</span>
                </a>
            </li>
        </ul>

        <p class="footer__meta"><?= e($site['location']) ?></p>
    </div>
</footer>
