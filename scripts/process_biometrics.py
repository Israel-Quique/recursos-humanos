#!/usr/bin/env python3
import argparse
import json
from pathlib import Path

import pandas as pd


def normalize_columns(frame):
    frame.columns = [str(col).strip() for col in frame.columns]
    return frame


def first_column(columns, options):
    lower_map = {str(col).strip().lower(): col for col in columns}
    for option in options:
        match = lower_map.get(option.lower())
        if match:
            return match
    return None


parser = argparse.ArgumentParser(description='Procesa un Excel de marcaciones y emite JSON resumido')
parser.add_argument('input', help='Archivo Excel (xlsx/csv)')
parser.add_argument('--output', '-o', dest='output', required=True)
args = parser.parse_args()

input_path = Path(args.input)
output_path = Path(args.output)

if not input_path.exists():
    print(json.dumps({'error': 'Archivo no encontrado'}))
    raise SystemExit(1)

if input_path.suffix.lower() in ['.xls', '.xlsx']:
    df = pd.read_excel(input_path)
else:
    df = pd.read_csv(input_path)

df = normalize_columns(df)

tiempo_col = first_column(df.columns, ['Tiempo', 'FechaHora', 'fecha_hora', 'Fecha y Hora', 'Datetime'])
fecha_col = first_column(df.columns, ['Fecha', 'fecha'])
hora_col = first_column(df.columns, ['Hora', 'hora'])
codigo_col = first_column(df.columns, ['ID de Usuario', 'Codigo', 'codigo', 'id_externo', 'ID'])
nombre_col = first_column(df.columns, ['Nombre', 'nombre', 'Empleado', 'Funcionario'])

if tiempo_col:
    df['fecha_hora'] = pd.to_datetime(df[tiempo_col], errors='coerce')
elif fecha_col and hora_col:
    df['fecha_hora'] = pd.to_datetime(
        df[fecha_col].astype(str).str.strip() + ' ' + df[hora_col].astype(str).str.strip(),
        errors='coerce',
    )
elif fecha_col:
    df['fecha_hora'] = pd.to_datetime(df[fecha_col], errors='coerce')
elif 'fecha_hora' in df.columns:
    df['fecha_hora'] = pd.to_datetime(df['fecha_hora'], errors='coerce')
else:
    df['fecha_hora'] = pd.NaT

marks = []
for _, row in df.iterrows():
    if pd.isna(row.get('fecha_hora')):
        continue

    marks.append({
        'codigo': str(row.get(codigo_col) or row.get('codigo') or row.get('id_externo') or ''),
        'fecha_hora': row['fecha_hora'].isoformat(),
        'tipo': 'entrada',
        'metodo_verificacion': 'huella',
        'datos_originales': row.to_dict(),
    })

if codigo_col:
    employees = len(df[codigo_col].dropna().unique())
elif nombre_col:
    employees = len(df[nombre_col].dropna().unique())
else:
    employees = 0

summary = {
    'valid_rows': len(marks),
    'employees': employees,
    'duplicates': 0,
}

result = {'marks': marks, 'summary': summary}
with open(output_path, 'w', encoding='utf-8') as fh:
    json.dump(result, fh, ensure_ascii=False, indent=2)

print('OK')
