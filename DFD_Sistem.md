# 📊 Dokumentasi Data Flow Diagram (DFD) Lengkap
## ISP Billing & Monitoring System (Sesuai Desain Context Diagram Baru)

Dokumen ini menyajikan rancangan **Data Flow Diagram (DFD)** lengkap mulai dari **Level 0 (Diagram Konteks)**, **Level 1 (Dekomposisi Proses)**, hingga **Level 2** untuk sistem ISP Billing & Monitoring Anda. Struktur entitas, alur data, dan penamaan proses disesuaikan secara presisi dengan desain diagram konteks baru yang Anda berikan.

---

## 1. DFD Level 0 (Diagram Konteks / Context Diagram)
Diagram Konteks memposisikan sistem utama di tengah sebagai kotak persegi panjang, dengan 7 entitas luar berbentuk kapsul/stadium di sekelilingnya yang saling bertukar data secara langsung.

### 🖼️ Gambar Visual Diagram Konteks
![Diagram Konteks](diagram_konteks.png)

### 💻 Diagram Kode (Mermaid)
```mermaid
graph TD
    %% Styling Klasik (Hitam-Putih, Border Tipis)
    classDef system fill:#ffffff,stroke:#000000,stroke-width:2px,color:#000000;
    classDef entity fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000;

    %% Nodes
    SYS["Sistem ISP Billing & Network Monitoring"]:::system
    
    SUP_ADM(["Master / Super Admin"]):::entity
    PRTG(["PRTG Network Monitor"]):::entity
    
    ADM_CAB(["Admin Cabang"]):::entity
    FIN_KAS(["Finance / Kasir"]):::entity
    NOC(["NOC"]):::entity
    TEK_LAP(["Teknisi Lapangan"]):::entity
    PEL(["Pelanggan"]):::entity

    %% Hubungan Aliran Data
    %% 1. Master / Super Admin (Atas Kiri)
    SUP_ADM -->|"Pantau performa global"| SYS
    SYS -->|"Laporan keuangan & operasional"| SUP_ADM

    %% 2. PRTG Network Monitor (Atas Kanan)
    PRTG -->|"Data sensor JSON"| SYS

    %% 3. Admin Cabang (Bawah Kiri 1)
    ADM_CAB -->|"Data invoice, sensor PRTG, laporan tiket, konfirmasi pembayaran"| SYS
    SYS -->|"Riwayat cicilan & status tagihan"| ADM_CAB

    %% 4. Finance / Kasir (Bawah Kiri 2)
    FIN_KAS -->|"Input cicilan pembayaran"| SYS
    SYS -->|"Riwayat cicilan & status tagihan"| FIN_KAS

    %% 5. NOC (Bawah Tengah)
    NOC -->|"Buat tiket gangguan"| SYS
    SYS -->|"Status sensor jaringan real-time"| NOC

    %% 6. Teknisi Lapangan (Bawah Kanan 1)
    TEK_LAP -->|"Update status & upload foto"| SYS
    SYS -->|"Daftar tiket tugas lapangan"| TEK_LAP

    %% 7. Pelanggan (Bawah Kanan 2)
    PEL -->|"Buat tiket gangguan"| SYS
    SYS -->|"Status tagihan & progress tiket"| PEL

    %% Styling Link
    linkStyle default stroke:#000000,stroke-width:1.2px;
```

---

## 2. DFD Level 1 (Diagram Dekomposisi Proses)
DFD Level 1 memecah sistem menjadi **5 proses utama** yang saling berinteraksi dengan **6 data store** utama untuk mengelola siklus akun, invoice, cicilan pembayaran, ticketing, dan pemantauan link real-time.

### 🖼️ Gambar Visual DFD Level 1
![DFD Level 1](dfd_level1.png)

