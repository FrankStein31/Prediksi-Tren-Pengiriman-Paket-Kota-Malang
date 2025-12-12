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

def load_historical_data(kecamatan, weeks_back=52):
    """Load data historis dari database weekly_shipment_data"""
    connection = get_db_connection()
    if connection is None:
        return None
    
    try:
        # Query to get weekly data from database
        query = """
        SELECT 
            week_start,
            week_end,
            year,
            week_number,
            total_paket
        FROM weekly_shipment_data
        WHERE kecamatan = %s
        ORDER BY week_start DESC
        LIMIT %s
        """
        
        df = pd.read_sql(query, connection, params=(kecamatan, weeks_back))
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


def generate_visualization_data(kecamatan, weeks_historical=52, weeks_forecast=4):
    """Generate data untuk visualisasi chart menggunakan pre-trained model"""
    
    # Load historical data from database
    historical_df = load_historical_data(kecamatan, weeks_historical)
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
