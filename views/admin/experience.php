<?php foreach ($experience as $job): ?>

    <form class="item" method="post" enctype="multipart/form-data">
        <h2 class="item__title"><?= e($job['company']) ?></h2>

        <div class="field">
            <label>Company</label>
            <input type="text" name="company" value="<?= e($job['company']) ?>">
        </div>

        <div class="field">
            <label>Role</label>
            <input type="text" name="role" value="<?= e($job['role']) ?>">
        </div>

        <div class="field">
            <label>Period</label>
            <input type="text" name="period" value="<?= e($job['period']) ?>">
        </div>

        <div class="field">
            <label>Summary</label>
            <textarea name="summary"><?= e($job['summary']) ?></textarea>
        </div>

        <div class="field">
            <label>Card background</label>
            <input type="file" name="card" accept="image/*">
        </div>

        <div class="field">
            <label>Small logo</label>
            <input type="file" name="icon" accept="image/*">
        </div>

        <div class="actions">
            <button class="btn btn--main" type="submit">Save</button>
            <button class="link" type="submit" name="move" value="up">Up</button>
            <button class="link" type="submit" name="move" value="down">Down</button>
            <button class="link link--danger" type="submit" name="delete" value="1" onclick="return confirm('Delete this position?')">Delete</button>
        </div>
    </form>

<?php endforeach; ?>

<form class="item" method="post" enctype="multipart/form-data">
    <h2 class="item__title">Add a position</h2>

    <div class="field">
        <label>Company</label>
        <input type="text" name="company" value="">
    </div>

    <div class="field">
        <label>Role</label>
        <input type="text" name="role" value="">
    </div>

    <div class="field">
        <label>Period</label>
        <input type="text" name="period" value="">
    </div>

    <div class="field">
        <label>Summary</label>
        <textarea name="summary"></textarea>
    </div>

    <div class="field">
        <label>Card background</label>
        <input type="file" name="card" accept="image/*">
    </div>

    <div class="field">
        <label>Small logo</label>
        <input type="file" name="icon" accept="image/*">
    </div>

    <div class="actions">
        <button class="btn btn--main" type="submit">Add</button>
    </div>
</form>
