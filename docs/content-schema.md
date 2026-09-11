# Content schema

The admin panel edits the content of the public page. The forms are already
built around the tables below, so wiring them to MySQL should not change the
screens.

Every list has `position` so the Up and Down buttons in the admin have
something to write, and the public page can order rows by it.

```sql
CREATE TABLE profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(150) NOT NULL,
    company VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    telegram VARCHAR(50) NOT NULL,
    linkedin VARCHAR(255) NOT NULL,
    linkedin_label VARCHAR(100) NOT NULL,
    meta VARCHAR(255) NOT NULL,
    about TEXT NOT NULL,
    avatar VARCHAR(255) NOT NULL
);

CREATE TABLE experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(100) NOT NULL,
    role VARCHAR(150) NOT NULL,
    period VARCHAR(50) NOT NULL,
    summary TEXT NOT NULL,
    card VARCHAR(255) NOT NULL,
    icon VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0
);

CREATE TABLE skill_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    note TEXT NOT NULL,
    position INT NOT NULL DEFAULT 0
);

CREATE TABLE skill_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    FOREIGN KEY (group_id) REFERENCES skill_groups(id) ON DELETE CASCADE
);

CREATE TABLE credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kind VARCHAR(30) NOT NULL,
    title VARCHAR(255) NOT NULL,
    issuer VARCHAR(255) NOT NULL,
    date VARCHAR(50) NOT NULL,
    position INT NOT NULL DEFAULT 0
);

CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anchor VARCHAR(50) NOT NULL,
    label VARCHAR(50) NOT NULL,
    position INT NOT NULL DEFAULT 0
);
```

## Notes

`profile` holds a single row. The admin form edits that row, it never adds one.

`skill_items` is a child table: the admin screen shows one skill per line in a
text area, and each line becomes a row.

`date` in `credentials` is text on purpose. The values are written as
`Issued Jan 2023` or `2021 — Jan 2024`, which no date type can store.

Image columns keep a path, not the file itself, exactly like the current
`config/content.php`. Uploads go to `public/uploads/`.

The Telegram chat id and bot token stay in `.env`, not in the database. They
are credentials, and the repository must not contain them.

## Where the data comes from today

`config/content.php` still holds the real content and feeds both the public
page and the admin forms. When the tables above exist, the two places that
read that file are `HomeController::index()` and `AdminController::show()`.
