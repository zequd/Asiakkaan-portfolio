<?php foreach ($skills as $group): ?>

    <form class="item" method="post">
        <h2 class="item__title"><?= e($group['title']) ?></h2>

        <div class="field">
            <label>Title</label>
            <input type="text" name="title" value="<?= e($group['title']) ?>">
        </div>

        <div class="field">
            <label>Note</label>
            <textarea name="note"><?= e($group['note']) ?></textarea>
        </div>

        <div class="field">
            <label>Skills, one per line</label>
            <textarea name="items" style="min-height:120px"><?= e(implode("\n", $group['items'])) ?></textarea>
        </div>

        <div class="actions">
            <button class="btn btn--main" type="submit">Save</button>
            <button class="link" type="submit" name="move" value="up">Up</button>
            <button class="link" type="submit" name="move" value="down">Down</button>
            <button class="link link--danger" type="submit" name="delete" value="1" onclick="return confirm('Delete this group?')">Delete</button>
        </div>
    </form>

<?php endforeach; ?>

<form class="item" method="post">
    <h2 class="item__title">Add a group</h2>

    <div class="field">
        <label>Title</label>
        <input type="text" name="title" value="">
    </div>

    <div class="field">
        <label>Note</label>
        <textarea name="note"></textarea>
    </div>

    <div class="field">
        <label>Skills, one per line</label>
        <textarea name="items" style="min-height:120px"></textarea>
    </div>

    <div class="actions">
        <button class="btn btn--main" type="submit">Add</button>
    </div>
</form>
