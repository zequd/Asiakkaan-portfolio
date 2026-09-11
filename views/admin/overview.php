<div class="list">
    <a href="/admin/profile">Profile <span><?= e($site['name']) ?> — <?= e($site['role']) ?></span></a>
    <a href="/admin/experience">Experience <span><?= count($experience) ?> positions</span></a>
    <a href="/admin/skills">Skills <span><?= count($skills) ?> groups</span></a>
    <a href="/admin/credentials">Credentials <span><?= count($credentials) ?> records</span></a>
    <a href="/admin/sections">Menu <span><?= count($sections) ?> links</span></a>
    <a href="/admin/contact">Contact <span>Where form messages go</span></a>
</div>
