import openpyxl
import os

def main():
    workspace_dir = os.path.abspath(".")
    backup_path = os.path.join(workspace_dir, "Analisis biaya dan manfaat (Contoh).xlsx")
    target_path = os.path.join(workspace_dir, "Analisis biaya dan manfaat.xlsx")
    markdown_path = os.path.join(workspace_dir, "Analisis Biaya dan Manfaat Proyek.md")

    if not os.path.exists(backup_path):
        print(f"Backup file not found at: {backup_path}")
        return

    # Load workbook keeping styles and formulas
    wb = openpyxl.load_workbook(backup_path, data_only=False)
    sheet = wb["Analisis Biaya dan manfaat"]

    # 1. Update BIAYA-BIAYA (Tahun 0)
    sheet["B4"] = 40000000.0  # Biaya pengadaan
    sheet["B5"] = 4000000.0   # Biaya persiapan operasi
    sheet["B7"] = 10000000.0  # Biaya konsultan
    sheet["B8"] = 8000000.0   # Tahap analisis sistem
    sheet["B9"] = 8000000.0   # Tahap desain sistem
    sheet["B10"] = 15000000.0 # Penerapan sistem
    sheet["E11"] = 200000.0   # Biaya penyusutan Year 3

    # 2. Update BIAYA OPERASIONAL (Tahun 1, 2, 3)
    sheet["C17"] = 3000000.0  # Operasional Year 1
    sheet["D17"] = 3500000.0  # Operasional Year 2
    sheet["E17"] = 4000000.0  # Operasional Year 3
    
    sheet["C18"] = 2000000.0  # Perawatan Year 1
    sheet["D18"] = 2500000.0  # Perawatan Year 2
    sheet["E18"] = 2500000.0  # Perawatan Year 3

    # 3. Update MANFAAT BERWUJUD (Tahun 1, 2, 3)
    sheet["C26"] = 10000000.0 # Penghematan biaya operasional pershn Year 1
    sheet["D26"] = 12000000.0 # Penghematan biaya operasional pershn Year 2
    sheet["E26"] = 15000000.0 # Penghematan biaya operasional pershn Year 3

    sheet["C27"] = 12000000.0 # Peningkatan penjualan Year 1
    sheet["D27"] = 20000000.0 # Peningkatan penjualan Year 2
    sheet["E27"] = 30000000.0 # Peningkatan penjualan Year 3

    sheet["C28"] = 4000000.0  # Penurunan biaya persediaan Year 1
    sheet["D28"] = 5000000.0  # Penurunan biaya persediaan Year 2
    sheet["E28"] = 6000000.0  # Penurunan biaya persediaan Year 3

    # 4. Update MANFAAT TAK BERWUJUD (Tahun 1, 2, 3)
    sheet["C30"] = 5000000.0  # Peningkatan pelayanan Year 1
    sheet["D30"] = 7000000.0  # Peningkatan pelayanan Year 2
    sheet["E30"] = 10000000.0 # Peningkatan pelayanan Year 3

    sheet["C31"] = 3000000.0  # Peningkatan kepuasan pekerjaan Year 1
    sheet["D31"] = 4000000.0  # Peningkatan kepuasan pekerjaan Year 2
    sheet["E31"] = 5000000.0  # Peningkatan kepuasan pekerjaan Year 3

    sheet["C32"] = 4000000.0  # Peningkatan pengambilan keputusan Year 1
    sheet["D32"] = 5000000.0  # Peningkatan pengambilan keputusan Year 2
    sheet["E32"] = 6000000.0  # Peningkatan pengambilan keputusan Year 3

    # 5. Update summary tables (Rows 40-42) values / formulas
    sheet["B40"] = 85000000.0
    sheet["C40"] = 5000000.0
    sheet["D40"] = 6000000.0
    sheet["E40"] = 6700000.0

    sheet["B41"] = 0.0
    sheet["C41"] = 38000000.0
    sheet["D41"] = 53000000.0
    sheet["E41"] = 72000000.0

    sheet["C42"] = 33000000.0
    sheet["D42"] = 47000000.0
    sheet["E42"] = 65300000.0

    # 6. Update ROI Table (Rows 62-64) values / formulas
    sheet["B62"] = 85000000.0
    sheet["C62"] = 5000000.0
    sheet["D62"] = 6000000.0
    sheet["E62"] = 6700000.0

    sheet["B63"] = 0.0
    sheet["C63"] = 38000000.0
    sheet["D63"] = 53000000.0
    sheet["E63"] = 72000000.0

    sheet["C64"] = 33000000.0
    sheet["D64"] = 47000000.0
    sheet["E64"] = 65300000.0

    # 7. Update ROI % texts
    sheet["C69"] = "= 58.71%"

    # 8. Update Payback Period details
    # Row 55 Col 3 (C) had hardcoded division in original, let's write '=C52/E42' to make it dynamic or use value
    sheet["C55"] = "=C52/E42"
    # Row 57 Col 1 (A) summary text update:
    sheet["A57"] = "Sisa investasi tahun ke 3 tertutup oleh proceed tahun ke 3 yang berarti investasi akan kembali pada tahun ke 3 yaitu tepatnya 2.08 tahun atau 2 tahun 28 hari"

    # 9. Update NPV summary text
    # Original Row 78: "Rp 23,981,618 atau NPV lebih besar dari 0, maka proyek tersebut layak dilaksanakan"
    # New NPV is 32,903,832
    sheet["A78"] = "Rp 32,903,832 atau NPV lebih besar dari 0, maka proyek tersebut layak dilaksanakan"

    # Save
    wb.save(target_path)
    print(f"Successfully updated Excel values and formulas. Saved to {target_path}")

    # 10. Update the Markdown report
    markdown_content = """# Analisis Biaya dan Manfaat Proyek ISP Billing & Management System

Dokumen ini menyajikan analisis kelayakan investasi finansial untuk implementasi **ISP Billing & Management System** (tagihan terotomatisasi MikroTik, monitoring backbone PRTG & Telegram Bot, serta presensi kehadiran staf verifikasi wajah).

Analisis ini didasarkan pada proyek riil Anda dengan asumsi estimasi implementasi selama **3 tahun** dan tingkat bunga diskonto sebesar **10%**.

---

## 📊 Tabel Analisis Biaya & Manfaat (Persis Format Template)

| Komponen Analisis | Tahun 0 | Tahun 1 | Tahun 2 | Tahun 3 |
| :--- | :---: | :---: | :---: | :---: |
| **BIAYA-BIAYA** | | | | |
| **Biaya Pengembangan Sistem** | | | | |
| - Biaya pengadaan | Rp 40.000.000 | Rp 0 | Rp 0 | Rp 0 |
| - Biaya persiapan operasi | Rp 4.000.000 | Rp 0 | Rp 0 | Rp 0 |
| **Biaya Proyek** | | | | |
| - Biaya konsultan | Rp 10.000.000 | Rp 0 | Rp 0 | Rp 0 |
| - Tahap analisis sistem | Rp 8.000.000 | Rp 0 | Rp 0 | Rp 0 |
| - Tahap desain sistem | Rp 8.000.000 | Rp 0 | Rp 0 | Rp 0 |
| - Penerapan sistem | Rp 15.000.000 | Rp 0 | Rp 0 | Rp 0 |
| - Biaya penyusutan | Rp 0 | Rp 0 | Rp 0 | Rp 200.000 |
| **Total biaya proyek** | **Rp 41.000.000** | **Rp 0** | **Rp 0** | **Rp 200.000** |
| **Total Biaya Pengembangan Sistem** | **Rp 85.000.000** | **Rp 0** | **Rp 0** | **Rp 200.000** |
| | | | | |
| **Biaya Operasional dan Perawatan** | | | | |
| - Operasional | Rp 0 | Rp 3.000.000 | Rp 3.500.000 | Rp 4.000.000 |
| - Perawatan | Rp 0 | Rp 2.000.000 | Rp 2.500.000 | Rp 2.500.000 |
| **Total Biaya Operasional dan Perawatan**| **Rp 0** | **Rp 5.000.000** | **Rp 6.000.000** | **Rp 6.500.000** |
| **TOTAL BIAYA** | **Rp 85.000.000** | **Rp 5.000.000** | **Rp 6.000.000** | **Rp 6.700.000** |
| | | | | |
| **MANFAAT** | | | | |
| **Berwujud** | | | | |
| - Penghematan biaya operasional pershn | Rp 0 | Rp 10.000.000 | Rp 12.000.000 | Rp 15.000.000 |
| - Peningkatan penjualan | Rp 0 | Rp 12.000.000 | Rp 20.000.000 | Rp 30.000.000 |
| - Penurunan biaya persediaan | Rp 0 | Rp 4.000.000 | Rp 5.000.000 | Rp 6.000.000 |
| **Tak Berwujud** | | | | |
| - Peningkatan pelayanan | Rp 0 | Rp 5.000.000 | Rp 7.000.000 | Rp 10.000.000 |
| - Peningkatan kepuasan pekerjaan | Rp 0 | Rp 3.000.000 | Rp 4.000.000 | Rp 5.000.000 |
| - Peningkatan pengambilan keputusan | Rp 0 | Rp 4.000.000 | Rp 5.000.000 | Rp 6.000.000 |
| **TOTAL MANFAAT** | **Rp 0** | **Rp 38.000.000** | **Rp 53.000.000** | **Rp 72.000.000** |
| **PROCEED (TM - TB)** | **-Rp 85.000.000**| **Rp 33.000.000** | **Rp 47.000.000** | **Rp 65.300.000** |

---

## 📈 Metrik Evaluasi Kelayakan Proyek

### 1. Payback Period (PP)
* **Nilai Investasi:** Rp 85.000.000
* **Proceed Tahun I:** Rp 33.000.000 $\rightarrow$ Sisa investasi: Rp 52.000.000
* **Proceed Tahun II:** Rp 47.000.000 $\rightarrow$ Sisa investasi: Rp 5.000.000
* **Sisa Fraction (Tahun III):** $\frac{\text{Rp 5.000.000}}{\text{Rp 65.300.000}} \approx 0.08$ (atau **28 hari**).
* *Kesimpulan:* Investasi akan kembali pada tahun ke-3, yaitu tepatnya **2.08 tahun** (atau **2 tahun 28 hari**).

### 2. Return on Investment (ROI)
$$\text{ROI} = \frac{\text{Total Manfaat} - \text{Total Biaya}}{\text{Total Biaya}} = \frac{\text{Rp 163.000.000} - \text{Rp 102.700.000}}{\text{Rp 102.700.000}} \approx \mathbf{58,71\%}$$
* *Kesimpulan:* Proyek menghasilkan keuntungan bersih operasional sebesar **58,71%** dari total modal yang diinvestasikan.

### 3. Net Present Value (NPV) dengan Bunga 10%
* **PV Proceed 1:** $\frac{\text{Rp 33.000.000}}{(1 + 0,10)^1} = \text{Rp 30.000.000}$
* **PV Proceed 2:** $\frac{\text{Rp 47.000.000}}{(1 + 0,10)^2} = \text{Rp 38.842.975}$
* **PV Proceed 3:** $\frac{\text{Rp 65.300.000}}{(1 + 0,10)^3} = \text{Rp 49.060.857}$
* **Total Present Value (PV):** Rp 117.903.832
* **NPV:**
  $$\text{NPV} = \text{Total PV Proceeds} - \text{Investasi Awal}$$
  $$\text{NPV} = \text{Rp 117.903.832} - \text{Rp 85.000.000} = \mathbf{Rp 32.903.832}$$
* *Kesimpulan:* Karena **NPV > 0** (positif sebesar Rp 32.903.832), proyek **ISP Billing & Management System** ini dinyatakan **sangat layak dilaksanakan**.

---

## 💡 Rincian Justifikasi Manfaat Proyek Anda

1. **Otomatisasi Penagihan & Isolir MikroTik (Tabel `customers`, `invoices`, `payments`)**:
   Dengan adanya integrasi API MikroTik, sistem secara otomatis mengisolasi (memblokir) IP pelanggan yang memiliki tagihan `unpaid` melewati `due_date`. Hal ini meniadakan kebutuhan peninjauan manual dan meminimalkan kerugian akibat tagihan macet yang biasanya dibiarkan aktif.
2. **Dashboard Manajemen Multi-Tenant (Tabel `users` & `packages`)**:
   Setiap owner/admin memiliki kontrol penuh atas daftar paket dan limitasi pelanggan. Data terpusat mempercepat monitoring pendapatan bulanan.
3. **Pemantauan Infrastruktur Jaringan (Tabel `backbone_devices` & PRTG)**:
   Integrasi PRTG dan bot notifikasi Telegram memungkinkan notifikasi downtime perangkat backbone dikirim secara real-time. Downtime dapat diatasi sebelum banyak pelanggan komplain.
4. **Sistem Tiket Keluhan Pelanggan Terlacak (Tabel `tickets`)**:
   Keluhan pelanggan tercatat dengan foto kendala, teknisi yang ditugaskan (`assigned_to`), status (`open`/`resolved`/`closed`), serta bukti foto perbaikan. Ini meningkatkan transparansi dan kualitas pelayanan.
   
*Catatan: Dokumen interaktif Excel dengan formula otomatis tersedia di file proyek Anda: [Analisis biaya dan manfaat.xlsx](file:///d:/xampp/htdocs/isp-billing/Analisis%20biaya%20dan%20manfaat.xlsx).*
"""

    with open(markdown_path, "w", encoding="utf-8") as f:
        f.write(markdown_content)
    print(f"Successfully updated Markdown file at: {markdown_path}")

if __name__ == "__main__":
    main()
