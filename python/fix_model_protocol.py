"""
Re-save Prophet Models with Lower Protocol Version
For compatibility with older Python versions on cPanel (3.7/3.8)

Usage:
    python fix_model_protocol.py
"""

import joblib
import os
from pathlib import Path

# Configuration
MODELS_DIR = Path(__file__).parent / 'models'
KECAMATANS = ['BLIMBING', 'KEDUNGKANDANG', 'KLOJEN', 'LOWOKWARU', 'SUKUN']

def fix_model_protocol(kecamatan):
    """Load and re-save model with lower protocol version"""
    model_filename = f"prophet_model_{kecamatan}.pkl"
    model_path = MODELS_DIR / model_filename
    
    if not model_path.exists():
        print(f"❌ Model not found: {model_path}")
        return False
    
    try:
        print(f"Loading {model_filename}...")
        model = joblib.load(model_path)
        
        # Create backup
        backup_path = MODELS_DIR / f"{model_filename}.backup"
        if not backup_path.exists():
            os.rename(model_path, backup_path)
            print(f"✅ Backup created: {backup_path}")
        
        # Save with protocol 4 (compatible with Python 3.4+)
        print(f"Saving {model_filename} with protocol=4...")
        joblib.dump(model, model_path, protocol=4)
        
        # Verify
        test_model = joblib.load(model_path)
        print(f"✅ Verified: {model_filename}")
        
        # Get file sizes
        original_size = backup_path.stat().st_size / (1024 * 1024)
        new_size = model_path.stat().st_size / (1024 * 1024)
        
        print(f"   Original: {original_size:.2f} MB")
        print(f"   New:      {new_size:.2f} MB")
        print()
        
        return True
        
    except Exception as e:
        print(f"❌ Error processing {model_filename}: {str(e)}")
        
        # Restore from backup if exists
        if backup_path.exists():
            os.rename(backup_path, model_path)
            print(f"⚠️  Restored from backup")
        
        return False


def main():
    print("=" * 60)
    print("Prophet Model Protocol Fixer")
    print("=" * 60)
    print()
    
    if not MODELS_DIR.exists():
        print(f"❌ Models directory not found: {MODELS_DIR}")
        return
    
    print(f"Models directory: {MODELS_DIR}")
    print(f"Processing {len(KECAMATANS)} models...")
    print()
    
    success_count = 0
    
    for kecamatan in KECAMATANS:
        if fix_model_protocol(kecamatan):
            success_count += 1
    
    print("=" * 60)
    print(f"Results: {success_count}/{len(KECAMATANS)} models processed successfully")
    
    if success_count == len(KECAMATANS):
        print("✅ All models fixed! Ready for cPanel deployment.")
        print()
        print("Next steps:")
        print("1. Upload models/ folder to cPanel")
        print("2. Test API: curl https://your-api.com/health")
        print("3. Check 'exists': true for all models")
    else:
        print("⚠️  Some models failed. Check errors above.")
        print()
        print("To restore backups:")
        print("  cd models/")
        print("  mv *.backup original_names")
    
    print("=" * 60)


if __name__ == '__main__':
    main()