### 💻 Diagram Kode (Mermaid)
```mermaid
flowchart TD
    %% Styling Klasik DFD
    classDef process fill:#ffffff,stroke:#000000,stroke-width:2px,color:#000000;
    classDef entity fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000;
    classDef datastore fill:#ffffff,stroke:#000000,stroke-width:1.5px,stroke-dasharray: 5 5,color:#000000;

    %% Entities
    SUP_ADM(["Master / Super Admin"]):::entity
    PRTG(["PRTG Network Monitor"]):::entity
    ADM_CAB(["Admin Cabang"]):::entity
    FIN_KAS(["Finance / Kasir"]):::entity
    NOC(["NOC"]):::entity
    TEK_LAP(["Teknisi Lapangan"]):::entity
    PEL(["Pelanggan"]):::entity

    %% Processes (Lingkaran)
    P1("(1.0) <br/> Kelola Akun & Pelanggan"):::process
    P2("(2.0) <br/> Kelola Invoice & Tagihan"):::process
    P3("(3.0) <br/> Kelola Pembayaran Cicilan"):::process
    P4("(4.0) <br/> Kelola Gangguan / Tiket"):::process
    P5("(5.0) <br/> Kelola Monitoring Jaringan"):::process

    %% Data Stores (Tabung / Garis Dash)
    D1["═ D1: USER ═"]:::datastore
    D3["═ D3: CUSTOMERS ═"]:::datastore
    D4["═ D4: INVOICES ═"]:::datastore
    D5["═ D5: PAYMENTS ═"]:::datastore
    D6["═ D6: TICKETS ═"]:::datastore
    D8["═ D8: DEVICES ═"]:::datastore

    %% Aliran Proses 1.0 (Akun & Pelanggan)
    SUP_ADM -->|"Data Admin & Staf"| P1
    ADM_CAB -->|"Data Pelanggan Baru"| P1
    P1 -->|"Tulis Data User"| D1
    P1 -->|"Tulis Data Pelanggan"| D3
    D1 -->|"Baca Hak Akses"| P1
    D3 -->|"Baca Detail Profil"| P1

    %% Aliran Proses 2.0 (Invoice & Tagihan)
    ADM_CAB -->|"Buat Tagihan Bulanan"| P2
    P2 -->|"Tulis Tagihan (Unpaid)"| D4
    D3 -->|"Baca Pelanggan Aktif"| P2
    P2 -->|"Status Tagihan"| PEL

    %% Aliran Proses 3.0 (Pembayaran Cicilan)
    FIN_KAS -->|"Input Cicilan Pembayaran"| P3
    P3 -->|"Tulis Transaksi"| D5
    P3 -->|"Update Status Tagihan"| D4
    D5 -->|"Baca Transaksi"| P3
    P3 -->|"Riwayat Cicilan & Tagihan"| FIN_KAS
    P3 -->|"Riwayat Cicilan & Tagihan"| ADM_CAB
    P3 -->|"Status Tagihan"| PEL

    %% Aliran Proses 4.0 (Gangguan / Tiket)
    NOC -->|"Buat Tiket Gangguan"| P4
    PEL -->|"Buat Tiket Gangguan"| P4
    TEK_LAP -->|"Update Status & Upload Foto"| P4
    P4 -->|"Tulis / Update Tiket"| D6
    D6 -->|"Baca Detail Tiket"| P4
    P4 -->|"Daftar Tiket Tugas Lapangan"| TEK_LAP
    P4 -->|"Progress Tiket Gangguan"| PEL

    %% Aliran Proses 5.0 (Monitoring Jaringan)
    PRTG -->|"Data Sensor JSON"| P5
    P5 -->|"Tulis / Update Status Link"| D8
    D8 -->|"Baca IP Backbone"| P5
    P5 -->|"Status Sensor Real-Time"| NOC
    P5 -->|"Pantau Performa Global"| SUP_ADM

    %% Styling Link
    linkStyle default stroke:#000000,stroke-width:1.2px;
```

---

## 3. DFD Level 2 (Rincian Proses Detail)

