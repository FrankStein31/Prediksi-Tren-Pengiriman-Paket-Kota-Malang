# Script untuk training model Prophet dan menyimpan model yang sudah dilatih
# Jalankan sekali saja untuk membuat model
# Parameter optimal dari hasil grid search notebook hot-winters(ETs).ipynb

import pandas as pd
import numpy as np
from prophet import Prophet
from prophet.make_holidays import make_holidays_df
import joblib
import os
import sys
import warnings
warnings.filterwarnings("ignore")

def train_and_save_model():
    """
    Train Prophet model untuk setiap kecamatan dengan parameter optimal
    dan simpan model ke file .pkl untuk produksi
    """
    
    # Load data
    data_path = os.path.join(os.path.dirname(__file__), '..', 'data', 'data_kiriman_converted.csv')
    
    # Fallback ke Excel jika CSV tidak ada
    if not os.path.exists(data_path):
        data_path = os.path.join(os.path.dirname(__file__), '..', 'data', 'data_kiriman.xlsx')
    
    if not os.path.exists(data_path):
        print(f"Error: Data tidak ditemukan di {data_path}")
        return False
    
    print(f"Loading data dari: {data_path}")
    
    # Load data
    if data_path.endswith('.csv'):
        df = pd.read_csv(data_path)
    else:
        df = pd.read_excel(data_path)
    
    # Preprocessing data
    print("Preprocessing data...")
    df = df[['Kota', 'Cek', 'Tgl_Kirim']]
    df['Tgl_Kirim'] = pd.to_datetime(df['Tgl_Kirim'])
    df['Kecamatan'] = df['Kota'].apply(lambda x: x.split(',')[1].strip() if len(x.split(',')) > 1 else '')
    df = df[['Kecamatan', 'Cek', 'Tgl_Kirim']]
    
    # Agregasi weekly
    df_kecamatan_weekly = df.groupby('Kecamatan').resample('W', on='Tgl_Kirim')['Cek'].count().reset_index()
    df_kecamatan_weekly.rename(columns={'Cek': 'total paket'}, inplace=True)
    
    print(f"Data berhasil diload: {len(df_kecamatan_weekly)} minggu")
    
    # Holiday Indonesia
    print("Loading holidays Indonesia...")
    holidays = make_holidays_df(
        year_list=[2021, 2022, 2023, 2024, 2025, 2026, 2027],
        country='ID'
    )
    
    # Directory untuk menyimpan model
    models_dir = os.path.join(os.path.dirname(__file__), '..', 'models')
    os.makedirs(models_dir, exist_ok=True)
    print(f"Models directory: {models_dir}")
    
    # Optimal hyperparameters untuk setiap kecamatan (dari grid search)
    # Source: hot-winters(ETs).ipynb - Cell Prophet
    print("\n" + "="*80)
    print("PARAMETER OPTIMAL DARI GRID SEARCH")
    print("="*80)
    
    optimal_params = {
        'KEDUNGKANDANG': {
            'changepoint_prior_scale': 1.0,
            'seasonality_prior_scale': 0.1,
            'seasonality_mode': 'additive',
            'n_changepoints': 150
        },
        'SUKUN': {
            'changepoint_prior_scale': 0.05,
            'seasonality_prior_scale': 0.01,
            'seasonality_mode': 'multiplicative',
            'n_changepoints': 10
        },
        'BLIMBING': {
            'changepoint_prior_scale': 1.0,
            'seasonality_prior_scale': 0.5,
            'seasonality_mode': 'additive',
            'n_changepoints': 50
        },
        'LOWOKWARU': {
            'changepoint_prior_scale': 1.0,
            'seasonality_prior_scale': 0.05,
            'seasonality_mode': 'additive',
            'n_changepoints': 75
        },
        'KLOJEN': {
            'changepoint_prior_scale': 0.001,
            'seasonality_prior_scale': 0.05,
            'seasonality_mode': 'multiplicative',
            'n_changepoints': 100
        }
    }
    
    unique_kecamatan = df_kecamatan_weekly['Kecamatan'].unique()
    trained_models = {}
    
    print(f"\nMemulai training untuk {len(unique_kecamatan)} kecamatan...")
    print("="*80)
    
    for idx, kecamatan in enumerate(unique_kecamatan, 1):
        print(f"\n[{idx}/{len(unique_kecamatan)}] Training model: {kecamatan}")
        print("-"*80)
        
        # Filter data untuk kecamatan
        df_filtered = df_kecamatan_weekly[df_kecamatan_weekly['Kecamatan'] == kecamatan].copy()
        
        # Prepare data untuk Prophet
        train_prophet = df_filtered[['Tgl_Kirim', 'total paket']].copy()
        train_prophet.columns = ['ds', 'y']
        
        print(f"   Data: {len(train_prophet)} minggu ({train_prophet['ds'].min().date()} s/d {train_prophet['ds'].max().date()})")
        print(f"   Total paket: {train_prophet['y'].sum():.0f} (rata-rata: {train_prophet['y'].mean():.1f}/minggu)")
        
        # Get optimal parameters for this kecamatan
        params = optimal_params.get(kecamatan, {
            'changepoint_prior_scale': 0.5,
            'seasonality_prior_scale': 1.0,
            'seasonality_mode': 'additive',
            'n_changepoints': 25
        })
        
        print(f"   Parameters:")
        print(f"      - changepoint_prior_scale: {params['changepoint_prior_scale']}")
        print(f"      - seasonality_prior_scale: {params['seasonality_prior_scale']}")
        print(f"      - seasonality_mode: {params['seasonality_mode']}")
        print(f"      - n_changepoints: {params['n_changepoints']}")
        
        # Create and train model with optimal parameters
        model = Prophet(
            yearly_seasonality=True,
            weekly_seasonality=True,
            daily_seasonality=False,
            holidays=holidays,
            **params
        )
        
        try:
            print(f"   Training model...")
            model.fit(train_prophet)
            
            # Save model
            model_filename = f"prophet_model_{kecamatan}.pkl"
            model_path = os.path.join(models_dir, model_filename)
            joblib.dump(model, model_path)
            
            # Get file size
            file_size = os.path.getsize(model_path) / 1024  # KB
            
            trained_models[kecamatan] = {
                'path': model_path,
                'filename': model_filename,
                'size_kb': file_size,
                'params': params
            }
            
            print(f"   Model berhasil disimpan: {model_filename} ({file_size:.1f} KB)")
            
        except Exception as e:
            print(f"   Error training model untuk {kecamatan}: {str(e)}")
            import traceback
            traceback.print_exc()
            continue
    
    # Summary
    print("\n" + "="*80)
    print("RINGKASAN TRAINING")
    print("="*80)
    print(f"Berhasil: {len(trained_models)}/{len(unique_kecamatan)} model")
    
    if trained_models:
        total_size = sum(m['size_kb'] for m in trained_models.values())
        print(f"Total ukuran model: {total_size:.1f} KB ({total_size/1024:.2f} MB)")
        print(f"Lokasi: {models_dir}")
        print("\nModel yang berhasil dibuat:")
        for kec, info in trained_models.items():
            print(f"   {info['filename']} ({info['size_kb']:.1f} KB)")
    
    print("="*80)
    print("\nTraining selesai!")
    
    return len(trained_models) > 0

if __name__ == "__main__":
    success = train_and_save_model()
    sys.exit(0 if success else 1)
