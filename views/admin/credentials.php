<?php $kinds = array('Education', 'Certification', 'Publication'); ?>

<?php foreach ($credentials as $item): ?>

    <form class="item" method="post">
        <h2 class="item__title"><?= e($item['kind']) ?></h2>

        <div class="field">
            <label>Kind</label>
            <select name="kind">
                <?php foreach ($kinds as $kind): ?>
                    <option value="<?= e($kind) ?>"<?= $kind === $item['kind'] ? ' selected' : '' ?>><?= e($kind) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Title</label>
            <input type="text" name="title" value="<?= e($item['title']) ?>">
        </div>

        <div class="field">
            <label>Issuer</label>
            <input type="text" name="issuer" value="<?= e($item['issuer']) ?>">
        </div>

        <div class="field">
            <label>Date</label>
            <input type="text" name="date" value="<?= e($item['date']) ?>">
        </div>

        <div class="actions">
            <button class="btn btn--main" type="submit">Save</button>
            <button class="link" type="submit" name="move" value="up">Up</button>
            <button class="link" type="submit" name="move" value="down">Down</button>
            <button class="link link--danger" type="submit" name="delete" value="1" onclick="return confirm('Delete this record?')">Delete</button>
        </div>
    </form>

<?php endforeach; ?>

<form class="item" method="post">
    <h2 class="item__title">Add a record</h2>

    <div class="field">
        <label>Kind</label>
        <select name="kind">
            <?php foreach ($kinds as $kind): ?>
                <option value="<?= e($kind) ?>"><?= e($kind) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label>Title</label>
        <input type="text" name="title" value="">
    </div>

    <div class="field">
        <label>Issuer</label>
        <input type="text" name="issuer" value="">
    </div>

    <div class="field">
        <label>Date</label>
        <input type="text" name="date" value="">
    </div>

    <div class="actions">
        <button class="btn btn--main" type="submit">Add</button>
    </div>
</form>
