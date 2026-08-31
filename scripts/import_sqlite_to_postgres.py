import re
import sqlite3
from pathlib import Path

root = Path(__file__).resolve().parents[1]
sqlite_db = root / 'database' / 'database.sqlite'
out_file = root / 'storage' / 'app' / 'recursos_humanos_pg.sql'

con = sqlite3.connect(str(sqlite_db))
con.row_factory = sqlite3.Row
cur = con.cursor()

# Only include real application tables (exclude sqlite internal tables)
tables = cur.execute(
    "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
).fetchall()

with out_file.open('w', encoding='utf-8') as f:
    f.write("BEGIN;\n")
    f.write("SET client_encoding = 'UTF8';\n\n")

    for row in tables:
        table = row['name']
        create_statement = cur.execute(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?",
            (table,),
        ).fetchone()[0]

        if not create_statement:
            continue

        normalized = create_statement
        normalized = re.sub(r'(?i)\bINTEGER\s+PRIMARY\s+KEY\s*AUTOINCREMENT\b', 'SERIAL PRIMARY KEY', normalized)
        normalized = re.sub(r'(?i)\bINTEGER\s+PRIMARY\s+KEY\b', 'SERIAL PRIMARY KEY', normalized)
        normalized = normalized.replace('AUTOINCREMENT', '')
        normalized = re.sub(r'(?i)\bdatetime\b', 'timestamp', normalized)
        normalized = re.sub(r'(?i)\btinyint\s*\(\s*1\s*\)', 'boolean', normalized)
        normalized = re.sub(r'(?i)\bbool\b', 'boolean', normalized)

        # Drop and recreate in PostgreSQL; keep same table names and constraint names where possible.
        f.write(f'DROP TABLE IF EXISTS "{table}" CASCADE;\n')
        f.write(normalized + ';\n\n')

        columns = [c['name'] for c in cur.execute(f'PRAGMA table_info("{table}")').fetchall()]
        if not columns:
            continue

        rows = cur.execute(f'SELECT * FROM "{table}"').fetchall()
        if not rows:
            # Ensure serial sequences still have a valid starting point even for empty tables.
            if 'id' in columns:
                f.write(f"SELECT setval(pg_get_serial_sequence('{table}', 'id'), 1, true);\n")
            continue

        column_list = ', '.join(f'"{c}"' for c in columns)
        for row_data in rows:
            values = []
            for value in row_data:
                if value is None:
                    values.append('NULL')
                elif isinstance(value, bool):
                    values.append('TRUE' if value else 'FALSE')
                elif isinstance(value, (int, float)):
                    values.append(str(value))
                elif isinstance(value, str):
                    escaped = value.replace("\\", "\\\\").replace("'", "''")
                    values.append(f"'{escaped}'")
                else:
                    text = str(value).replace("'", "''")
                    values.append(f"'{text}'")
            f.write(f'INSERT INTO "{table}" ({column_list}) VALUES ({', '.join(values)});\n')

        if 'id' in columns:
            f.write(f"SELECT setval(pg_get_serial_sequence('{table}', 'id'), COALESCE((SELECT MAX(id) FROM \"{table}\"), 1), true);\n")

        f.write('\n')

    f.write('COMMIT;\n')

print(f'Generated PostgreSQL import script at: {out_file}')
