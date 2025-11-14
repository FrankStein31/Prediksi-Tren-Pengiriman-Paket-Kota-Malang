#!/usr/bin/env python3
"""
Script untuk training model Prophet dan menyimpan model yang sudah dilatih
Jalankan sekali saja untuk membuat model
"""

import pandas as pd
import numpy as np
from prophet import Prophet
from prophet.make_holidays import make_holidays_df
import joblib
import os
import sys

def train_and_save_model():
    """Train Prophet model untuk setiap kecamatan dan simpan model"""
    
    # Load data
    data_path = os.path.join(os.path.dirname(__file__), '..', 'data', 'df_kecamatan_weekly.xlsx')
    if not os.path.exists(data_path):
        print(f"Error: Data file not found at {data_path}")
        return False
    
    df = pd.read_excel(data_path)
    df['Tgl_Kirim'] = pd.to_datetime(df['Tgl_Kirim'])
    
    # Holiday otomatis Indonesia
    holidays = make_holidays_df(
        year_list=[2021, 2022, 2023, 2024, 2025],
        country='ID'
    )
    
    # Directory untuk menyimpan model
    models_dir = os.path.join(os.path.dirname(__file__), '..', 'models')
    os.makedirs(models_dir, exist_ok=True)
    
    # Optimal hyperparameters untuk setiap kecamatan (dari optimasi)
    optimal_params = {
        'KEDUNGKANDANG': {
            'changepoint_prior_scale': 0.5,
            'seasonality_prior_scale': 1.0,
            'seasonality_mode': 'additive',
            'n_changepoints': 25
        },
        'SUKUN': {
            'changepoint_prior_scale': 0.001,
            'seasonality_prior_scale': 0.01,
            'seasonality_mode': 'multiplicative',
            'n_changepoints': 100
        },
        'BLIMBING': {
            'changepoint_prior_scale': 0.01,
            'seasonality_prior_scale': 10.0,
            'seasonality_mode': 'additive',
            'n_changepoints': 25
        },
        'LOWOKWARU': {
            'changepoint_prior_scale': 0.8,
            'seasonality_prior_scale': 10.0,
            'seasonality_mode': 'multiplicative',
            'n_changepoints': 25
        },
        'KLOJEN': {
            'changepoint_prior_scale': 0.8,
            'seasonality_prior_scale': 10.0,
            'seasonality_mode': 'multiplicative',
            'n_changepoints': 25
        }
    }
    
    unique_kecamatan = df['Kecamatan'].unique()
    trained_models = {}
    
    for kecamatan in unique_kecamatan:
        print(f"Training model untuk kecamatan: {kecamatan}")
        
        # Filter data untuk kecamatan
        df_filtered = df[df['Kecamatan'] == kecamatan].copy()
        
        # Prepare data untuk Prophet
        train_prophet = df_filtered[['Tgl_Kirim', 'total paket']].copy()
        train_prophet.columns = ['ds', 'y']
        
        # Get optimal parameters for this kecamatan
        params = optimal_params.get(kecamatan, {
            'changepoint_prior_scale': 0.8,
            'seasonality_prior_scale': 10.0,
            'seasonality_mode': 'multiplicative',
            'n_changepoints': 25
        })
        
        # Create and train model with optimal parameters
        model = Prophet(
            yearly_seasonality=True,
            weekly_seasonality=True,
            daily_seasonality=False,
            holidays=holidays,
            **params
        )
        
        try:
            model.fit(train_prophet)
            
            # Save model
            model_filename = f"prophet_model_{kecamatan.replace(' ', '_').replace(',', '')}.pkl"
            model_path = os.path.join(models_dir, model_filename)
            joblib.dump(model, model_path)
            
            trained_models[kecamatan] = model_path
            print(f"✓ Model untuk {kecamatan} berhasil disimpan: {model_filename}")
            
        except Exception as e:
            print(f"✗ Error training model untuk {kecamatan}: {str(e)}")
            continue
    
    print(f"\nTraining selesai! {len(trained_models)} model berhasil dibuat.")
    return True

if __name__ == "__main__":
    success = train_and_save_model()
    sys.exit(0 if success else 1)
