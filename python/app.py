"""
Flask API for Prophet Prediction
Provides endpoints for Laravel to get shipment predictions
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
import mysql.connector
import joblib
import os
import sys
from datetime import datetime, timedelta
import warnings
import logging

warnings.filterwarnings('ignore')

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)  # Enable CORS for Laravel integration

# Configuration
DB_CONFIG = {
    'host': 'localhost',
    'database': 'prediksi_paket',
    'user': 'root',
    'password': ''
}

MODELS_DIR = os.path.join(os.path.dirname(__file__), 'models')
KECAMATANS = ['BLIMBING', 'KEDUNGKANDANG', 'KLOJEN', 'LOWOKWARU', 'SUKUN']


def get_db_connection():
    """Create database connection"""
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except Exception as e:
        logger.error(f'Database connection error: {str(e)}')
        return None


def load_historical_data(kecamatan, weeks_back=52, reference_date=None, is_future_date=False):
    """Load historical data from database
    
    Args:
        kecamatan: Nama kecamatan
        weeks_back: Jumlah minggu yang ingin ditampilkan
        reference_date: Tanggal referensi (default: awal minggu ini)
        is_future_date: True if reference_date is in the future
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
        
        # For future dates, get the latest available data
        if is_future_date:
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
            logger.info(f'Future date detected. Loading latest {weeks_back} weeks of available data.')
            df = pd.read_sql(query, connection, params=(kecamatan, weeks_back))
        else:
            # For past/current dates, filter by reference date
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
        logger.error(f'Error loading data: {str(e)}')
        return None


def load_prophet_model(kecamatan):
    """Load pre-trained Prophet model"""
    model_filename = f"prophet_model_{kecamatan.upper()}.pkl"
    model_path = os.path.join(MODELS_DIR, model_filename)
    
    if not os.path.exists(model_path):
        return None, f"Model file not found: {model_path}"
    
    try:
        model = joblib.load(model_path)
        return model, None
    except Exception as e:
        return None, f"Error loading model: {str(e)}"


@app.route('/')
def index():
    """API Info"""
    return jsonify({
        'name': 'Prophet Prediction API',
        'version': '1.0.0',
        'endpoints': {
            '/': 'API information',
            '/health': 'Health check',
            '/api/predict': 'POST - Generate predictions',
            '/api/kecamatans': 'GET - List available kecamatans'
        }
    })


@app.route('/health')
def health():
    """Health check endpoint"""
    try:
        # Check database connection
        conn = get_db_connection()
        if conn:
            conn.close()
            db_status = 'OK'
        else:
            db_status = 'FAILED'
        
        # Check models
        models_status = []
        for kec in KECAMATANS:
            model_path = os.path.join(MODELS_DIR, f"prophet_model_{kec}.pkl")
            models_status.append({
                'kecamatan': kec,
                'exists': os.path.exists(model_path)
            })
        
        return jsonify({
            'status': 'healthy',
            'database': db_status,
            'models': models_status,
            'timestamp': datetime.now().isoformat()
        })
    except Exception as e:
        return jsonify({
            'status': 'unhealthy',
            'error': str(e)
        }), 500


@app.route('/api/kecamatans', methods=['GET'])
def get_kecamatans():
    """Get list of available kecamatans"""
    return jsonify({
        'kecamatans': KECAMATANS,
        'count': len(KECAMATANS)
    })


