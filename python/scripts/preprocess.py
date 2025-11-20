#!/usr/bin/env python3
"""
Script untuk preprocessing data Excel
- Preview: Membaca dan menampilkan preview data
- Process: Preprocessing lengkap untuk training model
"""

import pandas as pd
import numpy as np
import json
import argparse
import os
import sys
from datetime import datetime

def preview_data(file_path):
    """Preview data Excel untuk validasi"""
    try:
        # Baca Excel
        df = pd.read_excel(file_path)
        
        # Validasi kolom required
        required_columns = ['Kota', 'Cek', 'Tgl_Kirim']
        missing_columns = [col for col in required_columns if col not in df.columns]
        
        if missing_columns:
            return {
                'error': f'Missing required columns: {", ".join(missing_columns)}',
                'columns_found': list(df.columns)
            }
        
        # Buat preview (10 baris pertama)
        preview_rows = df.head(10).to_dict('records')
        
        # Info data
        result = {
            'success': True,
            'columns': list(df.columns),
            'rows': preview_rows,
            'total_rows': len(df),
            'info': {
                'date_range': {
                    'start': str(df['Tgl_Kirim'].min()),
                    'end': str(df['Tgl_Kirim'].max())
                },
                'total_records': len(df),
                'unique_kota': df['Kota'].nunique()
            }
        }
        
        return result
        
    except Exception as e:
        return {
            'error': f'Error reading file: {str(e)}'
        }

def process_data(file_path):
    """Preprocessing data lengkap untuk training"""
    try:
        # Baca Excel
        df = pd.read_excel(file_path)
        
        # Validasi kolom
        required_columns = ['Kota', 'Cek', 'Tgl_Kirim']
        if not all(col in df.columns for col in required_columns):
            return {
                'error': 'Missing required columns'
            }
        
        # Step 1: Select kolom yang diperlukan
        df = df[['Kota', 'Cek', 'Tgl_Kirim']]
        
        # Step 2: Extract Kecamatan dari kolom Kota
        df['Kecamatan'] = df['Kota'].apply(
            lambda x: x.split(',')[1].strip() if len(x.split(',')) > 1 else ''
        )
        
        # Step 3: Filter hanya data dengan Kecamatan valid
        df = df[df['Kecamatan'] != '']
        
        # Step 4: Select kolom final
        df = df[['Kecamatan', 'Cek', 'Tgl_Kirim']]
        
        # Step 5: Convert Tgl_Kirim ke datetime
        df['Tgl_Kirim'] = pd.to_datetime(df['Tgl_Kirim'])
        
        # Step 6: Agregasi weekly
        df_kecamatan_weekly = df.groupby('Kecamatan').resample(
            'W', on='Tgl_Kirim'
        )['Cek'].count().reset_index()
        
        df_kecamatan_weekly.rename(columns={'Cek': 'total paket'}, inplace=True)
        
        # Step 7: Tambah minggu_ke
        df_kecamatan_weekly['minggu_ke'] = df_kecamatan_weekly['Tgl_Kirim'].dt.isocalendar().week.astype(int)
        
        # Step 8: Simpan hasil preprocessing
        data_dir = os.path.join(os.path.dirname(__file__), '..', 'data')
        os.makedirs(data_dir, exist_ok=True)
        
        # Backup file lama jika ada
        output_file = os.path.join(data_dir, 'df_kecamatan_weekly.xlsx')
        if os.path.exists(output_file):
            backup_file = os.path.join(data_dir, f'df_kecamatan_weekly_backup_{datetime.now().strftime("%Y%m%d_%H%M%S")}.xlsx')
            os.rename(output_file, backup_file)
        
        # Simpan file baru
        df_kecamatan_weekly.to_excel(output_file, index=False)
        
        # Statistik hasil
        stats = {
            'success': True,
            'total_rows': len(df_kecamatan_weekly),
            'kecamatan': df_kecamatan_weekly['Kecamatan'].unique().tolist(),
            'date_range': {
                'start': str(df_kecamatan_weekly['Tgl_Kirim'].min()),
                'end': str(df_kecamatan_weekly['Tgl_Kirim'].max())
            },
            'output_file': output_file,
            'summary': df_kecamatan_weekly.groupby('Kecamatan')['total paket'].agg([
                'count', 'sum', 'mean', 'min', 'max'
            ]).to_dict('index')
        }
        
        return stats
        
    except Exception as e:
        return {
            'error': f'Error processing data: {str(e)}'
        }

def main():
    parser = argparse.ArgumentParser(description='Preprocess data Excel untuk Prophet')
    parser.add_argument('--preview', action='store_true', help='Preview data saja')
    parser.add_argument('--process', action='store_true', help='Process data lengkap')
    parser.add_argument('--file', type=str, required=True, help='Path ke file Excel')
    
    args = parser.parse_args()
    
    if not os.path.exists(args.file):
        print(json.dumps({'error': 'File not found'}))
        return
    
    if args.preview:
        result = preview_data(args.file)
    elif args.process:
        result = process_data(args.file)
    else:
        result = {'error': 'Specify either --preview or --process'}
    
    print(json.dumps(result, indent=2, default=str))

if __name__ == "__main__":
    main()