### DFD Level 2 - Proses 3.0: Kelola Pembayaran Cicilan
```mermaid
flowchart TD
    classDef process fill:#ffffff,stroke:#000000,stroke-width:2px,color:#000000;
    classDef entity fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000;
    classDef datastore fill:#ffffff,stroke:#000000,stroke-width:1.5px,stroke-dasharray: 5 5,color:#000000;

    FIN_KAS(["Finance / Kasir"]):::entity
    PEL(["Pelanggan"]):::entity

    P3_1("(3.1) <br/> Validasi Sisa Tagihan"):::process
    P3_2("(3.2) <br/> Tulis Transaksi Cicilan"):::process
    P3_3("(3.3) <br/> Hitung Akumulasi Pembayaran"):::process
    P3_4("(3.4) <br/> Update Status Tagihan"):::process

    D4["═ D4: INVOICES ═"]:::datastore
    D5["═ D5: PAYMENTS ═"]:::datastore

    FIN_KAS -->|"Input Pembayaran Cicilan"| P3_1
    P3_1 -->|"Baca Data Invoice"| D4
    P3_1 -->|"Sisa Tagihan Tervalidasi"| P3_2

    P3_2 -->|"Simpan Pembayaran"| D5
    P3_2 -->|"Data Transaksi Baru"| P3_3

    P3_3 -->|"Baca Histori Pembayaran"| D5
    P3_3 -->|"Akumulasi Nominal Lunas"| P3_4

    P3_4 -->|"Update Invoice Status (Paid/Partial)"| D4
    P3_4 -->|"Status Tagihan"| PEL

    linkStyle default stroke:#000000,stroke-width:1.2px;
```

---

### DFD Level 2 - Proses 4.0: Kelola Gangguan / Tiket
```mermaid
flowchart TD
    classDef process fill:#ffffff,stroke:#000000,stroke-width:2px,color:#000000;
    classDef entity fill:#ffffff,stroke:#000000,stroke-width:1.5px,color:#000000;
    classDef datastore fill:#ffffff,stroke:#000000,stroke-width:1.5px,stroke-dasharray: 5 5,color:#000000;

    NOC(["NOC"]):::entity
    PEL(["Pelanggan"]):::entity
    TEK_LAP(["Teknisi Lapangan"]):::entity

    P4_1("(4.1) <br/> Registrasi Tiket Gangguan"):::process
    P4_2("(4.2) <br/> Disposisi Tugas Lapangan"):::process
    P4_3("(4.3) <br/> Upload Bukti Perbaikan"):::process
    P4_4("(4.4) <br/> Update Status Tiket"):::process

    D6["═ D6: TICKETS ═"]:::datastore

    NOC -->|"Buat Tiket Gangguan"| P4_1
    PEL -->|"Buat Tiket Gangguan"| P4_1
    P4_1 -->|"Tulis Tiket (Open)"| D6
    P4_1 -->|"Tiket Terdaftar"| P4_2

    P4_2 -->|"Tugaskan Teknisi"| D6
    P4_2 -->|"Daftar Tiket Tugas Lapangan"| TEK_LAP

    TEK_LAP -->|"Update Status & Upload Foto"| P4_3
    P4_3 -->|"Simpan Foto Bukti"| D6
    P4_3 -->|"Penyelesaian Tiket"| P4_4

    P4_4 -->|"Update Status Tiket (Resolved)"| D6
    P4_4 -->|"Progress & Status Tiket"| PEL

    linkStyle default stroke:#000000,stroke-width:1.2px;
```

---

## 📖 Kamus Penjelasan Setiap Aliran Data (Data Flow Dictionary)

### A. Aliran Data - DFD Level 0 (Diagram Konteks)

