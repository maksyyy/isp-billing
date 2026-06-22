import React, { useState } from "react";
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

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

export default function MikrotikMonitoringPanel({
    data,
    loading,
    // Traffic props dari BackboneAlerts
    interfaces = [],
    selectedInterface,
    onSelectInterface,
    trafficHistory = { times: [], rx: [], tx: [] },
}) {
    const [activeSection, setActiveSection] = useState("traffic"); // default ke traffic
    const [searchTerm, setSearchTerm] = useState("");

    if (loading && !data) {
        return (
            <div style={historySectionStyle}>
                <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700" }}>🔌 Router MikroTik Monitor</h3>
                <p style={{ color: "#64748b", margin: "10px 0" }}>⏳ Menghubungi router MikroTik...</p>
            </div>
        );
    }

    if (!data || !data.connected) {
        return (
            <div style={{ ...historySectionStyle, borderLeft: "5px solid #ef4444" }}>
                <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700", display: "flex", alignItems: "center", gap: "8px" }}>
                    🔌 Router MikroTik Monitor <span style={{ fontSize: "11px", background: "#fee2e2", color: "#b91c1c", padding: "2px 8px", borderRadius: "8px" }}>Disconnected</span>
                </h3>
                <p style={{ color: "#64748b", fontSize: "14px", marginTop: "10px" }}>
                    {data?.message || "Koneksi ke MikroTik belum dikonfigurasi. Silakan masuk ke Pengaturan Sistem untuk menghubungkan router."}
                </p>
                <a href="/settings?tab=mikrotik" style={{ ...btnBlue, display: "inline-block", marginTop: "10px", textDecoration: "none", fontSize: "12px", width: "auto", flex: "none", padding: "8px 16px" }} className="btn-hover">
                    ⚙️ Konfigurasi MikroTik
                </a>
            </div>
        );
    }

    const { resources, active_users, address_lists } = data;

    // Filter active users
    const filteredActive = (active_users || []).filter(u =>
        u.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.service.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Filter address lists
    const filteredAddresses = (address_lists || []).filter(entry =>
        entry.customer_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.customer_code.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.list.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.comment.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // RAM calculation
    const ramFreeMb = Math.round((resources?.free_memory || 0) / 1048576);
    const ramTotalMb = Math.round((resources?.total_memory || 0) / 1048576);
    const ramUsedMb = ramTotalMb - ramFreeMb;
    const ramUsedPercent = ramTotalMb > 0 ? Math.round((ramUsedMb / ramTotalMb) * 100) : 0;

    // Traffic helpers
    const currentIface = selectedInterface || interfaces[0] || null;
    const currentRxSpeed = trafficHistory.rx.length > 0 ? trafficHistory.rx[trafficHistory.rx.length - 1] : 0;
    const currentTxSpeed = trafficHistory.tx.length > 0 ? trafficHistory.tx[trafficHistory.tx.length - 1] : 0;

    const formatSpeed = (mbps) => {
        if (!mbps) return "0.00 Mbps";
        if (mbps >= 1000) return `${(mbps / 1000).toFixed(2)} Gbps`;
        return `${mbps.toFixed(2)} Mbps`;
    };

    // Chart.js config
    const chartData = {
        labels: trafficHistory.times,
        datasets: [
            {
                label: "Download (RX)",
                data: trafficHistory.rx,
                borderColor: "#8b5cf6",
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, "rgba(139,92,246,0.25)");
                    gradient.addColorStop(1, "rgba(139,92,246,0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointRadius: (ctx) => ctx.dataIndex === ctx.dataset.data.length - 1 ? 5 : 0,
                pointBackgroundColor: "#8b5cf6",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
            },
            {
                label: "Upload (TX)",
                data: trafficHistory.tx,
                borderColor: "#06b6d4",
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, "rgba(6,182,212,0.15)");
                    gradient.addColorStop(1, "rgba(6,182,212,0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: (ctx) => ctx.dataIndex === ctx.dataset.data.length - 1 ? 4 : 0,
                pointBackgroundColor: "#06b6d4",
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
                labels: { color: "#475569", font: { size: 11, weight: "bold" } }
            },
            tooltip: {
                backgroundColor: "#1e293b",
                titleColor: "#f1f5f9",
                bodyColor: "#94a3b8",
                callbacks: {
                    label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw.toFixed(2)} Mbps`
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: "#94a3b8", font: { size: 10 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: "rgba(0,0,0,0.04)" },
                ticks: {
                    color: "#94a3b8",
                    font: { size: 10 },
                    callback: (v) => `${v} M`
                }
            }
        }
    };

    return (
        <section style={{ ...historySectionStyle, borderLeft: "5px solid #8b5cf6" }}>
            {/* Header */}
            <div style={historyHeaderStyle}>
                <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                    <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700" }}>
                        🔌 Router MikroTik Monitor
                    </h3>
                    <div style={{ display: "flex", alignItems: "center", gap: "6px", background: "#f5f3ff", padding: "4px 10px", borderRadius: "12px" }}>
                        <span style={livePulseDot} className="pulse-green"></span>
                        <span style={{ fontSize: "12px", color: "#6d28d9", fontWeight: "600" }}>Connected</span>
                    </div>
                </div>
                <span style={{ ...historyBadgeStyle, background: "#f5f3ff", color: "#6d28d9" }}>
                    {resources?.board_name || "Device"}
                </span>
            </div>

            <p style={{ margin: "0 0 20px 0", color: "#64748b", fontSize: "14px" }}>
                Status real-time, spesifikasi beban kerja CPU, RAM, serta pemetaan firewall address-list pelanggan MikroTik.
            </p>

            {/* Navigation Tabs */}
            <div style={panelControlsRow}>
                <div style={tabContainer}>
                    {[
                        { key: "traffic", label: "📈 Traffic" },
                        { key: "resources", label: "📊 Resources" },
                        { key: "active_users", label: `👥 Active Users (${(active_users || []).length})` },
                        { key: "address_lists", label: `📋 Address List (${(address_lists || []).length})` },
                    ].map(({ key, label }) => (
                        <button
                            key={key}
                            type="button"
                            onClick={() => setActiveSection(key)}
                            className="btn-hover"
                            style={{
                                padding: "8px 16px", borderRadius: "8px", border: "none", cursor: "pointer",
                                fontWeight: "600", fontSize: "13px", transition: "all 0.2s ease",
                                background: activeSection === key ? "#8b5cf6" : "transparent",
                                color: activeSection === key ? "#fff" : "#475569",
                            }}
                        >
                            {label}
                        </button>
                    ))}
                </div>

                {activeSection !== "resources" && activeSection !== "traffic" && (
                    <input
                        type="text"
                        placeholder="Cari..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        style={localSearchInput}
                    />
                )}
            </div>

            {/* ===== TAB: TRAFFIC ===== */}
            {activeSection === "traffic" && (
                <div>
                    {/* Interface selector + speed strip */}
                    <div style={trafficHeaderRow}>
                        <div style={{ display: "flex", alignItems: "center", gap: "12px", flexWrap: "wrap" }}>
                            {/* Dropdown interface */}
                            {interfaces.length > 0 ? (
                                <select
                                    value={currentIface?.name || ""}
                                    onChange={(e) => {
                                        const match = interfaces.find(i => i.name === e.target.value);
                                        if (match && onSelectInterface) onSelectInterface(match);
                                    }}
                                    style={ifaceSelectStyle}
                                >
                                    {interfaces.map(i => (
                                        <option key={i.id || i.name} value={i.name}>
                                            {i.name} — {i.type.toUpperCase()} {i.running ? "✅" : "⚠️"}
                                        </option>
                                    ))}
                                </select>
                            ) : (
                                <span style={{ fontSize: "13px", color: "#94a3b8" }}>Mendeteksi interface...</span>
                            )}

                            {/* Uptime badge */}
                            {resources?.uptime && (
                                <span style={uptimeBadge}>⏱ Uptime: {resources.uptime}</span>
                            )}
                        </div>

                        {/* Live RX/TX speeds */}
                        <div style={{ display: "flex", gap: "20px", alignItems: "center" }}>
                            <div style={speedItemCard}>
                                <span style={{ fontSize: "11px", color: "#8b5cf6", fontWeight: "700", textTransform: "uppercase" }}>Download RX</span>
                                <strong style={{ fontSize: "18px", color: "#1e293b" }}>{formatSpeed(currentRxSpeed)}</strong>
                            </div>
                            <div style={speedItemCard}>
                                <span style={{ fontSize: "11px", color: "#06b6d4", fontWeight: "700", textTransform: "uppercase" }}>Upload TX</span>
                                <strong style={{ fontSize: "18px", color: "#1e293b" }}>{formatSpeed(currentTxSpeed)}</strong>
                            </div>
                            <div style={{ display: "flex", alignItems: "center", gap: "5px" }}>
                                <span style={livePulseDot} className="pulse-green"></span>
                                <span style={{ fontSize: "11px", color: "#8b5cf6", fontWeight: "700" }}>Live</span>
                            </div>
                        </div>
                    </div>

                    {/* Chart */}
                    <div style={{ height: "260px", marginTop: "16px", position: "relative" }}>
                        {trafficHistory.times.length === 0 ? (
                            <div style={emptyChartStyle}>
                                <span style={{ fontSize: "28px" }}>📈</span>
                                <span>Menunggu polling data traffic pertama...</span>
                                <small style={{ color: "#94a3b8" }}>Data akan muncul dalam ~5 detik setelah interface dipilih</small>
                            </div>
                        ) : (
                            <Line data={chartData} options={chartOptions} />
                        )}
                    </div>
                </div>
            )}

            {/* ===== TAB: RESOURCES ===== */}
            {activeSection === "resources" && resources && (
                <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))", gap: "20px", marginTop: "10px" }}>
                    {/* CPU */}
                    <div style={resourceCard}>
                        <span style={cardLabel}>CPU Load</span>
                        <div style={{ position: "relative", width: "90px", height: "90px", display: "flex", alignItems: "center", justifyContent: "center", margin: "auto" }}>
                            <svg width="90" height="90" viewBox="0 0 36 36">
                                <path stroke="#e2e8f0" strokeWidth="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path stroke="#8b5cf6" strokeWidth="3" strokeDasharray={`${resources.cpu_load || 0}, 100`} fill="none" strokeLinecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span style={{ position: "absolute", fontSize: "18px", fontWeight: "800", color: "#1e293b" }}>{resources.cpu_load || 0}%</span>
                        </div>
                    </div>

                    {/* RAM */}
                    <div style={resourceCard}>
                        <span style={cardLabel}>Memory / RAM Usage</span>
                        <div style={{ display: "flex", justifyContent: "space-between", fontSize: "13px", fontWeight: "bold", color: "#1e293b", marginBottom: "6px" }}>
                            <span>Terpakai: {ramUsedMb} MB</span>
                            <span style={{ color: "#64748b" }}>Total: {ramTotalMb} MB</span>
                        </div>
                        <div style={{ height: "12px", background: "#e2e8f0", borderRadius: "6px", overflow: "hidden" }}>
                            <div style={{ height: "100%", width: `${ramUsedPercent}%`, background: "linear-gradient(90deg, #8b5cf6, #3b82f6)", borderRadius: "6px" }}></div>
                        </div>
                        <span style={{ fontSize: "11px", color: "#94a3b8", display: "block", marginTop: "8px" }}>Bebas: {ramFreeMb} MB ({100 - ramUsedPercent}%)</span>
                    </div>

                    {/* System Info */}
                    <div style={resourceCard}>
                        <span style={cardLabel}>System Details</span>
                        <div style={{ fontSize: "13px", color: "#475569", display: "flex", flexDirection: "column", gap: "8px" }}>
                            {[
                                ["Uptime", resources.uptime],
                                ["Version", `v${resources.version}`],
                                ["CPU Freq", `${resources.cpu_frequency} MHz`],
                            ].map(([k, v]) => (
                                <div key={k} style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid #f1f5f9", paddingBottom: "4px" }}>
                                    <strong>{k}:</strong><span>{v || "Unknown"}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            )}

            {/* ===== TAB: ACTIVE USERS ===== */}
            {activeSection === "active_users" && (
                <div style={{ marginTop: "10px" }}>
                    {filteredActive.length === 0 ? (
                        <div style={emptyTabStyle}>🔍 Tidak ada pengguna aktif yang ditemukan.</div>
                    ) : (
                        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))", gap: "14px" }}>
                            {filteredActive.map((user, i) => (
                                <div key={i} style={userCardStyle}>
                                    {/* Header card */}
                                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: "10px" }}>
                                        <div>
                                            {user.customer_name ? (
                                                <>
                                                    <div style={{ fontWeight: "700", fontSize: "14px", color: "#1e293b" }}>
                                                        👤 {user.customer_name}
                                                    </div>
                                                    <div style={{ fontSize: "11px", color: "#8b5cf6", fontWeight: "600", marginTop: "2px" }}>
                                                        PPPoE: <code style={{ background: "#f5f3ff", padding: "1px 5px", borderRadius: "4px" }}>{user.name}</code>
                                                    </div>
                                                </>
                                            ) : (
                                                <div style={{ fontWeight: "700", fontSize: "14px", color: "#475569" }}>
                                                    🔌 {user.name}
                                                    <span style={{ fontSize: "11px", color: "#94a3b8", marginLeft: "6px" }}>Tidak teridentifikasi</span>
                                                </div>
                                            )}
                                        </div>
                                        <div style={{ display: "flex", flexDirection: "column", gap: "4px", alignItems: "flex-end" }}>
                                            <span style={{
                                                fontSize: "10px", padding: "2px 8px", borderRadius: "999px", fontWeight: "700",
                                                background: user.service === "PPPoE" ? "#dbeafe" : "#ffedd5",
                                                color: user.service === "PPPoE" ? "#1e40af" : "#9a3412"
                                            }}>{user.service}</span>
                                            {user.is_active !== null && (
                                                <span style={{
                                                    fontSize: "10px", padding: "2px 8px", borderRadius: "999px", fontWeight: "700",
                                                    background: user.is_active ? "#d1fae5" : "#fee2e2",
                                                    color: user.is_active ? "#065f46" : "#991b1b"
                                                }}>{user.is_active ? "✅ Aktif" : "🚫 Nonaktif"}</span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Detail rows */}
                                    <div style={userDetailGrid}>
                                        <span style={detailLabel}>IP Address</span>
                                        <span style={detailValue}><code>{user.address}</code></span>

                                        {user.customer_code && user.customer_code !== '-' && (
                                            <>
                                                <span style={detailLabel}>Kode</span>
                                                <span style={{ ...detailValue, color: "#8b5cf6", fontWeight: "700" }}>{user.customer_code}</span>
                                            </>
                                        )}

                                        {user.package_name && (
                                            <>
                                                <span style={detailLabel}>Paket</span>
                                                <span style={detailValue}>📦 {user.package_name}</span>
                                            </>
                                        )}

                                        {user.customer_phone && (
                                            <>
                                                <span style={detailLabel}>Telpon</span>
                                                <a href={`tel:${user.customer_phone}`} style={{ ...detailValue, color: "#2563eb", textDecoration: "none" }}>
                                                    📞 {user.customer_phone}
                                                </a>
                                            </>
                                        )}

                                        <span style={detailLabel}>Uptime</span>
                                        <span style={{ ...detailValue, color: "#10b981", fontWeight: "700" }}>🕒 {user.uptime}</span>

                                        <span style={detailLabel}>MAC/Caller</span>
                                        <span style={{ ...detailValue, fontFamily: "monospace", fontSize: "11px" }}>{user.caller_id}</span>
                                    </div>

                                    {/* Link ke profil pelanggan */}
                                    {user.customer_id && (
                                        <a
                                            href={`/customers/${user.customer_id}`}
                                            style={linkBtnStyle}
                                            className="btn-hover"
                                        >
                                            🔗 Lihat Profil Pelanggan
                                        </a>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* ===== TAB: ADDRESS LIST ===== */}
            {activeSection === "address_lists" && (
                <div style={{ marginTop: "10px" }}>
                    {filteredAddresses.length === 0 ? (
                        <div style={emptyTabStyle}>🔍 Tidak ada data address list yang ditemukan.</div>
                    ) : (
                        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))", gap: "14px" }}>
                            {filteredAddresses.map((entry, i) => {
                                const isIsolated = entry.list?.toLowerCase().includes("isolir") ||
                                    entry.list?.toLowerCase().includes("block") ||
                                    entry.comment?.toLowerCase().includes("isolir");
                                const dbStatus = entry.is_active; // boolean from DB or null

                                return (
                                    <div key={i} style={{
                                        ...userCardStyle,
                                        borderLeft: `4px solid ${isIsolated ? "#ef4444" : "#10b981"}`
                                    }}>
                                        {/* Header */}
                                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: "10px" }}>
                                            <div>
                                                <div style={{ fontWeight: "700", fontSize: "14px", color: "#1e293b" }}>
                                                    📡 {entry.customer_name}
                                                </div>
                                                {entry.customer_code && entry.customer_code !== '-' && (
                                                    <div style={{ fontSize: "11px", color: "#8b5cf6", fontWeight: "600", marginTop: "2px" }}>
                                                        Kode: <strong>{entry.customer_code}</strong>
                                                    </div>
                                                )}
                                            </div>
                                            <div style={{ display: "flex", flexDirection: "column", gap: "4px", alignItems: "flex-end" }}>
                                                {/* Status MikroTik (address-list) */}
                                                <span style={{
                                                    fontSize: "10px", padding: "2px 8px", borderRadius: "999px", fontWeight: "700",
                                                    background: isIsolated ? "#fee2e2" : "#d1fae5",
                                                    color: isIsolated ? "#991b1b" : "#065f46",
                                                    border: `1px solid ${isIsolated ? "#fecaca" : "#bbf7d0"}`
                                                }}>
                                                    {isIsolated ? "🚫 TERISOLIR" : "✅ AKTIF"}
                                                </span>
                                                {/* Status DB */}
                                                {dbStatus !== null && dbStatus !== undefined && (
                                                    <span style={{
                                                        fontSize: "10px", padding: "2px 8px", borderRadius: "999px", fontWeight: "600",
                                                        background: dbStatus ? "#f0fdf4" : "#fef2f2",
                                                        color: dbStatus ? "#16a34a" : "#dc2626"
                                                    }}>
                                                        DB: {dbStatus ? "Aktif" : "Nonaktif"}
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        {/* Detail rows */}
                                        <div style={userDetailGrid}>
                                            <span style={detailLabel}>IP Address</span>
                                            <span style={detailValue}><code>{entry.address}</code></span>

                                            <span style={detailLabel}>Address List</span>
                                            <span style={{ ...detailValue, fontSize: "11px" }}>
                                                <span style={{
                                                    background: isIsolated ? "#fef2f2" : "#f0fdf4",
                                                    color: isIsolated ? "#dc2626" : "#16a34a",
                                                    padding: "2px 8px", borderRadius: "999px", fontWeight: "600"
                                                }}>{entry.list}</span>
                                            </span>

                                            {entry.package_name && entry.package_name !== '-' && (
                                                <>
                                                    <span style={detailLabel}>Paket</span>
                                                    <span style={detailValue}>📦 {entry.package_name}</span>
                                                </>
                                            )}

                                            {entry.customer_phone && entry.customer_phone !== '-' && (
                                                <>
                                                    <span style={detailLabel}>Telpon</span>
                                                    <a href={`tel:${entry.customer_phone}`} style={{ ...detailValue, color: "#2563eb", textDecoration: "none" }}>
                                                        📞 {entry.customer_phone}
                                                    </a>
                                                </>
                                            )}

                                            {entry.comment && (
                                                <>
                                                    <span style={detailLabel}>Comment</span>
                                                    <span style={{ ...detailValue, color: "#64748b", fontSize: "11px" }}>{entry.comment}</span>
                                                </>
                                            )}
                                        </div>

                                        {/* Link profil */}
                                        {entry.customer_id && (
                                            <a
                                                href={`/customers/${entry.customer_id}`}
                                                style={linkBtnStyle}
                                                className="btn-hover"
                                            >
                                                🔗 Lihat Profil Pelanggan
                                            </a>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            )}

        </section>
    );
}

/* ======= STYLES ======= */
const historySectionStyle = {
    marginTop: "30px",
    background: "#fff",
    borderRadius: "16px",
    boxShadow: "0 10px 15px -3px rgba(0,0,0,0.05)",
    padding: "25px",
};

const historyHeaderStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: "20px",
    borderBottom: "1px solid #f1f5f9",
    paddingBottom: "12px"
};

const historyBadgeStyle = {
    background: "#f1f5f9",
    color: "#475569",
    padding: "6px 12px",
    borderRadius: "999px",
    fontSize: "12px",
    fontWeight: "bold",
};

const livePulseDot = {
    width: "8px",
    height: "8px",
    borderRadius: "50%",
    background: "#10b981",
    display: "inline-block",
    boxShadow: "0 0 0 0 rgba(16, 185, 129, 0.7)"
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
    background: "#f8fafc",
    padding: "4px",
    borderRadius: "10px",
    border: "1px solid #e2e8f0",
    flexWrap: "wrap"
};

const btnBlue = {
    padding: "10px 16px",
    background: "#2563eb",
    color: "#fff",
    border: "none",
    borderRadius: "8px",
    cursor: "pointer",
    fontWeight: "600",
    textAlign: "center",
    textDecoration: "none",
};

const localSearchInput = {
    padding: "8px 12px",
    borderRadius: "8px",
    border: "1px solid #cbd5e1",
    fontSize: "14px",
    outline: "none",
    background: "#fff",
    width: "220px",
};

const resourceCard = {
    background: "#f8fafc",
    border: "1px solid #e2e8f0",
    borderRadius: "12px",
    padding: "16px",
    display: "flex",
    flexDirection: "column",
    gap: "10px",
    minHeight: "150px",
    justifyContent: "space-between",
};

const cardLabel = {
    fontSize: "12px",
    color: "#64748b",
    fontWeight: "700",
    textTransform: "uppercase",
    letterSpacing: "0.05em",
};

const trafficHeaderRow = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    flexWrap: "wrap",
    gap: "12px",
    padding: "14px 16px",
    background: "#f8fafc",
    borderRadius: "10px",
    border: "1px solid #e2e8f0",
};

const ifaceSelectStyle = {
    padding: "8px 14px",
    borderRadius: "8px",
    border: "1px solid #cbd5e1",
    fontSize: "13px",
    fontWeight: "600",
    background: "#fff",
    color: "#1e293b",
    outline: "none",
    cursor: "pointer",
    minWidth: "200px",
};

const uptimeBadge = {
    fontSize: "12px",
    color: "#6d28d9",
    background: "#f5f3ff",
    padding: "4px 10px",
    borderRadius: "999px",
    fontWeight: "600",
};

const speedItemCard = {
    display: "flex",
    flexDirection: "column",
    gap: "2px",
    textAlign: "right",
};

const emptyChartStyle = {
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
    justifyContent: "center",
    height: "100%",
    gap: "8px",
    color: "#64748b",
    fontSize: "13px",
};

const userCardStyle = {
    background: "#ffffff",
    border: "1px solid #e2e8f0",
    borderRadius: "12px",
    padding: "16px",
    display: "flex",
    flexDirection: "column",
    justifyContent: "space-between",
    boxShadow: "0 1px 3px 0 rgba(0, 0, 0, 0.05)",
    transition: "transform 0.2s ease, box-shadow 0.2s ease",
};

const userDetailGrid = {
    display: "grid",
    gridTemplateColumns: "100px 1fr",
    rowGap: "8px",
    columnGap: "12px",
    fontSize: "13px",
    alignItems: "center",
    marginBottom: "12px",
};

const detailLabel = {
    color: "#64748b",
    fontWeight: "600",
};

const detailValue = {
    color: "#1e293b",
    fontWeight: "500",
    wordBreak: "break-all",
};

const linkBtnStyle = {
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    gap: "6px",
    padding: "8px 12px",
    background: "#f1f5f9",
    color: "#475569",
    borderRadius: "8px",
    fontSize: "12px",
    fontWeight: "700",
    textDecoration: "none",
    textAlign: "center",
    transition: "all 0.2s ease",
    border: "1px solid #e2e8f0",
};

const emptyTabStyle = {
    padding: "40px",
    textAlign: "center",
    color: "#94a3b8",
    background: "#f8fafc",
    borderRadius: "12px",
    border: "1px dashed #cbd5e1",
    fontSize: "14px",
    fontWeight: "500",
};
