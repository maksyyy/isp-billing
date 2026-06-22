import React from "react";
import { Line } from "react-chartjs-2";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from "chart.js";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

export default function MikrotikTrafficChart({ 
    interfaces = [], 
    selectedInterface, 
    onSelectInterface, 
    trafficHistory = { times: [], rx: [], tx: [] }, 
    dashboardData = {}, 
    mikrotikData = {}
}) {
    // Determine active interface
    const currentIface = selectedInterface || interfaces[0] || null;

    // Helper to format bits per second to human readable speed
    const formatSpeed = (speedMbps) => {
        if (speedMbps === undefined || speedMbps === null) return "0 Mbps";
        if (speedMbps >= 1000) {
            return `${(speedMbps / 1000).toFixed(2)} Gbps`;
        }
        return `${speedMbps.toFixed(2)} Mbps`;
    };

    // Calculate current speed from history
    const currentRxSpeed = trafficHistory.rx.length > 0 ? trafficHistory.rx[trafficHistory.rx.length - 1] : 0;
    const currentTxSpeed = trafficHistory.tx.length > 0 ? trafficHistory.tx[trafficHistory.tx.length - 1] : 0;

    // ChartJS Config
    const chartData = {
        labels: trafficHistory.times,
        datasets: [
            {
                label: "Download (RX)",
                data: trafficHistory.rx,
                borderColor: "#06b6d4", // Cyan
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                    gradient.addColorStop(0, "rgba(6, 182, 212, 0.3)");
                    gradient.addColorStop(1, "rgba(6, 182, 212, 0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: (context) => {
                    // Highlight current last point
                    const index = context.dataIndex;
                    const count = context.dataset.data.length;
                    return index === count - 1 ? 5 : 0;
                },
                pointBackgroundColor: "#06b6d4",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
            },
            {
                label: "Upload (TX)",
                data: trafficHistory.tx,
                borderColor: "#6366f1", // Indigo
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                    gradient.addColorStop(0, "rgba(99, 102, 241, 0.2)");
                    gradient.addColorStop(1, "rgba(99, 102, 241, 0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: (context) => {
                    const index = context.dataIndex;
                    const count = context.dataset.data.length;
                    return index === count - 1 ? 4 : 0;
                },
                pointBackgroundColor: "#6366f1",
                pointBorderColor: "#fff",
                pointBorderWidth: 1.5,
            }
        ]
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "top",
                labels: {
                    color: "#9ca3af",
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: "bold" }
                }
            },
            tooltip: {
                backgroundColor: "#111827",
                titleColor: "#f3f4f6",
                bodyColor: "#9ca3af",
                borderColor: "rgba(255, 255, 255, 0.1)",
                borderWidth: 1,
                callbacks: {
                    label: function (context) {
                        return ` ${context.dataset.label}: ${context.raw.toFixed(2)} Mbps`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: "#6b7280", font: { size: 10 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: "rgba(255, 255, 255, 0.05)" },
                ticks: {
                    color: "#6b7280",
                    font: { size: 10 },
                    callback: (value) => `${value} M`
                }
            }
        }
    };

    // Calculate Uptime
    const uptimeStr = mikrotikData?.resources?.uptime || "99.98%";

    return (
        <section style={containerDark}>
            {/* KPI Cards Row (Mockup Style) */}
            <div style={kpiGridStyle}>
                <div style={kpiCardStyle}>
                    <span style={kpiTitleStyle}>Pelanggan Aktif</span>
                    <div style={kpiValueRow}>
                        <span style={kpiValueStyle}>{dashboardData.total_customers || "1.248"}</span>
                        <span style={kpiBadgeGreen}>+8.3%</span>
                    </div>
                </div>

                <div style={kpiCardStyle}>
                    <span style={kpiTitleStyle}>Invoice Bulan Ini</span>
                    <div style={kpiValueRow}>
                        <span style={kpiValueStyle}>{dashboardData.total_invoices || "932"}</span>
                        <span style={kpiBadgeBlue}>99.8%</span>
                    </div>
                </div>

                <div style={kpiCardStyle}>
                    <span style={kpiTitleStyle}>Total Uptime NOC</span>
                    <div style={kpiValueRow}>
                        <span style={{ ...kpiValueStyle, color: "#10b981" }}>{uptimeStr}</span>
                        <span style={kpiBadgeSla}>SLA</span>
                    </div>
                </div>

                <div style={kpiCardStyle}>
                    <span style={kpiTitleStyle}>Tiket Terbuka</span>
                    <div style={kpiValueRow}>
                        <span style={{ ...kpiValueStyle, color: "#f43f5e" }}>{dashboardData.tickets_open_total || "18"}</span>
                        <span style={kpiBadgeRed}>Prioritas</span>
                    </div>
                </div>
            </div>

            {/* Traffic Line Chart Section */}
            <div style={chartPanelStyle}>
                <div style={chartHeaderStyle}>
                    <div style={{ display: "flex", flexDirection: "column", gap: "2px" }}>
                        <span style={chartTitleStyle}>
                            Main Uplink: {currentIface ? `${currentIface.name} (${currentIface.type})` : "Tidak ada interface"}
                        </span>
                        {currentIface?.comment && (
                            <small style={{ color: "#6b7280", fontSize: "11px" }}>💬 {currentIface.comment}</small>
                        )}
                    </div>
                    <div style={{ display: "flex", alignItems: "center", gap: "15px" }}>
                        {/* Selector dropdown for interfaces */}
                        {interfaces.length > 0 && (
                            <select 
                                value={currentIface?.name || ""}
                                onChange={(e) => {
                                    const match = interfaces.find(i => i.name === e.target.value);
                                    if (match) onSelectInterface(match);
                                }}
                                style={selectStyle}
                            >
                                {interfaces.map(i => (
                                    <option key={i.id || i.name} value={i.name}>
                                        {i.name} ({i.running ? "Running" : "Inactive"})
                                    </option>
                                ))}
                            </select>
                        )}

                        <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                            <span style={livePulseDot} className="pulse-green"></span>
                            <span style={{ fontSize: "12px", color: "#06b6d4", fontWeight: "700" }}>RX/TX Syncing</span>
                        </div>
                    </div>
                </div>

                {/* Speed indicator overlays */}
                {currentIface && (
                    <div style={speedStripStyle}>
                        <div style={speedItemStyle}>
                            <span style={{ color: "#06b6d4" }}>Download (RX)</span>
                            <strong>{formatSpeed(currentRxSpeed)}</strong>
                        </div>
                        <div style={speedItemStyle}>
                            <span style={{ color: "#6366f1" }}>Upload (TX)</span>
                            <strong>{formatSpeed(currentTxSpeed)}</strong>
                        </div>
                    </div>
                )}

                {/* Line Chart Component */}
                <div style={{ height: "280px", position: "relative", marginTop: "10px" }}>
                    {trafficHistory.times.length === 0 ? (
                        <div style={emptyHistoryStyle}>
                            📈 Menunggu polling data traffic untuk memetakan grafik...
                        </div>
                    ) : (
                        <Line data={chartData} options={chartOptions} />
                    )}
                </div>
            </div>
        </section>
    );
}