@app.route('/api/predict', methods=['POST'])
def predict():
    """
    Generate prediction for a kecamatan
    
    Request JSON:
    {
        "kecamatan": "BLIMBING",
        "weeks_historical": 52,
        "weeks_forecast": 4,
        "date_mode": "realtime",  // or "custom"
        "custom_date": "2023-10-10"  // optional, when date_mode=custom
    }
    """
    try:
        # Get request data
        data = request.get_json()
        
        if not data:
            return jsonify({'error': 'No JSON data provided'}), 400
        
        kecamatan = data.get('kecamatan')
        weeks_historical = data.get('weeks_historical', 52)
        weeks_forecast = data.get('weeks_forecast', 4)
        date_mode = data.get('date_mode', 'realtime')
        custom_date = data.get('custom_date')
        
        # Validation
        if not kecamatan:
            return jsonify({'error': 'Kecamatan is required'}), 400
        
        if kecamatan not in KECAMATANS:
            return jsonify({'error': f'Invalid kecamatan. Must be one of: {", ".join(KECAMATANS)}'}), 400
        
        # Validate ranges (max 52 historical, max 8 forecast)
        if not (4 <= weeks_historical <= 52):
            return jsonify({'error': 'weeks_historical must be between 4 and 52'}), 400
        
        if not (1 <= weeks_forecast <= 8):
            return jsonify({'error': 'weeks_forecast must be between 1 and 8'}), 400
        
        if date_mode not in ['realtime', 'custom']:
            return jsonify({'error': 'date_mode must be "realtime" or "custom"'}), 400
        
        # Determine reference date and check if it's in the future
        is_future_date = False
        if date_mode == 'custom':
            if not custom_date:
                return jsonify({'error': 'custom_date is required when date_mode is custom'}), 400
            try:
                reference_date = pd.to_datetime(custom_date)
                # Check if reference date is in the future
                today = datetime.now()
                if reference_date > today:
                    is_future_date = True
                    logger.info(f'Future date detected: {reference_date.strftime("%Y-%m-%d")}')
            except:
                return jsonify({'error': 'Invalid date format. Use YYYY-MM-DD'}), 400
        else:
            # Real-time mode: use start of current week (Monday)
            today = datetime.now()
            start_of_week = today - timedelta(days=today.weekday())
            reference_date = start_of_week
            logger.info(f'Real-time mode: Using start of week {start_of_week.strftime("%Y-%m-%d")}')
        
        # Load historical data
        logger.info(f'Loading data for {kecamatan} (mode: {date_mode}, is_future: {is_future_date})...')
        historical_df = load_historical_data(kecamatan, weeks_historical, reference_date, is_future_date)
        
        if historical_df is None or historical_df.empty:
            return jsonify({'error': f'No historical data found for {kecamatan}'}), 404
        
        # If it's a future date, we need to predict from last data point to reference_date + weeks_forecast
        if is_future_date:
            # Get the last data point
            last_data_date = historical_df['week_start'].max()
            
            # Calculate weeks between last data and reference date
            weeks_gap = int((reference_date - last_data_date).days / 7)
            
            # Total forecast needed = weeks to reach reference_date + user requested forecast
            total_forecast_weeks = weeks_gap + weeks_forecast
            
            logger.info(f'Last data: {last_data_date.strftime("%Y-%m-%d")}, '
                       f'Reference: {reference_date.strftime("%Y-%m-%d")}, '
                       f'Gap: {weeks_gap} weeks, '
                       f'Total forecast: {total_forecast_weeks} weeks')
            
            # Update weeks_forecast for prediction
            actual_forecast_weeks = total_forecast_weeks
        else:
            actual_forecast_weeks = weeks_forecast
        
        # Load model
        logger.info(f'Loading model for {kecamatan}...')
        model, error = load_prophet_model(kecamatan)
        
        if model is None:
            return jsonify({'error': error}), 404
        
        # Generate predictions
        logger.info(f'Generating {actual_forecast_weeks} weeks of predictions for {kecamatan}...')
        last_date = historical_df['week_start'].max()
        
        future_dates = pd.date_range(
            start=last_date + timedelta(weeks=1),
            periods=actual_forecast_weeks,
            freq='W'
        )
        
        future_df = pd.DataFrame({'ds': future_dates})
        forecast = model.predict(future_df)
        
        # Prepare historical data
        historical_data = []
        for idx, row in historical_df.iterrows():
            historical_data.append({
                'date': row['week_start'].strftime('%Y-%m-%d'),
                'actual': int(row['total_paket']),
                'week_number': int(row['week_number']),
                'year': int(row['year'])
            })
        
        # Prepare forecast data
        # For future dates, only show forecast after reference_date
        forecast_data = []
        for idx, row in forecast.iterrows():
            forecast_date = row['ds']
            
            # For future reference dates, only include forecasts after the reference date
            if is_future_date:
                if forecast_date < reference_date:
                    continue  # Skip forecasts before reference date
            
            forecast_data.append({
                'date': forecast_date.strftime('%Y-%m-%d'),
                'predicted': max(0, int(round(row['yhat']))),
                'lower_bound': max(0, int(round(row['yhat_lower']))),
                'upper_bound': max(0, int(round(row['yhat_upper']))),
                'week_number': forecast_date.isocalendar()[1],
                'year': forecast_date.year
            })
            
            # Only show the requested number of forecast weeks
            if len(forecast_data) >= weeks_forecast:
                break
        
        # Calculate statistics
        total_historical = int(historical_df['total_paket'].sum())
        avg_weekly = int(historical_df['total_paket'].mean())
        total_forecast = sum([d['predicted'] for d in forecast_data])
        
        # Prepare response
        response = {
            'success': True,
            'kecamatan': kecamatan,
            'date_mode': date_mode,
            'is_future_date': is_future_date,
            'reference_date': reference_date.strftime('%Y-%m-%d') if isinstance(reference_date, (datetime, pd.Timestamp)) else reference_date,
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
            },
            'generated_at': datetime.now().isoformat()
        }
        
        logger.info(f'Prediction generated successfully for {kecamatan} '
                   f'(historical: {len(historical_data)}, forecast: {len(forecast_data)})')
        return jsonify(response)
        
    except Exception as e:
        logger.error(f'Error generating prediction: {str(e)}')
        return jsonify({
            'success': False,
            'error': 'Internal server error',
            'message': str(e)
        }), 500


@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Endpoint not found'}), 404


@app.errorhandler(500)
def internal_error(error):
    return jsonify({'error': 'Internal server error'}), 500


if __name__ == '__main__':
    print('=' * 60)
    print('🚀 Prophet Prediction API Server')
    print('=' * 60)
    print(f'Environment: Development')
    print(f'Database: {DB_CONFIG["database"]}')
    print(f'Models Directory: {MODELS_DIR}')
    print(f'Available Kecamatans: {", ".join(KECAMATANS)}')
    print('=' * 60)
    print('Starting server on http://127.0.0.1:5000')
    print('Press CTRL+C to stop')
    print('=' * 60)
    
    app.run(
        host='127.0.0.1',
        port=5000,
        debug=True
    )
