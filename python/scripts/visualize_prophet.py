#!/usr/bin/env python3
"""
Script untuk visualisasi data historis dan prediksi Prophet
Menampilkan 52 minggu historis + 4 minggu prediksi
Menggunakan model Prophet yang sudah di-train (.pkl files)
"""

import pandas as pd
import numpy as np
from prophet import Prophet
import mysql.connector
import joblib
import os
import sys
import json
import argparse
from datetime import datetime, timedelta
import warnings
warnings.filterwarnings('ignore')

def get_db_connection():
    """Create database connection"""
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='prediksi_paket',
            user='root',
            password=''
        )
        return connection
    except Exception as e:
        print(json.dumps({'error': f'Database connection error: {str(e)}'}), file=sys.stderr)
        return None

def load_historical_data(kecamatan, weeks_back=52, reference_date=None):
    """Load data historis dari database weekly_shipment_data
    
    Args:
        kecamatan: Nama kecamatan
        weeks_back: Jumlah minggu yang ingin ditampilkan
        reference_date: Tanggal referensi (default: awal minggu ini)
    """
    connection = get_db_connection()
    if connection is None:
        return None
    
    try:
        # Use reference date or start of current week
        if reference_date is None:
            # Get start of current week (Monday)
            today = datetime.now()
            start_of_week = today - timedelta(days=today.weekday())
            reference_date = start_of_week
        else:
            reference_date = pd.to_datetime(reference_date)
        
        # Query to get weekly data before reference date
        query = """
        SELECT 
            week_start,
            week_end,
            year,
            week_number,
            total_paket
        FROM weekly_shipment_data
        WHERE kecamatan = %s AND week_start <= %s
        ORDER BY week_start DESC
        LIMIT %s
        """
        
        df = pd.read_sql(query, connection, params=(kecamatan, reference_date, weeks_back))
        connection.close()
        
        if df.empty:
            return None
        
        # Sort ascending for time series
        df = df.sort_values('week_start')
        
        # Ensure datetime
        df['week_start'] = pd.to_datetime(df['week_start'])
        df['week_end'] = pd.to_datetime(df['week_end'])
        
        return df
        
    except Exception as e:
        if connection:
            connection.close()
        print(json.dumps({'error': f'Error loading data: {str(e)}'}), file=sys.stderr)
        return None

def load_prophet_model(kecamatan):
    """Load pre-trained Prophet model dari file .pkl"""
    models_dir = os.path.join(os.path.dirname(__file__), '..', 'models')
    model_filename = f"prophet_model_{kecamatan.upper()}.pkl"
    model_path = os.path.join(models_dir, model_filename)
    
    if not os.path.exists(model_path):
        return None, f"Model file not found: {model_path}"
    
    try:
        model = joblib.load(model_path)
        return model, None
    except Exception as e:
        return None, f"Error loading model: {str(e)}"


def generate_visualization_data(kecamatan, weeks_historical=52, weeks_forecast=4, reference_date=None, date_mode='realtime'):
    """Generate data untuk visualisasi chart menggunakan pre-trained model
    
    Args:
        kecamatan: Nama kecamatan
        weeks_historical: Jumlah minggu historis (max 52)
        weeks_forecast: Jumlah minggu prediksi (max 8)
        reference_date: Tanggal referensi untuk custom date (bisa masa depan)
        date_mode: 'realtime' atau 'custom'
    """
    
    # Validate ranges
    weeks_historical = min(max(weeks_historical, 4), 52)
    weeks_forecast = min(max(weeks_forecast, 1), 8)
    
    # Determine reference date
    if date_mode == 'custom' and reference_date is not None:
        ref_date = pd.to_datetime(reference_date)
    else:
        # Real-time mode: use start of current week (Monday)
        today = datetime.now()
        start_of_week = today - timedelta(days=today.weekday())
        ref_date = start_of_week
    
    # Load historical data from database
    historical_df = load_historical_data(kecamatan, weeks_historical, ref_date)
    if historical_df is None:
        return {'error': f'Data historis untuk kecamatan {kecamatan} tidak ditemukan'}
    
    # Load pre-trained Prophet model
    model, error = load_prophet_model(kecamatan)
    if model is None:
        return {'error': error}
    
    try:
        # Get the last date in historical data
        last_date = historical_df['week_start'].max()
        
        # Create future dataframe starting from last_date + 1 week
        future_dates = pd.date_range(
            start=last_date + timedelta(weeks=1),
            periods=weeks_forecast,
            freq='W'
        )
        
        # Create dataframe for predictions
        future_df = pd.DataFrame({'ds': future_dates})
        
        # Make predictions for future dates only
        forecast = model.predict(future_df)
        
        # Prepare historical data for chart
        historical_data = []
        for idx, row in historical_df.iterrows():
            historical_data.append({
                'date': row['week_start'].strftime('%Y-%m-%d'),
                'actual': int(row['total_paket']),
                'week_number': int(row['week_number']),
                'year': int(row['year'])
            })
        
        # Prepare forecast data for chart
        forecast_data = []
        for idx, row in forecast.iterrows():
            forecast_data.append({
                'date': row['ds'].strftime('%Y-%m-%d'),
                'predicted': max(0, int(round(row['yhat']))),  # Ensure non-negative
                'lower_bound': max(0, int(round(row['yhat_lower']))),
                'upper_bound': max(0, int(round(row['yhat_upper']))),
                'week_number': row['ds'].isocalendar()[1],
                'year': row['ds'].year
            })
        
        # Calculate statistics
        total_historical = int(historical_df['total_paket'].sum())
        avg_weekly = int(historical_df['total_paket'].mean())
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
                       help='Number of historical weeks to show (max: 52, default: 52)')
    parser.add_argument('--weeks_forecast', type=int, default=4,
                       help='Number of weeks to forecast (max: 8, default: 4)')
    parser.add_argument('--date_mode', type=str, default='realtime',
                       choices=['realtime', 'custom'],
                       help='Date mode: realtime (today) or custom date')
    parser.add_argument('--custom_date', type=str, default=None,
                       help='Custom reference date (YYYY-MM-DD format)')
    
    args = parser.parse_args()
    
    # Generate data
    result = generate_visualization_data(
        kecamatan=args.kecamatan,
        weeks_historical=args.weeks_historical,
        weeks_forecast=args.weeks_forecast,
        reference_date=args.custom_date,
        date_mode=args.date_mode
    )
    
    # Output as JSON
    print(json.dumps(result, indent=2))

if __name__ == '__main__':
    main()