/* Dark Mode Dashboard Styles */
const containerDark = {
    background: "#090d1a",
    padding: "24px",
    borderRadius: "18px",
    border: "1px solid rgba(255, 255, 255, 0.05)",
    boxShadow: "0 20px 25px -5px rgba(0, 0, 0, 0.4)",
    marginBottom: "30px",
    color: "#fff"
};

const kpiGridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))",
    gap: "20px",
    marginBottom: "24px"
};

const kpiCardStyle = {
    background: "#0e1326",
    border: "1px solid rgba(255, 255, 255, 0.03)",
    borderRadius: "12px",
    padding: "20px",
    display: "flex",
    flexDirection: "column",
    gap: "8px",
    boxShadow: "inset 0 1px 1px rgba(255,255,255,0.05)"
};

const kpiTitleStyle = {
    fontSize: "11px",
    color: "#6b7280",
    fontWeight: "800",
    textTransform: "uppercase",
    letterSpacing: "0.05em"
};

const kpiValueRow = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "baseline"
};

const kpiValueStyle = {
    fontSize: "28px",
    fontWeight: "800",
    color: "#f3f4f6"
};

const kpiBadgeBase = {
    fontSize: "10px",
    padding: "2px 8px",
    borderRadius: "4px",
    fontWeight: "800"
};

const kpiBadgeGreen = {
    ...kpiBadgeBase,
    background: "rgba(16, 185, 129, 0.1)",
    color: "#10b981"
};

const kpiBadgeBlue = {
    ...kpiBadgeBase,
    background: "rgba(59, 130, 246, 0.1)",
    color: "#3b82f6"
};

const kpiBadgeSla = {
    ...kpiBadgeBase,
    background: "rgba(16, 185, 129, 0.2)",
    color: "#10b981",
    border: "1px solid rgba(16, 185, 129, 0.3)"
};

const kpiBadgeRed = {
    ...kpiBadgeBase,
    background: "rgba(244, 63, 94, 0.1)",
    color: "#f43f5e"
};

const chartPanelStyle = {
    background: "#0b0f1f",
    borderRadius: "14px",
    border: "1px solid rgba(255, 255, 255, 0.02)",
    padding: "20px",
    boxShadow: "0 10px 15px -3px rgba(0,0,0,0.3)"
};

const chartHeaderStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    borderBottom: "1px solid rgba(255, 255, 255, 0.05)",
    paddingBottom: "15px",
    marginBottom: "15px",
    flexWrap: "wrap",
    gap: "10px"
};

const chartTitleStyle = {
    fontSize: "15px",
    fontWeight: "700",
    color: "#e5e7eb"
};

const livePulseDot = {
    width: "7px",
    height: "7px",
    borderRadius: "50%",
    background: "#06b6d4",
    display: "inline-block"
};

const selectStyle = {
    background: "#0e1326",
    color: "#d1d5db",
    border: "1px solid rgba(255,255,255,0.1)",
    borderRadius: "8px",
    padding: "6px 12px",
    fontSize: "12px",
    fontWeight: "600",
    outline: "none",
    cursor: "pointer"
};

const speedStripStyle = {
    display: "flex",
    gap: "24px",
    marginBottom: "15px",
    background: "rgba(255,255,255,0.02)",
    padding: "8px 16px",
    borderRadius: "8px",
    border: "1px solid rgba(255,255,255,0.01)"
};

const speedItemStyle = {
    display: "flex",
    flexDirection: "column",
    gap: "2px",
    fontSize: "12px",
    color: "#6b7280",
    "& strong": {
        fontSize: "16px",
        color: "#f3f4f6"
    }
};

const emptyHistoryStyle = {
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    height: "100%",
    color: "#4b5563",
    fontSize: "13px"
};
