"""
Script untuk memisahkan file CSV berdasarkan tahun
Memisahkan data kiriman berdasarkan tahun dari kolom tgl_kirim
Semua kolom akan dipertahankan
"""

import pandas as pd
import os
from datetime import datetime

def split_csv_by_year(input_file, output_dir='../data/yearly'):
    """
    Memisahkan file CSV/Excel berdasarkan tahun
    
    Args:
        input_file: Path ke file CSV/Excel input
        output_dir: Directory untuk menyimpan file hasil split
    """
    
    print(f"📂 Membaca file: {input_file}")
    
    # Buat directory output jika belum ada
    os.makedirs(output_dir, exist_ok=True)
    
    try:
        # Cek ekstensi file
        file_ext = os.path.splitext(input_file)[1].lower()
        
        df = None
        
        if file_ext in ['.xlsx', '.xls']:
            # File Excel
            print("📊 Membaca file Excel...")
            df = pd.read_excel(input_file, engine='openpyxl' if file_ext == '.xlsx' else 'xlrd')
            print(f"   ✅ Berhasil membaca Excel file")
        else:
            # File CSV - coba berbagai encoding dan separator
            print("🔍 Mendeteksi encoding dan separator CSV...")
            encodings = ['utf-8', 'latin1', 'iso-8859-1', 'cp1252', 'utf-8-sig']
            separators = [',', ';', '\t', '|']
            
            for enc in encodings:
                for sep in separators:
                    try:
                        print(f"   Mencoba encoding: {enc}, separator: '{sep}'")
                        df = pd.read_csv(input_file, encoding=enc, sep=sep, low_memory=False)
                        if len(df.columns) > 1:  # Pastikan berhasil parsing kolom
                            print(f"   ✅ Berhasil dengan encoding: {enc}, separator: '{sep}'")
                            break
                    except Exception as e:
                        continue
                if df is not None and len(df.columns) > 1:
                    break
        
        if df is None or len(df.columns) <= 1:
            print("❌ Tidak bisa membaca file dengan encoding/separator apapun!")
            print("💡 Pastikan file adalah CSV atau Excel yang valid")
            return
        
        print(f"✅ Total data terbaca: {len(df):,} rows")
        print(f"📊 Kolom yang ada: {list(df.columns)}")
        
        # Cari kolom tanggal (biasanya tgl_kirim, tgl kirim, atau tanggal kirim)
        date_columns = [col for col in df.columns if 'kirim' in col.lower() and 'tgl' in col.lower()]
        
        if not date_columns:
            print("❌ Kolom tanggal kirim tidak ditemukan!")
            print(f"   Kolom yang tersedia: {list(df.columns)}")
            return
        
        date_col = date_columns[0]
        print(f"📅 Menggunakan kolom: '{date_col}' untuk split berdasarkan tahun")
        
        # Convert kolom tanggal ke datetime
        print("⏳ Konversi format tanggal...")
        
        # Cek tipe data kolom tanggal
        print(f"   Tipe data kolom '{date_col}': {df[date_col].dtype}")
        print(f"   Contoh nilai (5 baris pertama): {list(df[date_col].head())}")
        
        # Strategi parsing yang lebih robust
        df['year'] = None
        
        try:
            # Method 1: Gunakan pd.to_datetime untuk konversi otomatis
            df['parsed_date'] = pd.to_datetime(df[date_col], errors='coerce')
            df['year'] = df['parsed_date'].dt.year
            
            # Hitung berapa yang berhasil
            parsed_count = df['year'].notna().sum()
            print(f"✅ Berhasil parse {parsed_count:,} tanggal dari {len(df):,} rows")
            
            # Drop kolom temporary
            df = df.drop(columns=['parsed_date'])
            
        except Exception as e:
            print(f"⚠️  Konversi otomatis gagal: {e}")
            print("   Mencoba parsing manual...")
            
            parsed_count = 0
            for idx, date_val in df[date_col].items():
                if pd.isna(date_val):
                    continue
                    
                try:
                    # Jika sudah datetime object
                    if isinstance(date_val, pd.Timestamp) or hasattr(date_val, 'year'):
                        df.at[idx, 'year'] = date_val.year
                        parsed_count += 1
                    # Coba format Excel serial date (angka)
                    elif isinstance(date_val, (int, float)):
                        from datetime import timedelta
                        excel_date = datetime(1899, 12, 30) + timedelta(days=int(date_val))
                        df.at[idx, 'year'] = excel_date.year
                        parsed_count += 1
                    # Coba parse sebagai string
                    elif isinstance(date_val, str):
                        for fmt in ['%d/%m/%Y', '%Y-%m-%d', '%d-%m-%Y', '%m/%d/%Y', '%Y/%m/%d']:
                            try:
                                parsed_date = datetime.strptime(str(date_val).strip(), fmt)
                                df.at[idx, 'year'] = parsed_date.year
                                parsed_count += 1
                                break
                            except:
                                continue
                except Exception as e:
                    continue
            
            print(f"✅ Berhasil parse {parsed_count:,} tanggal dari {len(df):,} rows")
        
        # Hapus baris yang tidak ada tahunnya
        df_valid = df[df['year'].notna()].copy()
        df_invalid = df[df['year'].isna()].copy()
        
        if len(df_invalid) > 0:
            print(f"⚠️  {len(df_invalid):,} rows tidak memiliki tanggal valid (akan diskip)")
        
        # Hapus kolom 'year' temporary (jangan simpan ke file)
        years = sorted(df_valid['year'].unique())
        print(f"\n📆 Tahun yang ditemukan: {[int(y) for y in years]}")
        
        # Split berdasarkan tahun
        print("\n🔄 Memisahkan data berdasarkan tahun...")
        
        base_filename = os.path.splitext(os.path.basename(input_file))[0]
        
        for year in years:
            year_int = int(year)
            year_data = df_valid[df_valid['year'] == year].copy()
            
            # Hapus kolom 'year' temporary
            year_data = year_data.drop(columns=['year'])
            
            # Nama file output
            output_file = os.path.join(output_dir, f"{base_filename}_{year_int}.csv")
            
            # Simpan ke CSV
            year_data.to_csv(output_file, index=False, encoding='utf-8-sig')
            
            file_size = os.path.getsize(output_file) / (1024 * 1024)  # MB
            print(f"   ✅ {year_int}: {len(year_data):,} rows → {output_file} ({file_size:.2f} MB)")
        
        print("\n" + "="*70)
        print("🎉 SELESAI! File berhasil dipisahkan berdasarkan tahun")
        print(f"📁 Lokasi file: {os.path.abspath(output_dir)}")
        print("="*70)
        
        # Summary
        print("\n📊 RINGKASAN:")
        print(f"   Total data awal: {len(df):,} rows")
        print(f"   Data valid: {len(df_valid):,} rows")
        print(f"   Data invalid: {len(df_invalid):,} rows")
        print(f"   Jumlah tahun: {len(years)} tahun")
        print(f"   Jumlah kolom: {len(df.columns) - 1} kolom (semua dipertahankan)")
        
    except Exception as e:
        print(f"\n❌ ERROR: {str(e)}")
        import traceback
        traceback.print_exc()


def main():
    """
    Main function
    """
    print("="*70)
    print("🔧 SPLIT CSV/EXCEL BY YEAR - Data Pengiriman Paket")
    print("="*70)
    print()
    
    # Cari file yang tersedia
    data_dir = '../data'
    possible_files = [
        '../data/data_kiriman.xlsx',
        '../data/data_kiriman_converted.csv',
        '../data/data_paket.csv'
    ]
    
    print("🔍 Mencari file data...")
    input_file = None
    
    for file_path in possible_files:
        if os.path.exists(file_path):
            input_file = file_path
            print(f"✅ File ditemukan: {file_path}")
            break
    
    if input_file is None:
        print(f"❌ File tidak ditemukan!")
        print(f"📂 Current directory: {os.getcwd()}")
        print(f"📂 Files di {data_dir}:")
        if os.path.exists(data_dir):
            for f in os.listdir(data_dir):
                if f.endswith(('.csv', '.xlsx', '.xls')):
                    print(f"   - {f}")
        return
    
    # Jalankan split
    split_csv_by_year(input_file)


if __name__ == "__main__":
    main()
