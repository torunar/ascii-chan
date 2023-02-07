create table if not exists boards
(
    id           integer not null
        constraint id
        primary key autoincrement,
    slug         TEXT    not null,
    display_name TEXT    not null
);

create table if not exists  posts
(
    id         integer not null
        constraint id
        primary key autoincrement,
    thread_id  integer not null,
    text       TEXT    not null,
    created_at TEXT    not null
);

create table if not exists threads
(
    id         integer not null
        constraint id
        primary key autoincrement,
    title      TEXT,
    board_id   integer not null,
    op_post_id integer not null,
    created_at integer not null,
    updated_at integer not null
);
