# Database Structure

Use `tools/export_schema.php` to export the live MySQL structure without data:

```bash
php tools/export_schema.php
```

The script reads `resources/dbcon.php` by default and writes `database/schema.sql`.
It exports tables, indexes, foreign keys, views, routines, and triggers when the
connected database user has permission to read them.

If the connection file is somewhere else, pass it with `DBCON_PATH`:

```bash
DBCON_PATH=/path/to/dbcon.php php tools/export_schema.php
```

`schema.inferred.sql` is only a reference rebuilt from the repository code and
the old `db.dbs.bak` model. For moving to another server, prefer a fresh
`schema.sql` generated from the live database.
