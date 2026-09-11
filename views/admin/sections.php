<?php foreach ($sections as $section): ?>

    <form class="item" method="post">
        <h2 class="item__title"><?= e($section['label']) ?></h2>

        <div class="field">
            <label>Label</label>
            <input type="text" name="label" value="<?= e($section['label']) ?>">
        </div>

        <div class="field">
            <label>Anchor</label>
            <input type="text" name="id" value="<?= e($section['id']) ?>">
        </div>

        <div class="actions">
            <button class="btn btn--main" type="submit">Save</button>
            <button class="link" type="submit" name="move" value="up">Up</button>
            <button class="link" type="submit" name="move" value="down">Down</button>
            <button class="link link--danger" type="submit" name="delete" value="1" onclick="return confirm('Delete this link?')">Delete</button>
        </div>
    </form>

<?php endforeach; ?>

<form class="item" method="post">
    <h2 class="item__title">Add a link</h2>

    <div class="field">
        <label>Label</label>
        <input type="text" name="label" value="">
    </div>

    <div class="field">
        <label>Anchor</label>
        <input type="text" name="id" value="">
    </div>

    <div class="actions">
        <button class="btn btn--main" type="submit">Add</button>
    </div>
</form>
