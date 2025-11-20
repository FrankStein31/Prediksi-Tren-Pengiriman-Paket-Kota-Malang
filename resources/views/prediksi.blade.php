<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Pengiriman Paket</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        #result { background: #f8f9fa; padding: 20px; border-radius: 4px; margin-top: 20px; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prediksi Pengiriman Paket</h1>
        
        <div class="card">
            <h3>1. Training Model (Jalankan Sekali)</h3>
            <p>Klik tombol ini untuk melatih model Prophet berdasarkan data historis.</p>
            <button class="btn" onclick="trainModel()">Train Model</button>
        </div>

        <div class="card">
            <h3>2. Lihat Kecamatan yang Tersedia</h3>
            <button class="btn" onclick="getKecamatan()">Get Available Kecamatan</button>
        </div>

        <div class="card">
            <h3>3. Prediksi Paket</h3>
            <form onsubmit="predict(event)">
                <div>
                    <label>Kecamatan:</label>
                    <input type="text" id="kecamatan" placeholder="Contoh: Bandung Wetan" required>
                </div>
                <br>
                <div>
                    <label>Jumlah Minggu Prediksi:</label>
                    <input type="number" id="weeks" value="12" min="1" max="52">
                </div>
                <br>
                <button type="submit" class="btn">Predict</button>
            </form>
        </div>

        <div id="result"></div>
    </div>

    <script>
        async function trainModel() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>Training model... Mohon tunggu (bisa memakan waktu beberapa menit)</p>';
            
            try {
                const response = await fetch('/prediksi/train', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }

        async function getKecamatan() {
            const resultDiv = document.getElementById('result');
            
            try {
                const response = await fetch('/prediksi/kecamatan');
                const data = await response.json();
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }

        async function predict(event) {
            event.preventDefault();
            
            const kecamatan = document.getElementById('kecamatan').value;
            const weeks = document.getElementById('weeks').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = '<p>Melakukan prediksi... Mohon tunggu</p>';
            
            try {
                const response = await fetch('/prediksi/predict', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        kecamatan: kecamatan,
                        weeks: parseInt(weeks)
                    })
                });
                
                const data = await response.json();
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }
    </script>
</body>
</html>