| No | Sumber (Source) | Tujuan (Destination) | Nama Aliran Data | Kandungan & Penjelasan Data |
|:---|:---|:---|:---|:---|
| **1** | Master / Super Admin | Sistem | Pantau performa global | Memantau seluruh aktivitas sistem, log monitoring, performa kasir, dan status teknisi lapangan. |
| **2** | Sistem | Master / Super Admin | Laporan keuangan & operasional | Rekapitulasi pendapatan bulanan global, laporan absensi teknisi, dan performa jaringan. |
| **3** | PRTG Network Monitor | Sistem | Data sensor JSON | Pengiriman payload data JSON yang berisi status link interface, bandwidth real-time, dan alert link down. |
| **4** | Admin Cabang | Sistem | Data invoice, sensor PRTG, laporan tiket, konfirmasi pembayaran | Menginputkan data tagihan cabang, request status PRTG lokal, laporan keluhan tiket pelanggan cabang, serta verifikasi bayar. |
| **5** | Sistem | Admin Cabang | Riwayat cicilan & status tagihan | Menampilkan riwayat cicilan pembayaran pelanggan cabang serta status lunas/sebagian. |
| **6** | Finance / Kasir | Sistem | Input cicilan pembayaran | Memasukkan nominal pembayaran cicilan tagihan dari pelanggan beserta tanggal transaksi. |
| **7** | Sistem | Finance / Kasir | Riwayat cicilan & status tagihan | Menampilkan histori data angsuran cicilan tagihan pelanggan untuk dicetak sebagai tanda terima. |
| **8** | NOC | Sistem | Buat tiket gangguan | Menginput data aduan gangguan jaringan backbone atau masalah teknis client. |
| **9** | Sistem | NOC | Status sensor jaringan real-time | Menampilkan grafik pemantauan sensor throughput dan status keaktifan link interface secara langsung. |
| **10** | Teknisi Lapangan | Sistem | Update status & upload foto | Mengubah status tiket gangguan menjadi *Resolved* dan mengunggah foto bukti fisik perbaikan di lapangan. |
| **11** | Sistem | Teknisi Lapangan | Daftar tiket tugas lapangan | Menampilkan daftar antrean penugasan perbaikan gangguan internet pelanggan. |
| **12** | Pelanggan | Sistem | Buat tiket gangguan | Melaporkan keluhan koneksi internet terputus atau lambat melalui aplikasi pelanggan. |
| **13** | Sistem | Pelanggan | Status tagihan & progress tiket | Menampilkan total tunggakan billing, riwayat pembayaran, serta pelacakan status penanganan keluhan teknisi. |

---

## 🧭 Penjelasan Alur Jalannya Data (Data Flow Trace)

### 📶 Alur 1: Penagihan & Pembayaran Cicilan (Finance & Admin Cabang)
1. **Admin Cabang** membuat tagihan melalui sistem (**Proses 2.0**). Data tagihan disimpan di **D4 (Invoices)** dengan status awal `unpaid`.
2. Pelanggan mendatangi kasir untuk mengangsur tagihan. **Finance / Kasir** menginput data nominal angsuran ke **Proses 3.0**.
3. **Proses 3.0** menyimpan record angsuran baru ke **D5 (Payments)** dan memperbarui sisa tagihan di **D4 (Invoices)**.
4. **Finance / Kasir**, **Admin Cabang**, dan **Pelanggan** menerima pembaruan secara real-time mengenai **Riwayat cicilan & status tagihan**.

### 🛠️ Alur 2: Manajemen Gangguan & Dispatch Tiket (NOC, Pelanggan, & Teknisi)
1. **Pelanggan** atau **NOC** melaporkan kendala internet terputus ke sistem (**Proses 4.0**). Laporan tersebut disimpan di **D6 (Tickets)** dengan status `open`.
2. **Proses 4.0** menugaskan teknisi dan mengirimkan **Daftar tiket tugas lapangan** ke panel **Teknisi Lapangan**.
3. **Teknisi Lapangan** memperbaiki masalah di rumah pelanggan, lalu mengunggah status selesai beserta foto bukti perbaikan ke **Proses 4.0**.
4. **Proses 4.0** memperbarui status tiket di **D6 (Tickets)** menjadi `resolved` dan memperbarui halaman pelacakan **Pelanggan** mengenai **Progress tiket gangguan**.

### 📊 Alur 3: Monitoring & Sensor Real-Time (PRTG, NOC, & Master Admin)
1. **PRTG Network Monitor** secara konstan mengirimkan **Data sensor JSON** berisi metrik interface link ke sistem (**Proses 5.0**).
2. **Proses 5.0** memproses payload JSON tersebut dan memperbarui database log status link pada **D8 (Devices)**.
3. Petugas **NOC** memantau visualisasi grafik real-time tersebut pada panel monitoring.
4. **Master / Super Admin** mengakses sistem untuk melihat visualisasi performa global untuk kebutuhan analisis laporan operasional.
