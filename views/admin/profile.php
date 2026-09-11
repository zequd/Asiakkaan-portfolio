<form method="post" enctype="multipart/form-data">

    <div class="field">
        <label>Name</label>
        <input type="text" name="name" value="<?= e($site['name']) ?>">
    </div>

    <div class="field">
        <label>Role</label>
        <input type="text" name="role" value="<?= e($site['role']) ?>">
    </div>

    <div class="field">
        <label>Company</label>
        <input type="text" name="company" value="<?= e($site['company']) ?>">
    </div>

    <div class="field">
        <label>Location</label>
        <input type="text" name="location" value="<?= e($site['location']) ?>">
    </div>

    <div class="field">
        <label>Telegram</label>
        <input type="text" name="telegram" value="<?= e($site['telegram']) ?>">
    </div>

    <div class="field">
        <label>LinkedIn address</label>
        <input type="text" name="linkedin" value="<?= e($site['linkedin']) ?>">
    </div>

    <div class="field">
        <label>LinkedIn label</label>
        <input type="text" name="linkedin_label" value="<?= e($site['linkedin_label']) ?>">
    </div>

    <div class="field">
        <label>Search description</label>
        <textarea name="meta"><?= e($site['meta']) ?></textarea>
    </div>

    <div class="field">
        <label>About</label>
        <textarea name="about" style="min-height:140px"><?= e($site['about']) ?></textarea>
    </div>

    <div class="field">
        <label>Portrait</label>
        <input type="file" name="avatar" accept="image/*">
    </div>

    <div class="actions">
        <button class="btn btn--main" type="submit">Save</button>
    </div>

</form>
