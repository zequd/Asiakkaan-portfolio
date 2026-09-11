<form method="post">

    <div class="field">
        <label>Telegram chat id</label>
        <input type="text" name="chat_id" value="<?= e($telegram['chat_id']) ?>">
    </div>

    <div class="field">
        <label>Bot token</label>
        <input type="password" name="bot_token" value="">
    </div>

    <div class="actions">
        <button class="btn btn--main" type="submit">Save</button>
    </div>

</form>
