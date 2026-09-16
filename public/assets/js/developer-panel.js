(function () {
    'use strict';

    var state = {
        open: false,
        loggedIn: false,
        csrf: '',
        content: null,
        original: null,
        tab: 'profile',
        dirty: false,
        sequence: null
    };

    var apiBase = (function () {
        var path = window.location.pathname || '/';
        var marker = '/public/';
        var index = path.indexOf(marker);
        if (index !== -1) return path.substring(0, index) + '/public';
        return '';
    })();

    function api(path) { return apiBase + path; }

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function clone(obj) { return JSON.parse(JSON.stringify(obj)); }
    function equal(a, b) { return JSON.stringify(a) === JSON.stringify(b); }

    function request(path, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.credentials = 'same-origin';
        if (!options.body || !(options.body instanceof FormData)) {
            options.headers['Content-Type'] = 'application/json';
        }
        return fetch(api(path), options).then(function (response) {
            return response.json().catch(function () {
                return { success: false, message: 'Invalid server response.' };
            }).then(function (data) {
                if (!response.ok) {
                    var error = new Error(data.message || 'Request failed.');
                    error.status = response.status;
                    throw error;
                }
                return data;
            });
        });
    }

    function markDirty() {
        state.dirty = !equal(state.content, state.original);
        var badge = document.querySelector('[data-dev-status]');
        if (badge) {
            badge.textContent = state.dirty ? 'Unsaved changes' : 'Saved';
            badge.className = 'dap-status ' + (state.dirty ? 'dirty' : 'saved');
        }
    }

    function setContent(content) {
        state.content = clone(content);
        state.original = clone(content);
        state.dirty = false;
    }

    function ensureContent() {
        if (!state.content) return;
        state.content.site = state.content.site || {};
        if (!state.content.site.avatar) state.content.site.avatar = '/assets/img/avatar.webp';
        state.content.experience = Array.isArray(state.content.experience) ? state.content.experience : [];
        state.content.skills = Array.isArray(state.content.skills) ? state.content.skills : [];
        state.content.credentials = Array.isArray(state.content.credentials) ? state.content.credentials : [];
        state.content.sections = Array.isArray(state.content.sections) ? state.content.sections : [];
    }

    function buildShell() {
        if (document.getElementById('developer-panel-root')) return;
        var root = document.createElement('div');
        root.id = 'developer-panel-root';
        root.innerHTML = `
            <div class="dap-backdrop" data-dap-close></div>
            <div class="dap-login" data-dap-login>
                <div class="dap-card dap-login-card">
                    <button class="dap-x" type="button" data-dap-login-close>×</button>
                    <div class="dap-kicker">Developer access</div>
                    <h2>Admin panel</h2>
                    <p class="dap-muted">Enter your administrator credentials.</p>
                    <form data-dap-login-form>
                        <label>Username<input name="username" autocomplete="username" required></label>
                        <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
                        <div class="dap-error" data-dap-login-error></div>
                        <button class="dap-primary" type="submit">Login</button>
                    </form>
                </div>
            </div>
            <div class="dap-editor" data-dap-editor>
                <aside class="dap-sidebar">
                    <div>
                        <div class="dap-brand">DEVELOPER PANEL</div>
                        <div class="dap-small">Portfolio editor</div>
                    </div>
                    <nav data-dap-tabs>
                        <button data-tab="profile">Profile</button>
                        <button data-tab="experience">Experience</button>
                        <button data-tab="skills">Skills</button>
                        <button data-tab="credentials">Credentials</button>
                        <button data-tab="sections">Menu</button>
                        <button data-tab="security">Security</button>
                    </nav>
                    <div class="dap-side-bottom">
                        <span class="dap-status saved" data-dev-status>Saved</span>
                        <button class="dap-ghost" data-dap-logout type="button">Logout</button>
                    </div>
                </aside>
                <main class="dap-main">
                    <header class="dap-header">
                        <div>
                            <div class="dap-kicker">Website editor</div>
                            <h1 data-dap-title>Profile</h1>
                        </div>
                        <div class="dap-actions">
                            <button class="dap-ghost" type="button" data-dap-cancel>Close</button>
                            <button class="dap-primary" type="button" data-dap-save>Save changes</button>
                        </div>
                    </header>
                    <section class="dap-content" data-dap-content></section>
                </main>
            </div>
        `;
        document.body.appendChild(root);
        injectCss();
        bindShell();
    }

    function injectCss() {
        if (document.getElementById('developer-panel-style')) return;
        var style = document.createElement('style');
        style.id = 'developer-panel-style';
        style.textContent = `
            #developer-panel-root{font-family:Arial,sans-serif;color:#f5f5f5}
            #developer-panel-root *{box-sizing:border-box}
            .dap-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:99998}
            .dap-login{display:none;position:fixed;inset:0;align-items:center;justify-content:center;padding:20px;z-index:100000;background:rgba(0,0,0,.78)}
            .dap-login.open{display:flex}.dap-editor.open{display:flex}.dap-editor{display:none;position:fixed;inset:0;z-index:99999;background:#101114}
            .dap-card{background:#191b20;border:1px solid #30343c;border-radius:18px;box-shadow:0 25px 80px rgba(0,0,0,.5)}
            .dap-login-card{width:min(430px,100%);padding:30px;position:relative}.dap-x{position:absolute;right:15px;top:10px;border:0;background:transparent;color:#aaa;font-size:28px;cursor:pointer}
            .dap-kicker{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#8f98a8}.dap-muted,.dap-small{color:#9299a6;font-size:13px}.dap-login-card h2{margin:8px 0 6px;font-size:28px}
            .dap-login-card form{display:grid;gap:14px;margin-top:24px}.dap-login-card label,.dap-field label{display:grid;gap:7px;font-size:12px;color:#aeb5c1}
            .dap-login-card input,.dap-field input,.dap-field textarea,.dap-field select{width:100%;border:1px solid #343943;background:#101216;color:#fff;border-radius:9px;padding:11px 12px;outline:none;font:inherit}
            .dap-field textarea{min-height:105px;resize:vertical}.dap-login-card input:focus,.dap-field input:focus,.dap-field textarea:focus{border-color:#697386}
            .dap-primary,.dap-ghost{border:0;border-radius:9px;padding:10px 14px;font-weight:700;cursor:pointer}.dap-primary{background:#fff;color:#111}.dap-ghost{background:#24272e;color:#e9ebef}.dap-primary:disabled{opacity:.5;cursor:not-allowed}
            .dap-error{color:#ff7f7f;font-size:13px;min-height:16px}.dap-success{color:#9ee2af;font-size:13px;min-height:16px}
            .dap-sidebar{width:245px;border-right:1px solid #292c33;padding:25px 16px;display:flex;flex-direction:column;gap:28px;background:#15171b}.dap-brand{font-size:13px;font-weight:800;letter-spacing:.12em}
            .dap-sidebar nav{display:grid;gap:5px}.dap-sidebar nav button{border:0;text-align:left;background:transparent;color:#9da4b0;padding:11px 12px;border-radius:8px;cursor:pointer;font-size:14px}.dap-sidebar nav button.active,.dap-sidebar nav button:hover{background:#24272e;color:#fff}
            .dap-side-bottom{margin-top:auto;display:grid;gap:10px}.dap-status{font-size:11px;padding:8px 10px;border-radius:8px;background:#202329;color:#8f98a8}.dap-status.dirty{color:#ffd18a}.dap-status.saved{color:#9ee2af}
            .dap-main{min-width:0;flex:1;display:flex;flex-direction:column}.dap-header{min-height:92px;border-bottom:1px solid #292c33;padding:22px 28px;display:flex;align-items:center;justify-content:space-between;gap:20px}.dap-header h1{margin:5px 0 0;font-size:24px}.dap-actions{display:flex;gap:9px}.dap-content{padding:28px;overflow:auto}
            .dap-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;max-width:1050px}.dap-field{display:grid;gap:7px}.dap-field.full{grid-column:1/-1}.dap-section-title{font-size:14px;font-weight:800;margin:0 0 14px}
            .dap-block{max-width:1050px;border:1px solid #2c3038;background:#17191e;border-radius:13px;padding:18px;margin-bottom:15px}.dap-block-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:15px}.dap-block-head strong{font-size:14px}
            .dap-danger{border:0;background:#351f23;color:#ff9b9b;border-radius:8px;padding:8px 10px;cursor:pointer}.dap-row-actions{display:flex;gap:7px}.dap-icon-btn{border:0;background:#252830;color:#bbb;border-radius:7px;padding:6px 9px;cursor:pointer}
            .dap-list{display:grid;gap:10px}.dap-list-item{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center;border:1px solid #2d3139;border-radius:10px;padding:11px;background:#121419}.dap-add{margin-top:4px}.dap-empty{color:#8f96a3;border:1px dashed #353a44;border-radius:10px;padding:18px;text-align:center}
            .dap-media{display:grid;grid-template-columns:96px 1fr auto;gap:12px;align-items:center;grid-column:1/-1}.dap-thumb{width:96px;height:70px;object-fit:contain;background:#0d0f12;border:1px solid #30343c;border-radius:8px}.dap-upload{white-space:nowrap}
            .dap-security-note{max-width:700px;color:#aeb5c1;line-height:1.55;font-size:13px}.dap-account-form{max-width:560px;display:grid;gap:14px}.dap-divider{height:1px;background:#2c3038;margin:20px 0}
            @media(max-width:760px){.dap-sidebar{width:190px}.dap-grid{grid-template-columns:1fr}.dap-media{grid-template-columns:72px 1fr}.dap-media .dap-upload{grid-column:2}.dap-header{align-items:flex-start;flex-direction:column}.dap-actions{width:100%}.dap-actions button{flex:1}.dap-content{padding:18px}}
            @media(max-width:560px){.dap-sidebar{display:none}.dap-header{padding:18px}.dap-content{padding:14px}.dap-editor{overflow:auto}.dap-main{min-height:100vh}.dap-media{grid-template-columns:1fr}.dap-media .dap-upload{grid-column:auto}}
        `;
        document.head.appendChild(style);
    }

    function bindShell() {
        var root = document.getElementById('developer-panel-root');
        root.querySelector('[data-dap-login-form]').addEventListener('submit', function (event) {
            event.preventDefault();
            var form = event.currentTarget;
            var error = root.querySelector('[data-dap-login-error]');
            error.textContent = '';
            request('/admin/login', {
                method: 'POST',
                body: JSON.stringify({ username: form.username.value, password: form.password.value })
            }).then(function (data) {
                state.loggedIn = true;
                state.csrf = data.csrf || '';
                form.reset();
                return loadContent();
            }).then(function () {
                root.querySelector('[data-dap-login]').classList.remove('open');
                root.querySelector('[data-dap-editor]').classList.add('open');
                root.querySelector('.dap-backdrop').style.display = 'block';
                state.open = true;
                render();
            }).catch(function (err) { error.textContent = err.message || 'Login failed.'; });
        });

        root.querySelector('[data-dap-login-close]').addEventListener('click', closeAll);
        root.querySelector('[data-dap-close]').addEventListener('click', closeEditor);
        root.querySelector('[data-dap-cancel]').addEventListener('click', closeEditor);
        root.querySelector('[data-dap-save]').addEventListener('click', save);
        root.querySelector('[data-dap-logout]').addEventListener('click', function () {
            if (state.dirty && !window.confirm('You have unsaved changes. Logout anyway?')) return;
            request('/admin/logout', { method: 'POST', body: '{}' }).finally(closeAll);
        });

        root.querySelector('[data-dap-tabs]').addEventListener('click', function (event) {
            var button = event.target.closest('[data-tab]');
            if (!button) return;
            state.tab = button.getAttribute('data-tab');
            render();
        });

        root.addEventListener('click', function (event) {
            var action = event.target.closest('[data-action]');
            if (!action || !state.open) return;
            handleAction(action.getAttribute('data-action'), action.getAttribute('data-index'), action.getAttribute('data-extra'));
        });

        root.addEventListener('input', function (event) {
            var field = event.target.closest('[data-bind]');
            if (!field || !state.open) return;
            updateBinding(field);
        });

        root.addEventListener('change', function (event) {
            var upload = event.target.closest('[data-upload]');
            if (!upload || !state.open) return;
            var file = upload.files && upload.files[0];
            if (!file) return;
            uploadImage(file, upload.getAttribute('data-upload')).finally(function () { upload.value = ''; });
        });
    }

    function openLogin() {
        buildShell();
        var root = document.getElementById('developer-panel-root');
        request('/admin/status').then(function (data) {
            if (data.loggedIn) {
                state.loggedIn = true;
                state.csrf = data.csrf || '';
                return loadContent().then(function () {
                    root.querySelector('[data-dap-editor]').classList.add('open');
                    root.querySelector('.dap-backdrop').style.display = 'block';
                    state.open = true;
                    render();
                });
            }
            root.querySelector('[data-dap-login]').classList.add('open');
            root.querySelector('[data-dap-login] input[name="username"]').focus();
        }).catch(function (err) { window.alert(err.message || 'Cannot connect to admin endpoint.'); });
    }

    function closeEditor() {
        if (state.dirty && !window.confirm('You have unsaved changes. Close anyway?')) return;
        closeAll();
    }

    function closeAll() {
        var root = document.getElementById('developer-panel-root');
        if (!root) return;
        root.querySelector('[data-dap-login]').classList.remove('open');
        root.querySelector('[data-dap-editor]').classList.remove('open');
        root.querySelector('.dap-backdrop').style.display = 'none';
        state.open = false;
        state.loggedIn = false;
        state.csrf = '';
        state.dirty = false;
    }

    function loadContent() {
        return request('/admin/get').then(function (data) { setContent(data.content); ensureContent(); });
    }

    function save() {
        if (!state.content || !state.dirty) return;
        var button = document.querySelector('[data-dap-save]');
        button.disabled = true;
        button.textContent = 'Saving...';
        request('/admin/save', {
            method: 'POST',
            body: JSON.stringify({ csrf: state.csrf, content: state.content })
        }).then(function (data) {
            setContent(data.content);
            render();
        }).catch(function (err) { window.alert(err.message || 'Could not save changes.'); })
          .finally(function () { button.disabled = false; button.textContent = 'Save changes'; });
    }

    function uploadImage(file, target) {
        var form = new FormData();
        form.append('csrf', state.csrf);
        form.append('image', file);
        var root = document.getElementById('developer-panel-root');
        var status = root.querySelector('[data-upload-status]');
        if (status) status.textContent = 'Uploading...';

        return request('/admin/upload', { method: 'POST', body: form }).then(function (data) {
            setPath(target, data.path);
            markDirty();
            render();
        }).catch(function (err) {
            window.alert(err.message || 'Could not upload image.');
        });
    }

    function setPath(target, value) {
        var path = target.split('.');
        var obj = state.content;
        for (var i = 0; i < path.length - 1; i++) obj = obj[path[i]];
        obj[path[path.length - 1]] = value;
    }

    function updateBinding(field) {
        var path = field.getAttribute('data-bind').split('.');
        var target = state.content;
        for (var i = 0; i < path.length - 1; i++) {
            var part = path[i];
            if (target[part] === undefined) target[part] = {};
            target = target[part];
        }
        target[path[path.length - 1]] = field.value;
        markDirty();
    }

    function handleAction(action, index, extra) {
        index = index === null ? -1 : parseInt(index, 10);
        if (action === 'add-experience') state.content.experience.push({ company:'', role:'', period:'', summary:'', card:'', icon:'' });
        else if (action === 'delete-experience') state.content.experience.splice(index, 1);
        else if (action === 'up-experience') move(state.content.experience, index, -1);
        else if (action === 'down-experience') move(state.content.experience, index, 1);
        else if (action === 'add-skill') state.content.skills.push({ title:'', note:'', items:[] });
        else if (action === 'delete-skill') state.content.skills.splice(index, 1);
        else if (action === 'up-skill') move(state.content.skills, index, -1);
        else if (action === 'down-skill') move(state.content.skills, index, 1);
        else if (action === 'add-skill-item') state.content.skills[index].items.push('');
        else if (action === 'delete-skill-item') state.content.skills[index].items.splice(parseInt(extra, 10), 1);
        else if (action === 'add-credential') state.content.credentials.push({ kind:'Certification', title:'', issuer:'', date:'' });
        else if (action === 'delete-credential') state.content.credentials.splice(index, 1);
        else if (action === 'up-credential') move(state.content.credentials, index, -1);
        else if (action === 'down-credential') move(state.content.credentials, index, 1);
        else if (action === 'add-section') state.content.sections.push({ id:'', label:'' });
        else if (action === 'delete-section') state.content.sections.splice(index, 1);
        else if (action === 'up-section') move(state.content.sections, index, -1);
        else if (action === 'down-section') move(state.content.sections, index, 1);
        else return;
        markDirty();
        render();
    }

    function move(list, index, direction) {
        var next = index + direction;
        if (index < 0 || next < 0 || next >= list.length) return;
        var temp = list[index]; list[index] = list[next]; list[next] = temp;
    }

    function controls(type, index) {
        return `<div class="dap-row-actions"><button class="dap-icon-btn" data-action="up-${type}" data-index="${index}" type="button">↑</button><button class="dap-icon-btn" data-action="down-${type}" data-index="${index}" type="button">↓</button><button class="dap-danger" data-action="delete-${type}" data-index="${index}" type="button">Delete</button></div>`;
    }

    function field(label, bind, value, full) {
        return `<div class="dap-field${full ? ' full' : ''}"><label>${esc(label)}<input data-bind="${esc(bind)}" value="${esc(value)}"></label></div>`;
    }

    function textarea(label, bind, value) {
        return `<div class="dap-field full"><label>${esc(label)}<textarea data-bind="${esc(bind)}">${esc(value)}</textarea></label></div>`;
    }

    function uploadField(label, bind, value) {
        var preview = value ? `<img class="dap-thumb" src="${esc(value)}" alt="">` : '<div class="dap-thumb"></div>';
        return `<div class="dap-media"><div>${preview}</div><div class="dap-field"><label>${esc(label)}<input data-bind="${esc(bind)}" value="${esc(value)}"></label></div><label class="dap-primary dap-upload">Upload<input type="file" data-upload="${esc(bind)}" accept="image/jpeg,image/png,image/webp,image/gif" hidden></label></div>`;
    }

    function render() {
        if (!state.content) return;
        ensureContent();
        var root = document.getElementById('developer-panel-root');
        root.querySelectorAll('[data-tab]').forEach(function (button) { button.classList.toggle('active', button.getAttribute('data-tab') === state.tab); });
        var titles = { profile:'Profile', experience:'Experience', skills:'Skills', credentials:'Credentials', sections:'Menu', security:'Security' };
        root.querySelector('[data-dap-title]').textContent = titles[state.tab] || 'Profile';
        root.querySelector('[data-dap-content]').innerHTML = renderTab();
        markDirty();
    }

    function renderTab() {
        if (state.tab === 'profile') return renderProfile();
        if (state.tab === 'experience') return renderExperience();
        if (state.tab === 'skills') return renderSkills();
        if (state.tab === 'credentials') return renderCredentials();
        if (state.tab === 'security') return renderSecurity();
        return renderSections();
    }

    function renderProfile() {
        var s = state.content.site;
        return `<div class="dap-block"><div class="dap-section-title">Personal and professional information</div><div class="dap-grid">
            ${uploadField('Avatar image','site.avatar',s.avatar || '/assets/img/avatar.webp')}
            ${field('Name','site.name',s.name)}${field('Role','site.role',s.role)}${field('Company','site.company',s.company)}${field('Location','site.location',s.location)}
            ${field('Telegram username','site.telegram',s.telegram)}${field('LinkedIn URL','site.linkedin',s.linkedin)}${field('LinkedIn label','site.linkedin_label',s.linkedin_label)}
            ${textarea('Meta description','site.meta',s.meta)}${textarea('About','site.about',s.about)}
        </div></div>`;
    }

    function renderExperience() {
        var html = state.content.experience.map(function (item, i) {
            return `<div class="dap-block"><div class="dap-block-head"><strong>Experience #${i + 1}</strong>${controls('experience', i)}</div><div class="dap-grid">
                ${field('Company',`experience.${i}.company`,item.company)}${field('Role',`experience.${i}.role`,item.role)}${field('Period',`experience.${i}.period`,item.period)}
                ${textarea('Summary',`experience.${i}.summary`,item.summary)}${uploadField('Card image','experience.'+i+'.card',item.card)}${uploadField('Icon image','experience.'+i+'.icon',item.icon)}
            </div></div>`;
        }).join('');
        return (html || '<div class="dap-empty">No experience entries.</div>') + '<button class="dap-primary dap-add" data-action="add-experience" type="button">+ Add experience</button>';
    }

    function renderSkills() {
        var html = state.content.skills.map(function (group, i) {
            var items = (group.items || []).map(function (item, j) { return `<div class="dap-list-item"><input data-bind="skills.${i}.items.${j}" value="${esc(item)}"><button class="dap-danger" data-action="delete-skill-item" data-index="${i}" data-extra="${j}" type="button">Delete</button></div>`; }).join('');
            return `<div class="dap-block"><div class="dap-block-head"><strong>Skill group #${i + 1}</strong>${controls('skill', i)}</div><div class="dap-grid">${field('Title',`skills.${i}.title`,group.title)}${textarea('Note',`skills.${i}.note`,group.note)}</div><div class="dap-section-title" style="margin-top:18px">Skills</div><div class="dap-list">${items || '<div class="dap-empty">No skills in this group.</div>'}</div><button class="dap-ghost dap-add" data-action="add-skill-item" data-index="${i}" type="button">+ Add skill</button></div>`;
        }).join('');
        return (html || '<div class="dap-empty">No skill groups.</div>') + '<button class="dap-primary dap-add" data-action="add-skill" type="button">+ Add skill group</button>';
    }

    function renderCredentials() {
        var html = state.content.credentials.map(function (item, i) { return `<div class="dap-block"><div class="dap-block-head"><strong>Credential #${i + 1}</strong>${controls('credential', i)}</div><div class="dap-grid">${field('Kind',`credentials.${i}.kind`,item.kind)}${field('Date',`credentials.${i}.date`,item.date)}${field('Title',`credentials.${i}.title`,item.title,true)}${field('Issuer',`credentials.${i}.issuer`,item.issuer,true)}</div></div>`; }).join('');
        return (html || '<div class="dap-empty">No credentials.</div>') + '<button class="dap-primary dap-add" data-action="add-credential" type="button">+ Add credential</button>';
    }

    function renderSections() {
        var html = state.content.sections.map(function (item, i) { return `<div class="dap-block"><div class="dap-block-head"><strong>Menu item #${i + 1}</strong>${controls('section', i)}</div><div class="dap-grid">${field('Section ID',`sections.${i}.id`,item.id)}${field('Label',`sections.${i}.label`,item.label)}</div></div>`; }).join('');
        return (html || '<div class="dap-empty">No menu items.</div>') + '<button class="dap-primary dap-add" data-action="add-section" type="button">+ Add menu item</button>';
    }

    function renderSecurity() {
        return `<div class="dap-block"><div class="dap-section-title">Change administrator login</div>
            <p class="dap-security-note">To change the account, enter the current login and password first. Then choose the new login and password. The new password must contain at least 8 characters.</p>
            <div class="dap-divider"></div>
            <form class="dap-account-form" data-security-form>
                <div class="dap-field"><label>Current login<input name="current_username" autocomplete="username" required></label></div>
                <div class="dap-field"><label>Current password<input name="current_password" type="password" autocomplete="current-password" required></label></div>
                <div class="dap-field"><label>New login<input name="new_username" autocomplete="username" required></label></div>
                <div class="dap-field"><label>New password<input name="new_password" type="password" autocomplete="new-password" minlength="8" required></label></div>
                <div class="dap-field"><label>Repeat new password<input name="new_password_repeat" type="password" autocomplete="new-password" minlength="8" required></label></div>
                <div class="dap-error" data-security-error></div><div class="dap-success" data-security-success></div>
                <button class="dap-primary" type="submit">Change login and password</button>
            </form>
        </div>`;
    }

    function submitSecurity(form) {
        var error = form.querySelector('[data-security-error]');
        var success = form.querySelector('[data-security-success]');
        error.textContent = ''; success.textContent = '';
        if (form.new_password.value !== form.new_password_repeat.value) { error.textContent = 'New passwords do not match.'; return; }

        var button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        request('/admin/credentials-update', {
            method: 'POST',
            body: JSON.stringify({
                csrf: state.csrf,
                current_username: form.current_username.value,
                current_password: form.current_password.value,
                new_username: form.new_username.value,
                new_password: form.new_password.value
            })
        }).then(function (data) {
            state.csrf = data.csrf || state.csrf;
            success.textContent = 'Login and password changed successfully.';
            form.reset();
        }).catch(function (err) { error.textContent = err.message || 'Could not change credentials.'; })
          .finally(function () { button.disabled = false; });
    }

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('[data-security-form]');
        if (!form || !state.open) return;
        event.preventDefault();
        submitSecurity(form);
    });

    function handleKeys(event) {
        var key = String(event.key || '').toLowerCase();
        if (key === 'escape' && state.open) { closeEditor(); return; }
        if (event.ctrlKey || event.metaKey || event.altKey) return;
        if (key === 'p') { state.sequence = Date.now(); return; }
        if (key === 'a' && state.sequence && Date.now() - state.sequence < 900) { state.sequence = null; openLogin(); }
    }

    document.addEventListener('keydown', handleKeys);
    buildShell();
})();
