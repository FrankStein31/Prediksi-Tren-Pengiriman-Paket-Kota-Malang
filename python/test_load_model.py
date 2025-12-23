#!/usr/bin/env python3
"""
Test script to check if Prophet models can be loaded
Run this on cPanel to diagnose the KeyError 118 issue
"""

import sys
import os

print("=" * 60)
print("Prophet Model Load Test")
print("=" * 60)
print(f"Python version: {sys.version}")
print(f"Python version info: {sys.version_info}")
print()

# Check imports
try:
    import numpy as np
    print(f"✅ numpy {np.__version__}")
except Exception as e:
    print(f"❌ numpy import failed: {e}")
    sys.exit(1)

try:
    import pandas as pd
    print(f"✅ pandas {pd.__version__}")
except Exception as e:
    print(f"❌ pandas import failed: {e}")
    sys.exit(1)

try:
    import prophet
    print(f"✅ prophet {prophet.__version__}")
except Exception as e:
    print(f"❌ prophet import failed: {e}")
    sys.exit(1)

try:
    import joblib
    print(f"✅ joblib {joblib.__version__}")
except Exception as e:
    print(f"❌ joblib import failed: {e}")
    sys.exit(1)

import pickle

print()
print("=" * 60)
print("Testing Model Load")
print("=" * 60)

# Get models directory
MODELS_DIR = os.path.join(os.path.dirname(__file__), 'models')
KECAMATANS = ['BLIMBING', 'KEDUNGKANDANG', 'KLOJEN', 'LOWOKWARU', 'SUKUN']

print(f"Models directory: {MODELS_DIR}")
print(f"Directory exists: {os.path.exists(MODELS_DIR)}")
print()

if not os.path.exists(MODELS_DIR):
    print("❌ Models directory not found!")
    sys.exit(1)

# List files in models directory
print("Files in models directory:")
for f in os.listdir(MODELS_DIR):
    file_path = os.path.join(MODELS_DIR, f)
    size = os.path.getsize(file_path)
    print(f"  - {f} ({size:,} bytes, {size/(1024*1024):.2f} MB)")
print()

# Test loading each model
print("=" * 60)
print("Testing Each Model")
print("=" * 60)

for kecamatan in KECAMATANS:
    model_filename = f"prophet_model_{kecamatan}.pkl"
    model_path = os.path.join(MODELS_DIR, model_filename)
    
    print(f"\n{kecamatan}:")
    print(f"  File: {model_filename}")
    
    if not os.path.exists(model_path):
        print(f"  ❌ File not found!")
        continue
    
    file_size = os.path.getsize(model_path)
    print(f"  Size: {file_size:,} bytes ({file_size/(1024*1024):.2f} MB)")
    print(f"  Readable: {os.access(model_path, os.R_OK)}")
    
    # Try loading with joblib
    print(f"  Attempting joblib.load...")
    try:
        model = joblib.load(model_path)
        print(f"  ✅ SUCCESS with joblib.load")
        print(f"  Model type: {type(model)}")
        continue
    except KeyError as e:
        print(f"  ❌ KeyError with joblib: {e}")
    except Exception as e:
        print(f"  ❌ Error with joblib: {type(e).__name__} - {e}")
    
    # Try loading with pickle directly
    print(f"  Attempting pickle.load...")
    try:
        with open(model_path, 'rb') as f:
            model = pickle.load(f)
        print(f"  ✅ SUCCESS with pickle.load")
        print(f"  Model type: {type(model)}")
        continue
    except Exception as e:
        print(f"  ❌ Error with pickle: {type(e).__name__} - {e}")
    
    # Try with protocol 4
    print(f"  Attempting pickle.load with encoding...")
    try:
        with open(model_path, 'rb') as f:
            model = pickle.load(f, encoding='latin1')
        print(f"  ✅ SUCCESS with pickle.load (latin1)")
        print(f"  Model type: {type(model)}")
    except Exception as e:
        print(f"  ❌ Error with pickle (latin1): {type(e).__name__} - {e}")

print()
print("=" * 60)
print("Test Complete")
print("=" * 60)
