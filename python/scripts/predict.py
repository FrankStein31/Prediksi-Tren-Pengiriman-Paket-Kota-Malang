#!/usr/bin/env python3
"""
Script untuk prediksi menggunakan model Prophet yang sudah dilatih
Dipanggil oleh Laravel via route
"""

import pandas as pd
import numpy as np
from prophet import Prophet
import joblib
import os
import sys
import json
import argparse
from datetime import datetime, timedelta

def load_model(kecamatan):
    """Load model Prophet untuk kecamatan tertentu"""
    models_dir = os.path.join(os.path.dirname(__file__), '..', 'models')
    model_filename = f"prophet_model_{kecamatan.replace(' ', '_').replace(',', '')}.pkl"
    model_path = os.path.join(models_dir, model_filename)
    
    if not os.path.exists(model_path):
        return None
    
    try:
        model = joblib.load(model_path)
        return model
    except Exception as e:
        print(f"Error loading model: {str(e)}")
        return None

def predict_packages(kecamatan, weeks_ahead=12):
    """Prediksi jumlah paket untuk kecamatan dalam beberapa minggu ke depan"""
    
    # Load model
    model = load_model(kecamatan)
    if model is None:
        return {
            'error': f'Model untuk kecamatan {kecamatan} tidak ditemukan'
        }
    
    try:
        # Buat future dataframe
        future = model.make_future_dataframe(periods=weeks_ahead, freq='W')
        
        # Prediksi
        forecast = model.predict(future)
        
        # Ambil prediksi untuk periode yang diminta (minggu ke depan)
        predictions = forecast.tail(weeks_ahead)
        
        # Format hasil
        results = []
        for idx, row in predictions.iterrows():
            results.append({
                'tanggal': row['ds'].strftime('%Y-%m-%d'),
                'prediksi': round(max(0, row['yhat']), 0),  # Pastikan tidak negatif
                'lower_bound': round(max(0, row['yhat_lower']), 0),
                'upper_bound': round(max(0, row['yhat_upper']), 0),
                'minggu_ke': row['ds'].isocalendar()[1]
            })
        
        return {
            'kecamatan': kecamatan,
            'weeks_ahead': weeks_ahead,
            'predictions': results,
            'success': True
        }
        
    except Exception as e:
        return {
            'error': f'Error saat prediksi: {str(e)}'
        }

def get_available_kecamatan():
    """Dapatkan list kecamatan yang tersedia (yang ada modelnya)"""
    models_dir = os.path.join(os.path.dirname(__file__), '..', 'models')
    
    if not os.path.exists(models_dir):
        return []
    
    available_kecamatan = []
    for filename in os.listdir(models_dir):
        if filename.startswith('prophet_model_') and filename.endswith('.pkl'):
            # Extract kecamatan name from filename
            kecamatan_name = filename.replace('prophet_model_', '').replace('.pkl', '').replace('_', ' ')
            available_kecamatan.append(kecamatan_name)
    
    return available_kecamatan

def main():
    parser = argparse.ArgumentParser(description='Prediksi pengiriman paket')
    parser.add_argument('--kecamatan', type=str, help='Nama kecamatan')
    parser.add_argument('--weeks', type=int, default=12, help='Jumlah minggu prediksi (default: 12)')
    parser.add_argument('--list-kecamatan', action='store_true', help='Tampilkan list kecamatan yang tersedia')
    
    args = parser.parse_args()
    
    if args.list_kecamatan:
        available = get_available_kecamatan()
        result = {
            'available_kecamatan': available,
            'total': len(available)
        }
        print(json.dumps(result, indent=2))
        return
    
    if not args.kecamatan:
        print(json.dumps({'error': 'Parameter --kecamatan harus disediakan'}))
        return
    
    # Lakukan prediksi
    result = predict_packages(args.kecamatan, args.weeks)
    print(json.dumps(result, indent=2))

if __name__ == "__main__":
    main()
