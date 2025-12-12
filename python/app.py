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


def load_historical_data(kecamatan, weeks_back=52):
    """Load historical data from database"""
    connection = get_db_connection()
    if connection is None:
        return None
    
    try:
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
        "weeks_forecast": 4
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
        
        # Validation
        if not kecamatan:
            return jsonify({'error': 'Kecamatan is required'}), 400
        
        if kecamatan not in KECAMATANS:
            return jsonify({'error': f'Invalid kecamatan. Must be one of: {", ".join(KECAMATANS)}'}), 400
        
        if not (12 <= weeks_historical <= 104):
            return jsonify({'error': 'weeks_historical must be between 12 and 104'}), 400
        
        if not (1 <= weeks_forecast <= 52):
            return jsonify({'error': 'weeks_forecast must be between 1 and 52'}), 400
        
        # Load historical data
        logger.info(f'Loading data for {kecamatan}...')
        historical_df = load_historical_data(kecamatan, weeks_historical)
        
        if historical_df is None:
            return jsonify({'error': f'No historical data found for {kecamatan}'}), 404
        
        # Load model
        logger.info(f'Loading model for {kecamatan}...')
        model, error = load_prophet_model(kecamatan)
        
        if model is None:
            return jsonify({'error': error}), 404
        
        # Generate predictions
        logger.info(f'Generating predictions for {kecamatan}...')
        last_date = historical_df['week_start'].max()
        
        future_dates = pd.date_range(
            start=last_date + timedelta(weeks=1),
            periods=weeks_forecast,
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
        forecast_data = []
        for idx, row in forecast.iterrows():
            forecast_data.append({
                'date': row['ds'].strftime('%Y-%m-%d'),
                'predicted': max(0, int(round(row['yhat']))),
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
            'success': True,
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
            },
            'generated_at': datetime.now().isoformat()
        }
        
        logger.info(f'Prediction generated successfully for {kecamatan}')
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
