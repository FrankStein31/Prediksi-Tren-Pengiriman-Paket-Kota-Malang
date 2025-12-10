#!/usr/bin/env python3
"""
Script untuk visualisasi data historis dan prediksi Prophet
Menampilkan 52 minggu historis + 4 minggu prediksi
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
import warnings
warnings.filterwarnings('ignore')

def load_model(kecamatan):
    """Load model Prophet untuk kecamatan tertentu"""
    models_dir = os.path.join(os.path.dirname(__file__), '..', 'models')
    model_filename = f"prophet_model_{kecamatan.upper()}.pkl"
    model_path = os.path.join(models_dir, model_filename)
    
    if not os.path.exists(model_path):
        return None
    
    try:
        model = joblib.load(model_path)
        return model
    except Exception as e:
        print(json.dumps({'error': f'Error loading model: {str(e)}'}), file=sys.stderr)
        return None

def load_historical_data(kecamatan, weeks_back=52):
    """Load data historis dari CSV"""
    data_dir = os.path.join(os.path.dirname(__file__), '..', 'data')
    
    # Try to load weekly aggregated data
    weekly_file = os.path.join(data_dir, 'df_kecamatan_weekly.xlsx')
    
    try:
        # Read Excel file
        df = pd.read_excel(weekly_file)
        
        # Filter by kecamatan
        df_kec = df[df['Kecamatan'] == kecamatan].copy()
        
        if df_kec.empty:
            return None
        
        # Ensure Week_Start is datetime
        df_kec['Week_Start'] = pd.to_datetime(df_kec['Week_Start'])
        
        # Sort by date
        df_kec = df_kec.sort_values('Week_Start')
        
        # Get last N weeks
        if len(df_kec) > weeks_back:
            df_kec = df_kec.tail(weeks_back)
        
        return df_kec
        
    except Exception as e:
        print(json.dumps({'error': f'Error loading data: {str(e)}'}), file=sys.stderr)
        return None

def generate_visualization_data(kecamatan, weeks_historical=52, weeks_forecast=4):
    """Generate data untuk visualisasi chart"""
    
    # Load model
    model = load_model(kecamatan)
    if model is None:
        return {'error': f'Model untuk kecamatan {kecamatan} tidak ditemukan'}
    
    # Load historical data
    historical_df = load_historical_data(kecamatan, weeks_historical)
    if historical_df is None:
        return {'error': f'Data historis untuk kecamatan {kecamatan} tidak ditemukan'}
    
    try:
        # Get the last date in historical data
        last_date = historical_df['Week_Start'].max()
        
        # Create future dates for forecast
        future_dates = pd.date_range(
            start=last_date + timedelta(weeks=1),
            periods=weeks_forecast,
            freq='W-MON'
        )
        
        # Create dataframe for Prophet prediction (ds, y format)
        # Prophet needs historical data to make predictions
        historical_prophet = pd.DataFrame({
            'ds': historical_df['Week_Start'],
            'y': historical_df['Total_Paket']
        })
        
        # Create future dataframe for forecast
        future_df = pd.DataFrame({
            'ds': future_dates
        })
        
        # Combine for prediction
        full_df = pd.concat([historical_prophet, future_df], ignore_index=True)
        
        # Make predictions
        forecast = model.predict(full_df)
        
        # Prepare historical data for chart
        historical_data = []
        for idx, row in historical_df.iterrows():
            historical_data.append({
                'date': row['Week_Start'].strftime('%Y-%m-%d'),
                'actual': int(row['Total_Paket']),
                'week_number': row['Week_Start'].isocalendar()[1],
                'year': row['Week_Start'].year
            })
        
        # Prepare forecast data for chart
        forecast_data = []
        forecast_future = forecast.tail(weeks_forecast)
        
        for idx, row in forecast_future.iterrows():
            forecast_data.append({
                'date': row['ds'].strftime('%Y-%m-%d'),
                'predicted': max(0, int(round(row['yhat']))),  # Ensure non-negative
                'lower_bound': max(0, int(round(row['yhat_lower']))),
                'upper_bound': max(0, int(round(row['yhat_upper']))),
                'week_number': row['ds'].isocalendar()[1],
                'year': row['ds'].year
            })
        
        # Calculate statistics
        total_historical = int(historical_df['Total_Paket'].sum())
        avg_weekly = int(historical_df['Total_Paket'].mean())
        total_forecast = sum([d['predicted'] for d in forecast_data])
        
        # Prepare response
        response = {
            'kecamatan': kecamatan,
            'historical': historical_data,
            'forecast': forecast_data,
            'statistics': {
                'total_historical': total_historical,
                'average_weekly': avg_weekly,
                'total_forecast': total_forecast,
                'weeks_historical': len(historical_data),
                'weeks_forecast': len(forecast_data),
                'date_range_start': historical_data[0]['date'] if historical_data else None,
                'date_range_end': historical_data[-1]['date'] if historical_data else None,
                'forecast_start': forecast_data[0]['date'] if forecast_data else None,
                'forecast_end': forecast_data[-1]['date'] if forecast_data else None
            }
        }
        
        return response
        
    except Exception as e:
        return {
            'error': f'Error generating visualization: {str(e)}',
            'traceback': str(e)
        }

def main():
    parser = argparse.ArgumentParser(description='Generate Prophet visualization data')
    parser.add_argument('--kecamatan', type=str, required=True, 
                       choices=['BLIMBING', 'KEDUNGKANDANG', 'KLOJEN', 'LOWOKWARU', 'SUKUN'],
                       help='Kecamatan name')
    parser.add_argument('--weeks_historical', type=int, default=52,
                       help='Number of historical weeks to show (default: 52)')
    parser.add_argument('--weeks_forecast', type=int, default=4,
                       help='Number of weeks to forecast (default: 4)')
    
    args = parser.parse_args()
    
    # Generate data
    result = generate_visualization_data(
        args.kecamatan,
        args.weeks_historical,
        args.weeks_forecast
    )
    
    # Output as JSON
    print(json.dumps(result, indent=2))

if __name__ == '__main__':
    main()
