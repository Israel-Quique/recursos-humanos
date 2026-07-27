#!/usr/bin/env python3
import argparse
import json
from pathlib import Path

from zk import ZK


def serialize_attendance(record):
    timestamp = getattr(record, "timestamp", None)
    return {
        "uid": getattr(record, "uid", ""),
        "codigo": str(getattr(record, "user_id", "") or ""),
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
            attendance = [] if args.probe_only else (conn.get_attendance() or [])
            rows = [] if args.probe_only else [serialize_attendance(item) for item in attendance]
            output_path.write_text(
                json.dumps(
                    {
                        "ok": True,
                        "message": "Sesion ZKTeco abierta correctamente" if args.probe_only else "Marcaciones extraidas correctamente",
                        "rows": rows,
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
