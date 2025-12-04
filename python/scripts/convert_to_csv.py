import pandas as pd
import sys

print("Converting Excel to CSV...")
print("Loading Excel file...")

try:
    # Read Excel file
    df = pd.read_excel('data/data_kiriman.xlsx')
    
    print(f"Found {len(df)} rows")
    print(f"Columns: {df.columns.tolist()}")
    
    # Save to CSV
    output_file = 'data/data_kiriman_converted.csv'
    df.to_csv(output_file, index=False, encoding='utf-8')
    
    print(f"\n✅ Successfully converted to CSV: {output_file}")
    print(f"📊 Total rows: {len(df)}")
    
except Exception as e:
    print(f"❌ Error: {str(e)}")
    sys.exit(1)
