#!/usr/bin/env python3
import argparse
import json
from pathlib import Path

from zk import ZK


def split_name(full_name):
    value = str(full_name or "").strip()
    if not value:
        return "", ""

    parts = value.split()
    if len(parts) == 1:
        return parts[0], ""

    if len(parts) >= 3:
        return " ".join(parts[:-2]), " ".join(parts[-2:])

    return parts[0], parts[1]


def serialize_user(user):
    user_id = str(getattr(user, "user_id", "") or "").strip()
    name = str(getattr(user, "name", "") or "").strip()
    first_name, last_name = split_name(name)

    return {
        "codigo": user_id,
        "nombre_completo": name,
        "nombre": first_name,
        "apellido": last_name,
        "numero_tarjeta": str(getattr(user, "card", "") or "").strip(),
    }


def serialize_attendance(record, users_by_code):
    timestamp = getattr(record, "timestamp", None)
    codigo = str(getattr(record, "user_id", "") or "").strip()
    user = users_by_code.get(codigo, {})

    return {
        "uid": getattr(record, "uid", ""),
        "codigo": codigo,
        "nombre": user.get("nombre", ""),
        "apellido": user.get("apellido", ""),
        "nombre_completo": user.get("nombre_completo", ""),
        "numero_tarjeta": user.get("numero_tarjeta", ""),
        "fecha_hora": timestamp.isoformat() if timestamp else "",
        "estado": str(getattr(record, "status", "") or ""),
        "punch": str(getattr(record, "punch", "") or ""),
    }


def main():
    parser = argparse.ArgumentParser(description="Extrae marcaciones desde un ZKTeco y las guarda en JSON")
    parser.add_argument("--ip", required=True)
    parser.add_argument("--port", type=int, default=4370)
    parser.add_argument("--password", default="0")
    parser.add_argument("--timeout", type=int, default=10)
    parser.add_argument("--output", required=True)
    parser.add_argument("--probe-only", action="store_true")
    args = parser.parse_args()

    output_path = Path(args.output)
    password = int(str(args.password).strip() or "0")
    attempts = [
        {"force_udp": False, "ommit_ping": False},
        {"force_udp": False, "ommit_ping": True},
        {"force_udp": True, "ommit_ping": True},
    ]

    errors = []

    for attempt in attempts:
        conn = None
        try:
            zk = ZK(
                args.ip,
                port=args.port,
                timeout=args.timeout,
                password=password,
                force_udp=attempt["force_udp"],
                ommit_ping=attempt["ommit_ping"],
            )
            conn = zk.connect()
            users = [] if args.probe_only else (conn.get_users() or [])
            users_by_code = {
                user["codigo"]: user
                for user in (serialize_user(item) for item in users)
                if user.get("codigo")
            }
            attendance = [] if args.probe_only else (conn.get_attendance() or [])
            rows = [] if args.probe_only else [serialize_attendance(item, users_by_code) for item in attendance]
            output_path.write_text(
                json.dumps(
                    {
                        "ok": True,
                        "message": "Sesion ZKTeco abierta correctamente" if args.probe_only else "Marcaciones extraidas correctamente",
                        "rows": rows,
                        "users_count": len(users_by_code),
                        "transport": "udp" if attempt["force_udp"] else "tcp",
                        "ommit_ping": attempt["ommit_ping"],
                    },
                    ensure_ascii=False,
                    indent=2,
                ),
                encoding="utf-8",
            )
            print("OK")
            return 0
        except Exception as exc:
            errors.append(
                {
                    "transport": "udp" if attempt["force_udp"] else "tcp",
                    "ommit_ping": attempt["ommit_ping"],
                    "error": str(exc),
                }
            )
        finally:
            if conn is not None:
                try:
                    conn.disconnect()
                except Exception:
                    pass

    output_path.write_text(
        json.dumps(
            {
                "ok": False,
                "message": "No se pudo abrir una sesion ZKTeco con el equipo.",
                "attempts": errors,
            },
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )
    print(json.dumps({"ok": False, "attempts": errors}, ensure_ascii=False))
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
