#!/bin/bash
cp com.plexapp.plugins.library.db com.plexapp.plugins.library.db.original
sqlite3 com.plexapp.plugins.library.db "DROP index 'index_title_sort_naturalsort'"
sqlite3 com.plexapp.plugins.library.db "DELETE from schema_migrations where version='20180501000000'"
sqlite3 com.plexapp.plugins.library.db .dump > dump.sql
rm com.plexapp.plugins.library.db
sqlite3 com.plexapp.plugins.library.db < dump.sql
rm com.plexapp.plugins.library.db-shm
rm com.plexapp.plugins.library.db-wal
chown plex:plex com.plexapp.plugins.library.db
