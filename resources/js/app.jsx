import React, { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import BackboneAlerts from "./BackboneAlerts";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { Bar, Doughnut } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

// Clean raw HTML tags from PRTG API responses
const cleanHtml = (str) => {
    if (!str) return "";
    return String(str).replace(/<[^>]*>/g, "").trim();
};

function App({ role }) {
    const [data, setData] = useState(null);
    const [modal, setModal] = useState(null);
    const [prtg, setPrtg] = useState([]);
    const [searchQuery, setSearchQuery] = useState("");
    const [monthlyModal, setMonthlyModal] = useState(null);
    const [loadingMonthly, setLoadingMonthly] = useState(false);
    const canViewFinance = ["admin", "finance"].includes(role);
    const canViewDevices = ["admin", "noc", "teknisi"].includes(role);


    const [mikrotikData, setMikrotikData] = useState(null);
    const [loadingMikrotik, setLoadingMikrotik] = useState(false);

    const [month, setMonth] = useState(
        new Date().toISOString().slice(0, 7)
    );

    useEffect(() => {
        const fetchData = () => {
            fetch(`/api/dashboard-data?month=${month}`)
                .then((res) => {
                    if (!res.ok) throw new Error("Gagal mengambil data dashboard");
                    return res.json();
                })
                .then((res) => setData(res))
                .catch((err) => console.error("Error fetchData:", err));
        };

        const fetchPrtg = () => {
            console.log("Starting PRTG fetch...");
            fetch(`/api/prtg`)
                .then((res) => {
                    if (!res.ok) throw new Error("Gagal mengambil data PRTG");
                    return res.json();
                })
                .then((res) => {
                    const list = res.sensors || [];
                    console.log("PRTG Fetch Success! Raw sensors count:", list.length);
                    setPrtg(list);
                })
                .catch((err) => {
                    console.error("Error fetchPrtg:", err);
                });
        };

        const fetchMikrotik = () => {
            setLoadingMikrotik(true);
            fetch(`/api/mikrotik/dashboard-data`)
                .then((res) => {
                    if (!res.ok) throw new Error("Gagal mengambil data MikroTik");
                    return res.json();
                })
                .then((res) => setMikrotikData(res))
                .catch((err) => console.error("Error fetchMikrotik:", err))
                .finally(() => setLoadingMikrotik(false));
        };

        fetchData();
        if (canViewDevices) {
            fetchPrtg();
            fetchMikrotik();
        }

        const interval = setInterval(() => {
            fetchData();
            if (canViewDevices) {
                fetchPrtg();
                fetchMikrotik();
            }
        }, 10000);

        return () => clearInterval(interval);
    }, [month, canViewDevices]);

    const fetchMonthlyTickets = (monthCode, monthLabel) => {
        setLoadingMonthly(true);
        fetch(`/api/monthly-tickets?month=${monthCode}`)
            .then((res) => {
                if (!res.ok) throw new Error("Gagal mengambil data tiket bulanan");
                return res.json();
            })
            .then((res) => {
                setMonthlyModal({
                    title: `Riwayat Tiket - ${monthLabel}`,
                    monthCode: monthCode,
                    monthLabel: monthLabel,
                    data: res
                });
            })
            .catch((err) => {
                console.error("Error fetchMonthlyTickets:", err);
                alert("Gagal memuat data tiket untuk bulan tersebut.");
            })
            .finally(() => {
                setLoadingMonthly(false);
            });
    };

    const exportToCSV = (ticketsList, monthLabel) => {
        if (!ticketsList || ticketsList.length === 0) {
            alert("Tidak ada tiket untuk diekspor pada bulan ini.");
            return;
        }

        // CSV Headers
        const headers = ["No", "Judul Tiket", "Pelanggan", "Teknisi", "Tanggal Dibuat", "Status"];

        // Map data rows
        const rows = ticketsList.map((t, index) => [
            index + 1,
            t.title || "-",
            t.customer?.name || "-",
            t.teknisi?.name || "-",
            t.tanggal || "-",
            t.status === "open" ? "Open" : "Done"
        ]);

        // Construct CSV content string
        const csvContent = [
            headers.join(","),
            ...rows.map(row => row.map(val => `"${String(val).replace(/"/g, '""')}"`).join(","))
        ].join("\n");

        // Add UTF-8 BOM so Excel opens it with correct formatting
        const blob = new Blob(["\ufeff" + csvContent], { type: "text/csv;charset=utf-8;" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", `Riwayat_Tiket_${monthLabel.replace(/\s+/g, "_")}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    if (!data) return <p style={{ padding: "20px" }}>⏳ Loading...</p>;

    // FILTER HANYA PELANGGAN (ID DIAWALI ANGKA) & FILTER BERDASARKAN SEARCH QUERY
    const isCustomer = (name) => {
        if (!name) return false;
        return /^\d+/.test(String(name));
    };

    // PRTG STATUS IDENTIFIER
    const getDeviceStatus = (d) => {
        if (!d) return "unknown";
        const raw = Number(d.status_raw);
        const statusStr = d.status ? String(d.status).toLowerCase() : "";
        
        if (raw === 3 || statusStr.includes("up")) return "online";
        if (raw === 4 || raw === 10 || statusStr.includes("warning") || statusStr.includes("unusual")) return "warning";
        if ([5, 13, 14].includes(raw) || statusStr.includes("down")) return "down";
        if ([7, 8, 9, 11, 12].includes(raw) || statusStr.includes("paused")) return "paused";
        return "unknown";
    };

    const getDurationSince = (oleDate) => {
        if (!oleDate) return "tidak diketahui";
        const epochOffset = 25569; // Selisih hari antara 1899-12-30 dan 1970-01-01
        const msInDay = 86400000;
        const statusChangeTime = new Date((Number(oleDate) - epochOffset) * msInDay);
        const now = new Date();
        const diffMs = now - statusChangeTime;
        
        if (diffMs < 0) return "0 menit";
        
        const diffMins = Math.floor(diffMs / 60000);
        if (diffMins < 60) {
            return `${diffMins} menit`;
        }
        
        const diffHours = Math.floor(diffMins / 60);
        const mins = diffMins % 60;
        if (diffHours < 24) {
            return `${diffHours} jam ${mins} menit`;
        }
        
        const diffDays = Math.floor(diffHours / 24);
        const hours = diffHours % 24;
        return `${diffDays} hari ${hours} jam`;
    };

    const getDeviceDuration = (d) => {
        if (!d) return "tidak diketahui";
        const status = getDeviceStatus(d);
        
        if (status === "down") {
            // Downtime: sejak terakhir kali UP
            return getDurationSince(d.lastup_raw);
        } else if (status === "online") {
            // Uptime: sejak terakhir kali DOWN
            return getDurationSince(d.lastdown_raw);
        } else {
            // Warning / Paused / Unusual: sejak transisi terakhir yang tercatat
            const rawTime = d.lastup_raw || d.lastdown_raw;
            return rawTime ? getDurationSince(rawTime) : "tidak diketahui";
        }
    };

    const prtgCustomers = (prtg || []).filter((d) => d && d.device && isCustomer(d.device));
    const filtered = prtgCustomers.filter((d) => {
        const devName = d && d.device ? String(d.device) : "";
        return searchQuery ? devName.toLowerCase().includes(searchQuery.toLowerCase()) : true;
    });

    // STATUS (Dihitung dari seluruh device prtg pelanggan agar konsisten)
    const online = prtgCustomers.filter((d) => getDeviceStatus(d) === "online").length;
    const down = prtgCustomers.filter((d) => getDeviceStatus(d) === "down").length;
    const warning = prtgCustomers.filter((d) => getDeviceStatus(d) === "warning").length;
    const paused = prtgCustomers.filter((d) => getDeviceStatus(d) === "paused").length;

    console.log("PRTG Dashboard Calculations:", {
        totalRaw: prtg.length,
        totalCustomers: prtgCustomers.length,
        online,
        down,
        warning,
        paused
    });

    // ----------------------------------------------------
    // Chart.js Data & Options Configuration
    // ----------------------------------------------------
    const ticketChartData = {
        labels: (data.monthly_ticket_history || []).map((h) => h.label),
        datasets: [
            {
                label: "Tiket Terbuka",
                data: (data.monthly_ticket_history || []).map((h) => h.open),
                backgroundColor: "rgba(245, 158, 11, 0.8)",
                borderColor: "#f59e0b",
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: "Tiket Selesai",
                data: (data.monthly_ticket_history || []).map((h) => h.done),
                backgroundColor: "rgba(16, 185, 129, 0.8)",
                borderColor: "#10b981",
                borderWidth: 1,
                borderRadius: 6,
            },
        ],
    };

    const ticketChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "top",
                labels: {
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: "bold" },
                    color: "#71717A",
                },
            },
        },
        scales: {
            x: {
                grid: { color: "rgba(0, 0, 0, 0.05)" },
                ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10, weight: "600" }, color: "#71717A" },
            },
            y: {
                beginAtZero: true,
                grid: { color: "rgba(0, 0, 0, 0.05)" },
                ticks: { precision: 0, font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }, color: "#71717A" },
            },
        },
    };

    const financeChartData = {
        labels: ["Pemasukan (Terbayar)", "Sisa Tunggakan"],
        datasets: [
            {
                data: [data.total_income || 0, data.unpaid_total_amount || 0],
                backgroundColor: ["rgba(16, 185, 129, 0.8)", "rgba(239, 68, 68, 0.8)"],
                borderColor: ["#10b981", "#ef4444"],
                borderWidth: 1.5,
            },
        ],
    };

    const financeChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: "bold" },
                    color: "#71717A",
                },
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        let label = context.label || "";
                        if (label) label += ": ";
                        if (context.raw !== null) {
                            label += new Intl.NumberFormat("id-ID", {
                                style: "currency",
                                currency: "IDR",
                                maximumFractionDigits: 0,
                            }).format(context.raw);
                        }
                        return label;
                    },
                },
            },
        },
        cutout: "65%",
    };

    const deviceChartData = {
        labels: ["Online", "Down", "Warning", "Paused"],
        datasets: [
            {
                data: [online, down, warning, paused],
                backgroundColor: [
                    "rgba(16, 185, 129, 0.8)",
                    "rgba(239, 68, 68, 0.8)",
                    "rgba(245, 158, 11, 0.8)",
                    "rgba(100, 116, 139, 0.8)",
                ],
                borderColor: ["#10b981", "#ef4444", "#f59e0b", "#64748b"],
                borderWidth: 1.5,
            },
        ],
    };

    const deviceChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: "bold" },
                    color: "#71717A",
                },
            },
        },
        cutout: "65%",
    };

    return (
        <main style={appContainer}>
            <style>{`
                body { margin: 0; background-color: transparent; }
                * { box-sizing: border-box; }
                .modal-anim { animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: translateY(20px) scale(0.95); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
                ::-webkit-scrollbar { width: 8px; }
                ::-webkit-scrollbar-track { background: #FAF9F6; border-radius: 4px; }
                ::-webkit-scrollbar-thumb { background: #E4E4E7; border-radius: 4px; }
                ::-webkit-scrollbar-thumb:hover { background: #D4D4D8; }

                /* Efek visual hover, shadow, dan transition untuk tombol */
                .btn-hover {
                    transition: all 0.2s ease-in-out !important;
                }
                .btn-hover:hover {
                    transform: translateY(-2px) !important;
                    filter: brightness(1.1) !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                }
                .btn-hover:active {
                    transform: translateY(0) !important;
                }

                /* Efek visual hover, shadow, dan transition untuk gambar */
                .img-hover {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                }
                .img-hover:hover {
                    transform: scale(1.1) rotate(3deg) !important;
                    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2) !important;
                    border-color: #3b82f6 !important;
                }

                /* Animasi Pulse untuk Badge & Indikator */
                @keyframes pulseGreen {
                    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
                    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
                    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
                }
                .pulse-green {
                    animation: pulseGreen 2s infinite !important;
                }

                @keyframes pulseRed {
                    0% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                    70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
                    100% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
                }
                .pulse-red {
                    animation: pulseRed 1.5s infinite !important;
                }

                @keyframes pulseOrange {
                    0% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
                    70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(245, 158, 11, 0); }
                    100% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
                }
                .pulse-orange {
                    animation: pulseOrange 1.8s infinite !important;
                }
            `}</style>

            <header style={headerStyle}>
                <h2 style={titleStyle}>Dashboard Utama</h2>

                {/* Form Pencarian & Filter menggunakan tag <form> dan berbagai <input type> */}
                <form style={filterFormStyle} onSubmit={(e) => e.preventDefault()}>
                    {canViewFinance && (
                        <input
                            type="month"
                            value={month}
                            onChange={(e) => setMonth(e.target.value)}
                            style={{ ...inputStyle, marginRight: "10px" }}
                        />
                    )}
                    <input
                        type="text"
                        placeholder="Cari data..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        style={inputSearchStyle}
                    />
                </form>
            </header>

            <div style={grid}>
                <Card
                    title="Total Pelanggan"
                    value={data.total_customers || 0}
                    color="#3b82f6"
                    onClick={() =>
                        setModal({
                            title: "Daftar Pelanggan",
                            type: "customers",
                            data: data.customers || [],
                            link: "/customers",
                        })
                    }
                />

                <Card
                    title="Tiket Terbuka"
                    value={data.tickets_open_total || 0}
                    subtitle={`Bulan ini: ${data.tickets_open_month || 0}`}
                    color="#f59e0b"
                    onClick={() =>
                        setModal({
                            title: "Daftar Tiket Terbuka",
                            type: "tickets",
                            data: data.open_tickets_list || [],
                            link: "/tickets",
                        })
                    }
                />

                <Card
                    title="Tiket Selesai"
                    value={data.tickets_done_total || 0}
                    subtitle={`Bulan ini: ${data.tickets_done_month || 0}`}
                    color="#10b981"
                    onClick={() =>
                        setModal({
                            title: "Daftar Tiket Selesai",
                            type: "tickets",
                            data: data.done_tickets_list || [],
                            link: "/tickets",
                        })
                    }
                />

                {canViewFinance && (
                    <>
                <Card
                    title="Total Invoice"
                    value={data.total_invoices || 0}
                    color="#6366f1"
                    onClick={() =>
                        setModal({
                            title: "Daftar Invoice",
                            type: "invoice",
                            data: data.invoice_list || [],
                            link: "/invoices",
                        })
                    }
                />

                <Card
                    title="Belum Dibayar"
                    value={data.unpaid_invoices || 0}
                    color="#ef4444"
                    onClick={() =>
                        setModal({
                            title: "Invoice Belum Dibayar",
                            type: "unpaid",
                            data: data.unpaid_list || [],
                            link: "/invoices",
                        })
                    }
                />

                <Card
                    title="Total Income"
                    value={"Rp " + (data.total_income || 0).toLocaleString("id-ID")}
                    color="#10b981"
                    onClick={() =>
                        setModal({
                            title: "Total Income",
                            type: "info",
                            data: [
                                "Total pemasukan bulan ini:",
                                "Rp " + (data.total_income || 0).toLocaleString("id-ID"),
                            ],
                        })
                    }
                />

                <Card
                    title="Total Tunggakan"
                    value={"Rp " + (data.unpaid_total_amount || 0).toLocaleString("id-ID")}
                    color="#f59e0b"
                    onClick={() =>
                        setModal({
                            title: "Detail Tunggakan",
                            type: "unpaid",
                            data: data.unpaid_list || [],
                            link: "/invoices",
                        })
                    }
                />
                    </>
                )}

                {/* PRTG */}
                {canViewDevices && (
                    <>
                <Card
                    title="Device Online"
                    value={online}
                    color="#346538"
                    onClick={() =>
                        setModal({
                            title: "Daftar Device Online",
                            type: "prtg",
                            data: prtgCustomers.filter((d) => getDeviceStatus(d) === "online"),
                        })
                    }
                />

                <Card
                    title="Device Down"
                    value={down}
                    color="#9F2F2D"
                    onClick={() =>
                        setModal({
                            title: "Daftar Device Down (Offline)",
                            type: "prtg",
                            data: prtgCustomers.filter((d) => getDeviceStatus(d) === "down"),
                        })
                    }
                />

                <Card
                    title="Device Warning"
                    value={warning}
                    color="#956400"
                    onClick={() =>
                        setModal({
                            title: "Daftar Device Warning",
                            type: "prtg",
                            data: prtgCustomers.filter((d) => getDeviceStatus(d) === "warning"),
                        })
                    }
                />

                <Card
                    title="Device Paused"
                    value={paused}
                    color="#787774"
                    onClick={() =>
                        setModal({
                            title: "Daftar Device Paused",
                            type: "prtg",
                            data: prtgCustomers.filter((d) => getDeviceStatus(d) === "paused"),
                        })
                    }
                />
                    </>
                )}
            </div>

            {/* GRID GRAFIK DASHBOARD */}
            <div style={chartGridStyle}>
                {/* 1. Bar Chart: Riwayat Tiket Bulanan */}
                <div style={chartCardStyle}>
                    <h3 style={chartCardTitleStyle}>Statistik Tiket Bulanan</h3>
                    <p style={{ margin: "0 0 15px 0", color: "#787774", fontSize: "11px" }}>
                        Jumlah tiket terbuka vs diselesaikan selama 6 bulan terakhir.
                    </p>
                    <div style={{ height: "230px", position: "relative" }}>
                        <Bar data={ticketChartData} options={ticketChartOptions} />
                    </div>
                    <div style={{ marginTop: "15px", display: "flex", flexWrap: "wrap", gap: "6px", justifyContent: "center" }}>
                        {(data.monthly_ticket_history || []).map((h, i) => (
                             <button
                                 key={i}
                                 type="button"
                                 onClick={() => fetchMonthlyTickets(h.month, h.label)}
                                 style={{
                                     padding: "4px 8px",
                                     fontSize: "10px",
                                     fontWeight: "700",
                                     color: "#111111",
                                     background: "#F4F4F5",
                                     border: "1px solid #E4E4E7",
                                     borderRadius: "4px",
                                     cursor: "pointer",
                                 }}
                             >
                                 {h.label.split(' ')[0]} ({h.total})
                             </button>
                        ))}
                    </div>
                </div>

                {/* 2. Doughnut Chart: Ringkasan Pemasukan & Tunggakan */}
                {canViewFinance && (
                    <div style={chartCardStyle}>
                        <h3 style={chartCardTitleStyle}>Ringkasan Keuangan ({data.month || month})</h3>
                        <p style={{ margin: "0 0 15px 0", color: "#8E8E90", fontSize: "11px" }}>
                            Perbandingan antara realisasi pemasukan dengan sisa piutang tagihan.
                        </p>
                        <div style={{ height: "230px", position: "relative" }}>
                            <Doughnut data={financeChartData} options={financeChartOptions} />
                        </div>
                        <div style={{ marginTop: "15px", padding: "10px", background: "#F4F4F5", borderRadius: "6px", border: "1px solid #E4E4E7", display: "flex", justifyContent: "space-between", fontSize: "11px" }}>
                            <div>
                                <span style={{ color: "#71717A" }}>Efisiensi Penagihan:</span>
                                <strong style={{ display: "block", color: "#059669", fontSize: "14px" }}>
                                    {((data.total_income || 0) + (data.unpaid_total_amount || 0)) > 0 
                                        ? Math.round((data.total_income / ((data.total_income || 0) + (data.unpaid_total_amount || 0))) * 100) 
                                        : 0}%
                                </strong>
                            </div>
                            <div style={{ textAlign: "right" }}>
                                <span style={{ color: "#71717A" }}>Total Tagihan Terbit:</span>
                                <strong style={{ display: "block", color: "#111111", fontSize: "14px" }}>
                                    Rp {Number((data.total_income || 0) + (data.unpaid_total_amount || 0)).toLocaleString("id-ID")}
                                </strong>
                            </div>
                        </div>
                    </div>
                )}

                {/* 3. Doughnut Chart: Status Jaringan Device */}
                {canViewDevices && (
                    <div style={chartCardStyle}>
                        <h3 style={chartCardTitleStyle}>Status Jaringan Device</h3>
                        <p style={{ margin: "0 0 15px 0", color: "#8E8E90", fontSize: "11px" }}>
                            Status kesehatan koneksi perangkat pelanggan (PRTG Monitoring).
                        </p>
                        <div style={{ height: "230px", position: "relative" }}>
                            <Doughnut data={deviceChartData} options={deviceChartOptions} />
                        </div>
                        <div style={{ marginTop: "15px", padding: "10px", background: "#F4F4F5", borderRadius: "6px", border: "1px solid #E4E4E7", display: "flex", justifyContent: "space-around", fontSize: "10px", fontWeight: "bold" }}>
                            <span style={{ color: "#059669" }}>Online: {online}</span>
                            <span style={{ color: "#DC2626" }}>Down: {down}</span>
                            <span style={{ color: "#D97706" }}>Warn: {warning}</span>
                            <span style={{ color: "#71717A" }}>Pause: {paused}</span>
                        </div>
                    </div>
                )}
            </div>





            {/* MODAL */}
            {modal && (
                <div style={overlay} onClick={() => setModal(null)}>
                    <div style={{ ...modalBox, maxWidth: modal.type === "prtg" ? "750px" : "400px", width: "95%" }} className="modal-anim" onClick={(e) => e.stopPropagation()}>
                        <h3 className="m-0 mb-4 text-lg font-bold text-[#111111]">{modal.title}</h3>

                        <div style={{ ...content, maxHeight: modal.type === "prtg" ? "500px" : "350px" }}>
                            {modal.type === "customers" &&
                                modal.data.map((item) => (
                                    <div key={item.id} style={listItem}>
                                        {item.name}
                                    </div>
                                    ))}

                            {modal.type === "tickets" &&
                                modal.data.map((item) => (
                                    <div key={item.id} style={{ ...listItem, flexDirection: "column", alignItems: "flex-start", gap: "4px" }}>
                                        <div style={{ display: "flex", justifyContent: "space-between", width: "100%", fontWeight: "bold" }}>
                                            <span>{item.title}</span>
                                            <span style={{ 
                                                fontSize: "10px", 
                                                padding: "2px 6px", 
                                                borderRadius: "4px", 
                                                background: item.status === 'open' ? 'rgba(245, 158, 11, 0.15)' : 'rgba(16, 185, 129, 0.15)', 
                                                color: item.status === 'open' ? '#f59e0b' : '#10b981',
                                                border: `1px solid ${item.status === 'open' ? 'rgba(245, 158, 11, 0.2)' : 'rgba(16, 185, 129, 0.2)'}`
                                            }}>
                                                {item.status === 'open' ? 'Open' : 'Done'}
                                            </span>
                                        </div>
                                        <div style={{ fontSize: "13px", color: "#71717A" }}>
                                            Pelanggan: <strong style={{ color: "#111111" }}>{item.customer?.name || '-'}</strong>
                                        </div>
                                        {item.teknisi && (
                                            <div style={{ fontSize: "12px", color: "#71717A" }}>
                                                Teknisi: {item.teknisi.name}
                                            </div>
                                        )}
                                        <div style={{ fontSize: "11px", color: "#71717A" }}>
                                            Tanggal: {item.tanggal || '-'}
                                        </div>
                                    </div>
                                ))}

                            {modal.type === "invoice" &&
                                modal.data.map((item) => (
                                    <div key={item.id} style={listItem}>
                                        <div>
                                            {item.customer?.name}
                                            <br />
                                            <small style={{ color: "#8E8E90" }}>
                                                {item.status}
                                            </small>
                                        </div>
                                        <div>
                                            Rp {Number(item.amount).toLocaleString("id-ID")}
                                        </div>
                                    </div>
                                ))}

                            {modal.type === "unpaid" &&
                                modal.data.map((item) => (
                                    <div key={item.id} style={listItem}>
                                        <span>{item.customer?.name}</span>
                                        <span style={{ color: "#EF4444", fontWeight: "bold" }}>
                                            Rp {Number(item.amount).toLocaleString("id-ID")}
                                        </span>
                                    </div>
                                ))}

                            {modal.type === "info" &&
                                modal.data.map((text, i) => (
                                    <div key={i} style={listItem}>
                                        {text}
                                    </div>
                                ))}

                            {modal.type === "prtg" && (
                                <div style={{ overflowX: "auto" }} className="border border-[#E4E4E7] rounded-md">
                                    <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "13px", textAlign: "left" }}>
                                        <thead>
                                            <tr style={{ background: "#F4F4F5", borderBottom: "1px solid #E4E4E7" }}>
                                                <th style={{ padding: "10px", color: "#111111" }}>Device / Pelanggan</th>
                                                <th style={{ padding: "10px", color: "#111111" }}>Status</th>
                                                <th style={{ padding: "10px", color: "#111111" }}>Durasi Status</th>
                                                <th style={{ padding: "10px", color: "#111111" }}>Pesan PRTG</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {modal.data.length === 0 ? (
                                                <tr>
                                                    <td colSpan="4" style={{ padding: "20px", textAlign: "center", color: "#8E8E90" }}>
                                                        Tidak ada data device.
                                                    </td>
                                                </tr>
                                            ) : (
                                                modal.data.map((item, i) => {
                                                    const status = getDeviceStatus(item);
                                                    const duration = getDeviceDuration(item);
                                                    
                                                    // Status theme
                                                    const badgeStyle = {
                                                        fontSize: "10px",
                                                        padding: "4px 8px",
                                                        borderRadius: "999px",
                                                        fontWeight: "bold",
                                                        display: "inline-flex",
                                                        alignItems: "center",
                                                        gap: "4px",
                                                        background: 
                                                            status === "online" ? "rgba(16, 185, 129, 0.15)" :
                                                            status === "down" ? "rgba(239, 68, 68, 0.15)" :
                                                            status === "warning" ? "rgba(245, 158, 11, 0.15)" : "rgba(34, 34, 38, 0.5)",
                                                        color: 
                                                            status === "online" ? "#10B981" :
                                                            status === "down" ? "#EF4444" :
                                                            status === "warning" ? "#F59E0B" : "#8E8E90",
                                                        border: `1px solid ${
                                                            status === "online" ? "rgba(16, 185, 129, 0.2)" :
                                                            status === "down" ? "rgba(239, 68, 68, 0.2)" :
                                                            status === "warning" ? "rgba(245, 158, 11, 0.2)" : "rgba(34, 34, 38, 0.2)"
                                                        }`
                                                    };
 
                                                    return (
                                                        <tr key={i} style={{ borderBottom: "1px solid #E4E4E7" }}>
                                                            <td style={{ padding: "12px 10px", fontWeight: "600", color: "#111111" }}>
                                                                {item.device}
                                                            </td>
                                                            <td style={{ padding: "12px 10px" }}>
                                                                <span style={badgeStyle}>
                                                                    {status === 'down' && <span style={statusPulseRed} className="pulse-red"></span>}
                                                                    {status === 'warning' && <span style={statusPulseOrange} className="pulse-orange"></span>}
                                                                    {status.toUpperCase()}
                                                                </span>
                                                            </td>
                                                            <td style={{ padding: "12px 10px", color: "#111111" }}>
                                                                <strong>{duration}</strong>
                                                                <br />
                                                                <small style={{ color: "#71717A" }}>
                                                                    {status === 'online' ? `sejak offline: ${cleanHtml(item.lastdown) || "-"}` : `sejak online: ${cleanHtml(item.lastup) || "-"}`}
                                                                </small>
                                                            </td>
                                                            <td style={{ padding: "12px 10px", color: "#71717A", fontSize: "12px", maxWidth: "220px", wordBreak: "break-word" }}>
                                                                {cleanHtml(item.message) || "-"}
                                                             </td>
                                                        </tr>
                                                    );
                                                })
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>

                        <div style={{ display: "flex", gap: "10px", marginTop: "15px" }}>
                            {modal.link && (
                                <a href={modal.link} style={btnBlue} className="btn-hover">
                                    Lihat Semua
                                </a>
                            )}
                            <button style={btn} onClick={() => setModal(null)} className="btn-hover">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* MONTHLY TICKETS DETAIL MODAL */}
            {monthlyModal && (
                <div style={overlay} onClick={() => setMonthlyModal(null)}>
                    <div style={{ ...modalBox, maxWidth: "650px", width: "95%" }} className="modal-anim" onClick={(e) => e.stopPropagation()}>
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "15px", borderBottom: "1px solid #E4E4E7", paddingBottom: "10px" }}>
                            <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700", color: "#111111" }}>{monthlyModal.title}</h3>
                            <span style={{ fontSize: "12px", background: "rgba(99, 102, 241, 0.1)", color: "#6366F1", border: "1px solid rgba(99, 102, 241, 0.2)", padding: "4px 10px", borderRadius: "8px", fontWeight: "bold" }}>
                                {monthlyModal.data.length} Tiket
                            </span>
                        </div>

                        <div style={{ ...content, maxHeight: "400px" }}>
                            {monthlyModal.data.length === 0 ? (
                                <div style={{ textAlign: "center", padding: "40px 10px", color: "#8E8E90" }}>
                                    📭 Tidak ada tiket pada bulan ini.
                                </div>
                            ) : (
                                <div style={{ overflowX: "auto" }} className="border border-[#E4E4E7] rounded-md">
                                    <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "13px", textAlign: "left" }} className="app-table">
                                        <thead>
                                            <tr style={{ background: "#F4F4F5", borderBottom: "1px solid #E4E4E7" }}>
                                                <th style={{ padding: "8px 10px", color: "#6366F1", fontWeight: "bold" }}>No</th>
                                                <th style={{ padding: "8px 10px", color: "#6366F1", fontWeight: "bold" }}>Judul</th>
                                                <th style={{ padding: "8px 10px", color: "#6366F1", fontWeight: "bold" }}>Pelanggan</th>
                                                <th style={{ padding: "8px 10px", color: "#6366F1", fontWeight: "bold" }}>Teknisi</th>
                                                <th style={{ padding: "8px 10px", color: "#6366F1", fontWeight: "bold" }}>Tanggal</th>
                                                <th style={{ padding: "8px 10px", color: "#6366F1", fontWeight: "bold" }}>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {monthlyModal.data.map((t, index) => (
                                                <tr key={t.id} style={{ borderBottom: "1px solid #E4E4E7" }}>
                                                    <td style={{ padding: "10px", color: "#111111" }}>{index + 1}</td>
                                                    <td style={{ padding: "10px", fontWeight: "600", color: "#111111" }}>{t.title}</td>
                                                    <td style={{ padding: "10px", color: "#111111" }}>{t.customer?.name || "-"}</td>
                                                    <td style={{ padding: "10px", color: "#111111" }}>{t.teknisi?.name || "-"}</td>
                                                    <td style={{ padding: "10px", color: "#111111" }}>{t.tanggal || "-"}</td>
                                                    <td style={{ padding: "10px" }}>
                                                        <span style={{ 
                                                            fontSize: "11px", 
                                                            padding: "3px 8px", 
                                                            borderRadius: "999px", 
                                                            fontWeight: "bold",
                                                            background: t.status === 'open' ? 'rgba(245, 158, 11, 0.15)' : 'rgba(16, 185, 129, 0.15)', 
                                                            color: t.status === 'open' ? '#F59E0B' : '#10B981',
                                                            border: `1px solid ${t.status === 'open' ? 'rgba(245, 158, 11, 0.2)' : 'rgba(16, 185, 129, 0.2)'}`
                                                        }}>
                                                            {t.status === 'open' ? 'Open' : 'Done'}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>

                        <div style={{ display: "flex", gap: "10px", marginTop: "20px", borderTop: "1px solid #E4E4E7", paddingTop: "15px" }}>
                            {monthlyModal.data.length > 0 && (
                                <button 
                                    style={{ ...btnBlue, flex: 1, padding: "10px 16px", cursor: "pointer", border: "none" }} 
                                    onClick={() => exportToCSV(monthlyModal.data, monthlyModal.monthLabel)} 
                                    className="btn-hover"
                                >
                                    📥 Ekspor ke Excel (CSV)
                                </button>
                            )}
                            <button 
                                style={{ ...btn, flex: monthlyModal.data.length > 0 ? 0.5 : 1, padding: "10px 16px", border: "none" }} 
                                onClick={() => setMonthlyModal(null)} 
                                className="btn-hover"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* LOADING OVERLAY */}
            {loadingMonthly && (
                <div style={overlay}>
                    <div style={{ background: "#FFFFFF", border: "1px solid #E4E4E7", padding: "20px 30px", borderRadius: "12px", textAlign: "center", boxShadow: "0 10px 25px rgba(0,0,0,0.05)", display: "flex", flexDirection: "column", alignItems: "center", gap: "10px" }}>
                        <span style={{ fontSize: "28px" }} className="animate-pulse">⏳</span>
                        <p style={{ margin: 0, fontWeight: "bold", color: "#111111", fontSize: "14px" }}>Memuat Data Tiket...</p>
                    </div>
                </div>
            )}

            {/* Semantic <footer> */}
            <footer style={footerStyle}>
                <p style={{ margin: 0 }}>&copy; {new Date().getFullYear()} ISP Billing API Dashboard. Semua Hak Cipta Dilindungi.</p>
                <div style={{ display: "flex", gap: "10px", alignItems: "center" }}>
                    <span style={{ color: "#3b82f6", fontWeight: "bold" }}>Axios & React Portal</span>
                </div>
            </footer>
        </main>
    );
}

function PrtgMonitoringPanel({ prtgCustomers, getDeviceStatus, getDeviceDuration }) {
    const [activeTab, setActiveTab] = useState("all");
    const [localSearch, setLocalSearch] = useState("");

    // Counts
    const counts = {
        all: prtgCustomers.length,
        online: prtgCustomers.filter(d => getDeviceStatus(d) === "online").length,
        down: prtgCustomers.filter(d => getDeviceStatus(d) === "down").length,
        warning: prtgCustomers.filter(d => getDeviceStatus(d) === "warning").length,
        paused: prtgCustomers.filter(d => getDeviceStatus(d) === "paused").length
    };

    // Filtered list
    const filteredList = prtgCustomers.filter(d => {
        const matchesTab = activeTab === "all" || getDeviceStatus(d) === activeTab;
        const deviceName = d && d.device ? String(d.device) : "";
        const messageText = d && d.message ? String(d.message) : "";
        const matchesSearch = deviceName.toLowerCase().includes(localSearch.toLowerCase()) || 
                              messageText.toLowerCase().includes(localSearch.toLowerCase());
        return matchesTab && matchesSearch;
    });

    return (
        <section style={historySectionStyle}>
            {/* Header */}
            <div style={historyHeaderStyle}>
                <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                    <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700", display: "flex", alignItems: "center", gap: "8px" }}>
                        📡 Monitoring Status Device (PRTG)
                    </h3>
                    <div style={{ display: "flex", alignItems: "center", gap: "6px", background: "rgba(16, 185, 129, 0.08)", border: "1px solid rgba(16, 185, 129, 0.2)", padding: "4px 10px", borderRadius: "12px" }}>
                        <span style={livePulseDot} className="pulse-green"></span>
                        <span style={{ fontSize: "12px", color: "#059669", fontWeight: "600" }}>Live</span>
                    </div>
                </div>
                <span style={historyBadgeStyle}>{counts.all} Total Device</span>
            </div>

            <p style={{ margin: "0 0 20px 0", color: "#8E8E90", fontSize: "14px" }}>
                Status real-time pelanggan yang terhubung ke jaringan. Data diperbarui otomatis setiap 10 detik.
            </p>

            {/* Controls Row */}
            <div style={panelControlsRow}>
                {/* Tabs */}
                <div style={tabContainer}>
                    <TabButton active={activeTab === "all"} onClick={() => setActiveTab("all")} label="Semua" count={counts.all} color="#8B5CF6" />
                    <TabButton active={activeTab === "online"} onClick={() => setActiveTab("online")} label="Online" count={counts.online} color="#10b981" />
                    <TabButton active={activeTab === "down"} onClick={() => setActiveTab("down")} label="Down" count={counts.down} color="#ef4444" />
                    <TabButton active={activeTab === "warning"} onClick={() => setActiveTab("warning")} label="Warning" count={counts.warning} color="#f59e0b" />
                    <TabButton active={activeTab === "paused"} onClick={() => setActiveTab("paused")} label="Paused" count={counts.paused} color="#222226" />
                </div>

                {/* Local Search */}
                <input 
                    type="text"
                    placeholder="Cari nama device/pesan..."
                    value={localSearch}
                    onChange={(e) => setLocalSearch(e.target.value)}
                    style={localSearchInput}
                />
            </div>

            {/* Device Grid */}
            <div style={deviceGridStyle}>
                {filteredList.length === 0 ? (
                    <div style={{ gridColumn: "1 / -1", textAlign: "center", padding: "40px", color: "#71717A", background: "#F8F9FA", borderRadius: "10px", border: "1px dashed #D4D4D8" }}>
                        🔍 Tidak ada device yang cocok dengan kriteria filter/pencarian.
                    </div>
                ) : (
                    filteredList.map((d, index) => {
                        const status = getDeviceStatus(d);
                        const duration = getDeviceDuration(d);
                        
                        // Theme styling berdasarkan status
                        const theme = {
                            online: { bg: "rgba(16, 185, 129, 0.05)", border: "rgba(16, 185, 129, 0.2)", icon: "🟢", color: "#10B981", badgeBg: "rgba(16, 185, 129, 0.1)", badgeColor: "#059669" },
                            down: { bg: "rgba(239, 68, 68, 0.05)", border: "rgba(239, 68, 68, 0.2)", icon: "🔴", color: "#EF4444", badgeBg: "rgba(239, 68, 68, 0.1)", badgeColor: "#DC2626", pulse: true },
                            warning: { bg: "rgba(245, 158, 11, 0.05)", border: "rgba(245, 158, 11, 0.2)", icon: "🟠", color: "#F59E0B", badgeBg: "rgba(245, 158, 11, 0.1)", badgeColor: "#D97706", pulse: true },
                            paused: { bg: "#F8F9FA", border: "#E4E4E7", icon: "🟡", color: "#71717A", badgeBg: "#F4F4F5", badgeColor: "#71717A" },
                            unknown: { bg: "#F8F9FA", border: "#E4E4E7", icon: "⚪", color: "#71717A", badgeBg: "#F4F4F5", badgeColor: "#71717A" }
                        }[status] || { bg: "#F8F9FA", border: "#E4E4E7", icon: "⚪", color: "#71717A", badgeBg: "#F4F4F5", badgeColor: "#71717A" };

                        return (
                            <div key={index} style={{ ...deviceCardStyle, backgroundColor: theme.bg, borderColor: theme.border }}>
                                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: "10px" }}>
                                    <div style={{ fontWeight: "700", color: "#111111", fontSize: "14px", display: "flex", alignItems: "center", gap: "6px" }}>
                                        <span>📡</span>
                                        <span>{d.device}</span>
                                    </div>
                                    <span style={{ 
                                        fontSize: "10px", 
                                        padding: "3px 8px", 
                                        borderRadius: "999px", 
                                        fontWeight: "bold",
                                        background: theme.badgeBg,
                                        color: theme.badgeColor,
                                        display: "flex",
                                        alignItems: "center",
                                        gap: "4px"
                                    }}>
                                        {theme.pulse && <span style={status === 'down' ? statusPulseRed : statusPulseOrange} className={status === 'down' ? "pulse-red" : "pulse-orange"}></span>}
                                        {status.toUpperCase()}
                                    </span>
                                </div>

                                <div style={{ fontSize: "13px", color: "#111111", marginBottom: "8px", display: "flex", alignItems: "center", gap: "6px" }}>
                                    <span>🕒</span>
                                    <span>
                                        <strong>Durasi:</strong> {duration}
                                    </span>
                                </div>

                                {(d.lastup || d.lastdown) && (
                                    <div style={{ fontSize: "11px", color: "#71717A", marginBottom: "8px" }}>
                                        {status === 'online' ? `Terakhir offline: ${cleanHtml(d.lastdown) || "-"}` : `Terakhir online: ${cleanHtml(d.lastup) || "-"}`}
                                    </div>
                                )}

                                <div style={{ 
                                    background: "#ffffff", 
                                    padding: "8px 12px", 
                                    borderRadius: "8px", 
                                    fontSize: "12px", 
                                    color: "#64748b",
                                    border: "1px solid #f1f5f9",
                                    minHeight: "42px",
                                    display: "flex",
                                    alignItems: "center",
                                    wordBreak: "break-word"
                                }}>
                                    {cleanHtml(d.message) || "Tidak ada rincian pesan."}
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </section>
    );
}

function TabButton({ active, onClick, label, count, color }) {
    const [isHovered, setIsHovered] = useState(false);
    return (
        <button
            type="button"
            onClick={onClick}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
            style={{
                padding: "6px 14px",
                borderRadius: "8px",
                border: "none",
                cursor: "pointer",
                fontWeight: "600",
                fontSize: "13px",
                display: "flex",
                alignItems: "center",
                gap: "8px",
                background: active ? color : isHovered ? "#E4E4E7" : "transparent",
                color: active ? "#FFFFFF" : "#71717A",
                transition: "all 0.2s ease"
            }}
        >
            {label}
            <span style={{
                fontSize: "11px",
                padding: "2px 6px",
                borderRadius: "999px",
                background: active ? "rgba(255,255,255,0.25)" : "#E4E4E7",
                color: active ? "#FFFFFF" : "#71717A"
            }}>
                {count}
            </span>
        </button>
    );
}

function MikrotikMonitoringPanel({ data, loading }) {
    const [activeSection, setActiveSection] = useState("resources"); // "resources", "active_users", "address_lists"
    const [searchTerm, setSearchTerm] = useState("");

    if (loading && !data) {
        return (
            <div style={historySectionStyle}>
                <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700" }}>Router MikroTik Monitor</h3>
                <p style={{ color: "#787774", margin: "10px 0" }}>Menghubungi router MikroTik...</p>
            </div>
        );
    }

    if (!data || !data.connected) {
        return (
            <div style={{ ...historySectionStyle, borderLeft: "5px solid #9F2F2D" }}>
                <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700", display: "flex", alignItems: "center", gap: "8px" }}>
                    Router MikroTik Monitor <span style={{ fontSize: "11px", background: "#FDEBEC", color: "#9F2F2D", padding: "2px 8px", borderRadius: "4px" }}>Disconnected</span>
                </h3>
                <p style={{ color: "#787774", fontSize: "14px", marginTop: "10px" }}>
                    {data?.message || "Koneksi ke MikroTik belum dikonfigurasi. Silakan masuk ke Pengaturan Sistem untuk menghubungkan router."}
                </p>
                <a href="/settings?tab=mikrotik" style={{ ...btnBlue, display: "inline-block", marginTop: "10px", textDecoration: "none", fontSize: "12px", width: "auto", flex: "none", padding: "8px 16px" }} className="btn-hover">
                    Konfigurasi MikroTik
                </a>
            </div>
        );
    }

    const { resources, active_users, address_lists } = data;

    // Filter active users
    const filteredActive = active_users.filter(u => 
        u.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.service.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Filter address lists
    const filteredAddresses = address_lists.filter(entry => 
        entry.customer_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.customer_code.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.list.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.comment.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // RAM calculation
    const ramFreeMb = Math.round(resources.free_memory / 1048576);
    const ramTotalMb = Math.round(resources.total_memory / 1048576);
    const ramUsedMb = ramTotalMb - ramFreeMb;
    const ramUsedPercent = ramTotalMb > 0 ? Math.round((ramUsedMb / ramTotalMb) * 100) : 0;

    return (
        <section style={{ ...historySectionStyle, borderLeft: "5px solid #6366F1" }}>
            {/* Header */}
            <div style={historyHeaderStyle}>
                <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                    <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700", display: "flex", alignItems: "center", gap: "8px" }}>
                        Router MikroTik Monitor
                    </h3>
                    <div style={{ display: "flex", alignItems: "center", gap: "6px", background: "#EDF3EC", padding: "4px 10px", borderRadius: "4px", border: "1px solid #EDF3EC" }}>
                        <span style={livePulseDot}></span>
                        <span style={{ fontSize: "12px", color: "#346538", fontWeight: "600" }}>Connected</span>
                    </div>
                </div>
                <span style={{ ...historyBadgeStyle }}>
                    {resources.board_name}
                </span>
            </div>

            <p style={{ margin: "0 0 20px 0", color: "#787774", fontSize: "13px" }}>
                Status real-time, spesifikasi beban kerja CPU, RAM, serta pemetaan firewall address-list pelanggan MikroTik.
            </p>

            {/* Navigation Tabs */}
            <div style={panelControlsRow}>
                <div style={tabContainer}>
                    <button 
                        type="button"
                        onClick={() => setActiveSection("resources")} 
                        style={{
                            padding: "6px 12px", borderRadius: "6px", border: "none", cursor: "pointer", fontWeight: "600", fontSize: "12px",
                            background: activeSection === "resources" ? "#6366F1" : "transparent",
                            color: activeSection === "resources" ? "#FFFFFF" : "#71717A",
                            transition: "all 0.15s ease"
                        }}
                    >
                        Resources
                    </button>
                    <button 
                        type="button"
                        onClick={() => setActiveSection("active_users")} 
                        style={{
                            padding: "6px 12px", borderRadius: "6px", border: "none", cursor: "pointer", fontWeight: "600", fontSize: "12px",
                            background: activeSection === "active_users" ? "#6366F1" : "transparent",
                            color: activeSection === "active_users" ? "#FFFFFF" : "#71717A",
                            transition: "all 0.15s ease"
                        }}
                    >
                        Active Users ({active_users.length})
                    </button>
                    <button 
                        type="button"
                        onClick={() => setActiveSection("address_lists")} 
                        style={{
                            padding: "6px 12px", borderRadius: "6px", border: "none", cursor: "pointer", fontWeight: "600", fontSize: "12px",
                            background: activeSection === "address_lists" ? "#6366F1" : "transparent",
                            color: activeSection === "address_lists" ? "#FFFFFF" : "#71717A",
                            transition: "all 0.15s ease"
                        }}
                    >
                        Address List Pelanggan ({address_lists.length})
                    </button>
                </div>

                {activeSection !== "resources" && (
                    <input 
                        type="text"
                        placeholder="Cari..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        style={localSearchInput}
                    />
                )}
            </div>

            {/* SECTION 1: RESOURCES */}
            {activeSection === "resources" && (
                <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))", gap: "20px", marginTop: "10px" }}>
                    {/* CPU CARD */}
                    <div style={{ background: "#F8F9FA", border: "1px solid #E4E4E7", borderRadius: "10px", padding: "16px", display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", minHeight: "150px" }}>
                        <span style={{ fontSize: "12px", color: "#71717A", fontWeight: "700", textTransform: "uppercase", marginBottom: "10px" }}>CPU Load</span>
                        <div style={{ position: "relative", width: "90px", height: "90px", display: "flex", alignItems: "center", justifyContent: "center" }}>
                            <svg width="90" height="90" viewBox="0 0 36 36">
                                <path stroke="#E4E4E7" strokeWidth="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path stroke="#6366F1" strokeWidth="3" strokeDasharray={`${resources.cpu_load}, 100`} fill="none" strokeLinecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span style={{ position: "absolute", fontSize: "16px", fontWeight: "800", color: "#111111", fontFamily: "monospace" }}>{resources.cpu_load}%</span>
                        </div>
                    </div>

                    {/* RAM CARD */}
                    <div style={{ background: "#F8F9FA", border: "1px solid #E4E4E7", borderRadius: "10px", padding: "16px", display: "flex", flexDirection: "column", justifyContent: "space-between", minHeight: "150px" }}>
                        <div>
                            <span style={{ display: "block", fontSize: "11px", color: "#71717A", fontWeight: "700", textTransform: "uppercase", marginBottom: "12px", letterSpacing: "0.05em" }}>Memory / RAM Usage</span>
                            <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", fontWeight: "bold", color: "#111111", marginBottom: "6px" }}>
                                <span>Terpakai: {ramUsedMb} MB</span>
                                <span style={{ color: "#71717A" }}>Total: {ramTotalMb} MB</span>
                            </div>
                            <div style={{ height: "8px", background: "#E4E4E7", borderRadius: "4px", overflow: "hidden" }}>
                                <div style={{ height: "100%", width: `${ramUsedPercent}%`, background: "linear-gradient(90deg, #6366F1, #8B5CF6)", borderRadius: "4px" }}></div>
                            </div>
                        </div>
                        <span style={{ fontSize: "10px", color: "#71717A", fontFamily: "monospace" }}>Bebas: {ramFreeMb} MB ({100 - ramUsedPercent}%)</span>
                    </div>

                    {/* SYSTEM INFO */}
                    <div style={{ background: "#F8F9FA", border: "1px solid #E4E4E7", borderRadius: "10px", padding: "16px", display: "flex", flexDirection: "column", gap: "10px", minHeight: "150px" }}>
                        <span style={{ fontSize: "11px", color: "#71717A", fontWeight: "700", textTransform: "uppercase", letterSpacing: "0.05em" }}>System Details</span>
                        <div style={{ fontSize: "12px", color: "#111111" }}>
                            <div style={{ display: "flex", justifyContent: "space-between", padding: "4px 0", borderBottom: "1px solid #E4E4E7" }}>
                                <strong>Uptime:</strong> <span>{resources.uptime}</span>
                            </div>
                            <div style={{ display: "flex", justifyContent: "space-between", padding: "4px 0", borderBottom: "1px solid #E4E4E7" }}>
                                <strong>Version:</strong> <span>v{resources.version}</span>
                            </div>
                            <div style={{ display: "flex", justifyContent: "space-between", padding: "4px 0" }}>
                                <strong>CPU Freq:</strong> <span>{resources.cpu_frequency} MHz</span>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* SECTION 2: ACTIVE USERS */}
            {activeSection === "active_users" && (
                <div style={{ overflowX: "auto", marginTop: "10px" }} className="border border-[#E4E4E7] rounded-md">
                    <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "12px", textAlign: "left" }} className="app-table">
                        <thead>
                            <tr>
                                <th style={{ padding: "10px" }}>Username</th>
                                <th style={{ padding: "10px" }}>IP Address</th>
                                <th style={{ padding: "10px" }}>Service</th>
                                <th style={{ padding: "10px" }}>Uptime</th>
                                <th style={{ padding: "10px" }}>MAC / Caller ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredActive.length === 0 ? (
                                <tr>
                                    <td colSpan="5" style={{ padding: "30px", textAlign: "center", color: "#8E8E90", fontFamily: "monospace" }}>
                                        [Tidak ada pengguna aktif yang cocok]
                                    </td>
                                </tr>
                            ) : (
                                filteredActive.map((user, i) => (
                                    <tr key={i}>
                                        <td style={{ padding: "12px 10px" }}>
                                            <div style={{ fontWeight: "700", color: "#111111" }}>{user.customer_name || user.name}</div>
                                            {user.customer_name && <small style={{ color: "#71717A", fontFamily: "monospace" }}>PPPoE: <code>{user.name}</code></small>}
                                        </td>
                                        <td style={{ padding: "12px 10px", color: "#111111", fontWeight: "600", fontFamily: "monospace" }}>{user.address}</td>
                                        <td style={{ padding: "12px 10px" }}>
                                            <span style={{ 
                                                fontSize: "10px", 
                                                padding: "2px 6px", 
                                                borderRadius: "4px", 
                                                fontWeight: "bold",
                                                background: user.service === "PPPoE" ? "rgba(16, 185, 129, 0.15)" : "rgba(245, 158, 11, 0.15)",
                                                color: user.service === "PPPoE" ? "#10B981" : "#F59E0B"
                                            }}>{user.service}</span>
                                        </td>
                                        <td style={{ padding: "12px 10px", color: "#71717A", fontFamily: "monospace" }}>{user.uptime}</td>
                                        <td style={{ padding: "12px 10px", color: "#71717A", fontFamily: "monospace" }}>{user.caller_id}</td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            )}

            {/* SECTION 3: ADDRESS LIST CUSTOMERS */}
            {activeSection === "address_lists" && (
                <div style={{ overflowX: "auto", marginTop: "10px" }} className="border border-[#E4E4E7] rounded-md">
                    <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "12px", textAlign: "left" }} className="app-table">
                        <thead>
                            <tr>
                                <th style={{ padding: "10px" }}>Pelanggan</th>
                                <th style={{ padding: "10px" }}>IP Address</th>
                                <th style={{ padding: "10px" }}>Address List</th>
                                <th style={{ padding: "10px" }}>Paket</th>
                                <th style={{ padding: "10px", minWidth: "150px" }}>Keterangan / Comment</th>
                                <th style={{ padding: "10px", textAlign: "center" }}>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredAddresses.length === 0 ? (
                                <tr>
                                    <td colSpan="6" style={{ padding: "30px", textAlign: "center", color: "#8E8E90", fontFamily: "monospace" }}>
                                        [Tidak ada data address list pelanggan yang cocok]
                                    </td>
                                </tr>
                            ) : (
                                filteredAddresses.map((entry, i) => {
                                    const isIsolated = entry.list.toLowerCase().includes("isolir") || 
                                                       entry.list.toLowerCase().includes("block") ||
                                                       entry.comment.toLowerCase().includes("isolir");
                                    return (
                                        <tr key={i}>
                                            <td style={{ padding: "12px 10px" }}>
                                                <div style={{ fontWeight: "700", color: "#111111" }}>{entry.customer_name}</div>
                                                <small style={{ color: "#71717A", fontFamily: "monospace" }}>Kode: <strong>{entry.customer_code}</strong></small>
                                            </td>
                                            <td style={{ padding: "12px 10px", color: "#111111", fontWeight: "600", fontFamily: "monospace" }}>{entry.address}</td>
                                            <td style={{ padding: "12px 10px" }}>
                                                <span style={{
                                                    fontSize: "10px", padding: "2px 6px", borderRadius: "4px", fontWeight: "bold",
                                                    background: isIsolated ? "rgba(239, 68, 68, 0.15)" : "rgba(16, 185, 129, 0.15)",
                                                    color: isIsolated ? "#EF4444" : "#10B981"
                                                }}>
                                                    {entry.list}
                                                </span>
                                            </td>
                                            <td style={{ padding: "12px 10px", color: "#71717A" }}>{entry.package_name}</td>
                                            <td style={{ padding: "12px 10px", color: "#71717A", fontSize: "11px" }}>{entry.comment || "-"}</td>
                                            <td style={{ padding: "12px 10px", textAlign: "center" }}>
                                                <span style={{
                                                    fontSize: "10px", padding: "2px 6px", borderRadius: "4px", fontWeight: "bold",
                                                    background: isIsolated ? "rgba(239, 68, 68, 0.15)" : "rgba(16, 185, 129, 0.15)",
                                                    color: isIsolated ? "#EF4444" : "#10B981",
                                                    border: `1px solid ${isIsolated ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)'}`
                                                }}>
                                                    {isIsolated ? "TERISOLIR" : "AKTIF"}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
}


/* CARD */
function Card({ title, value, subtitle, color, onClick }) {
    const [isHovered, setIsHovered] = useState(false);
    
    // Resolve clean background for subtle color indicators
    let indicatorColor = "#787774";
    if (color === "#10b981") indicatorColor = "#10B981";
    if (color === "#ef4444") indicatorColor = "#EF4444";
    if (color === "#f59e0b") indicatorColor = "#F59E0B";
    if (color === "#3b82f6" || color === "#6366f1") indicatorColor = "#3B82F6";

    return (
        <div
            onClick={onClick}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
            style={{
                ...card,
                cursor: "pointer",
                transform: isHovered ? "translateY(-3px)" : "translateY(0)",
                boxShadow: isHovered
                    ? "0 8px 24px rgba(99, 102, 241, 0.12)"
                    : "0 2px 12px rgba(0,0,0,0.04)",
            }}
        >
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: "12px" }}>
                <h4 style={{ color: "#71717A", margin: 0, fontSize: "11px", textTransform: "uppercase", letterSpacing: "0.05em", fontWeight: "700" }}>{title}</h4>
                <span style={{ width: "8px", height: "8px", borderRadius: "50%", background: indicatorColor, opacity: 0.8 }}></span>
            </div>
            <p style={{ fontSize: "26px", fontWeight: "700", color: "#111111", margin: 0, letterSpacing: "-0.02em" }}>{value}</p>
            {subtitle && (
                <p style={{ color: "#71717A", fontSize: "12px", fontWeight: "500", margin: "6px 0 0 0" }}>{subtitle}</p>
            )}
            <div style={{ marginTop: "14px", paddingTop: "10px", borderTop: "1px solid #E4E4E7", display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                <small style={{ color: "#71717A", fontWeight: "500", fontSize: "11px" }}>Detail &rarr;</small>
            </div>
        </div>
    );
}

/* STYLE */
const appContainer = {
    padding: "0px",
    maxWidth: "100%",
    margin: "0 auto",
    fontFamily: "'Geist Sans', -apple-system, sans-serif",
    color: "#111111"
};

const headerStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: "30px",
    paddingBottom: "15px",
    borderBottom: "1px solid #E4E4E7",
    flexWrap: "wrap",
    gap: "15px"
};

const titleStyle = {
    margin: 0,
    fontSize: "36px",
    fontWeight: "700",
    color: "#111111"
};

const inputStyle = {
    padding: "6px 12px",
    borderRadius: "6px",
    border: "1px solid #D4D4D8",
    fontSize: "12px",
    outline: "none",
    cursor: "pointer",
    background: "#FFFFFF",
    color: "#111111"
};

const grid = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))",
    gap: "20px",
};

const card = {
    background: "#FFFFFF",
    padding: "20px",
    borderRadius: "10px",
    border: "1px solid #E4E4E7",
    boxShadow: "0 2px 12px rgba(0,0,0,0.04)",
    color: "#111111",
    transition: "all 0.15s ease-in-out",
};

const overlay = {
    position: "fixed",
    top: 0,
    left: 0,
    width: "100%",
    height: "100%",
    background: "rgba(17, 17, 17, 0.45)",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    zIndex: 999,
    backdropFilter: "blur(6px)"
};

const modalBox = {
    background: "#FFFFFF",
    padding: "25px",
    borderRadius: "12px",
    border: "1px solid #E4E4E7",
    width: "90%",
    maxWidth: "500px",
    maxHeight: "85vh",
    display: "flex",
    flexDirection: "column",
    boxShadow: "0 20px 60px rgba(0,0,0,0.12)",
    color: "#111111"
};

const content = {
    maxHeight: "450px",
    overflowY: "auto",
    marginTop: "20px",
    paddingRight: "5px"
};

const listItem = {
    padding: "10px 14px",
    marginBottom: "8px",
    background: "#F8F9FA",
    borderRadius: "8px",
    border: "1px solid #E4E4E7",
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    fontSize: "13px",
    color: "#111111"
};

const btn = {
    padding: "8px 16px",
    background: "linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%)",
    color: "#FFFFFF",
    border: "none",
    borderRadius: "8px",
    cursor: "pointer",
    fontWeight: "700",
    fontSize: "11px",
    textTransform: "uppercase",
    letterSpacing: "0.05em",
    textAlign: "center",
    transition: "all 0.15s ease",
    boxShadow: "0 4px 15px rgba(99, 102, 241, 0.25)"
};

const btnBlue = {
    ...btn,
    background: "linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%)",
    color: "#FFFFFF",
    border: "none",
    textDecoration: "none",
};

const filterFormStyle = {
    margin: 0,
    display: "flex",
    alignItems: "center"
};

const inputSearchStyle = {
    ...inputStyle,
    paddingLeft: "12px",
    width: "200px",
    transition: "all 0.2s ease"
};

const footerStyle = {
    marginTop: "50px",
    paddingTop: "20px",
    borderTop: "1px solid #E4E4E7",
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    fontSize: "11px",
    color: "#71717A",
    flexWrap: "wrap",
    gap: "10px"
};

const chartGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(280px, 1fr))",
    gap: "24px",
    marginTop: "30px",
    marginBottom: "30px"
};

const chartCardStyle = {
    background: "#FFFFFF",
    padding: "20px",
    borderRadius: "12px",
    border: "1px solid #E4E4E7",
    boxShadow: "0 2px 12px rgba(0,0,0,0.04)",
    display: "flex",
    flexDirection: "column",
    transition: "all 0.15s ease",
};

const chartCardTitleStyle = {
    margin: "0 0 4px 0",
    fontSize: "16px",
    fontWeight: "700",
    color: "#111111",
};

const historySectionStyle = {
    marginTop: "30px",
    background: "#FFFFFF",
    borderRadius: "12px",
    border: "1px solid #E4E4E7",
    boxShadow: "0 2px 12px rgba(0,0,0,0.04)",
    padding: "25px",
};

const historyHeaderStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: "20px",
    borderBottom: "1px solid #E4E4E7",
    paddingBottom: "12px"
};

const historyBadgeStyle = {
    background: "#F4F4F5",
    color: "#111111",
    padding: "4px 10px",
    borderRadius: "6px",
    border: "1px solid #E4E4E7",
    fontSize: "11px",
    fontWeight: "bold",
    fontFamily: "monospace"
};

const historyGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(180px, 1fr))",
    gap: "18px",
};

const historyCardStyle = {
    background: "#F8F9FA",
    border: "1px solid #E4E4E7",
    borderRadius: "10px",
    padding: "16px",
    transition: "all 0.2s ease",
};

const livePulseDot = {
    width: "8px",
    height: "8px",
    borderRadius: "50%",
    background: "#10B981",
    display: "inline-block",
};

const statusPulseRed = {
    width: "6px",
    height: "6px",
    borderRadius: "50%",
    background: "#EF4444",
    display: "inline-block"
};

const statusPulseOrange = {
    width: "6px",
    height: "6px",
    borderRadius: "50%",
    background: "#F59E0B",
    display: "inline-block"
};

const panelControlsRow = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: "15px",
    flexWrap: "wrap",
    marginBottom: "20px"
};

const tabContainer = {
    display: "flex",
    gap: "6px",
    background: "#F4F4F5",
    padding: "4px",
    borderRadius: "10px",
    border: "1px solid #E4E4E7",
    flexWrap: "wrap"
};

const localSearchInput = {
    ...inputStyle,
    width: "250px",
    fontSize: "14px",
    padding: "8px 12px"
};

const deviceGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(280px, 1fr))",
    gap: "20px",
    maxHeight: "600px",
    overflowY: "auto",
    paddingRight: "5px",
    paddingTop: "2px"
};

const deviceCardStyle = {
    border: "1px solid",
    borderRadius: "12px",
    padding: "16px",
    transition: "all 0.2s ease",
    display: "flex",
    flexDirection: "column",
    boxShadow: "0 2px 4px rgba(0,0,0,0.02)"
};

const container = document.getElementById("app");

if (container) {
    createRoot(container).render(<App role={container.dataset.role || ""} />);
}

const backboneContainer = document.getElementById("backbone-alerts-app");

if (backboneContainer) {
    createRoot(backboneContainer).render(<BackboneAlerts role={backboneContainer.dataset.role || ""} />);
}
