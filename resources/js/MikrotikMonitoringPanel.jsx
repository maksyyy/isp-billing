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
                <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700" }}>Router MikroTik Monitor</h3>
                <p style={{ color: "#787774", margin: "10px 0" }}>Menghubungi router MikroTik...</p>
            </div>
        );
    }

    if (!data || !data.connected) {
        return (
            <div style={{ ...historySectionStyle, borderLeft: "5px solid #9F2F2D" }}>
                <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700", display: "flex", alignItems: "center", gap: "8px" }}>
                    Router MikroTik Monitor <span style={{ fontSize: "11.5px", background: "#FDEBEC", color: "#9F2F2D", padding: "2px 8px", borderRadius: "6px" }}>Disconnected</span>
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
                borderColor: "#111111",
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, "rgba(17,17,17,0.15)");
                    gradient.addColorStop(1, "rgba(17,17,17,0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: (ctx) => ctx.dataIndex === ctx.dataset.data.length - 1 ? 5 : 0,
                pointBackgroundColor: "#111111",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
            },
            {
                label: "Upload (TX)",
                data: trafficHistory.tx,
                borderColor: "#787774",
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, "rgba(120,119,116,0.10)");
                    gradient.addColorStop(1, "rgba(120,119,116,0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 1.5,
                pointRadius: (ctx) => ctx.dataIndex === ctx.dataset.data.length - 1 ? 4 : 0,
                pointBackgroundColor: "#787774",
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
        <section style={{ ...historySectionStyle, borderLeft: "5px solid #111111" }}>
            {/* Header */}
            <div style={historyHeaderStyle}>
                <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                    <h3 style={{ margin: 0, fontSize: "18px", fontWeight: "700" }}>
                        Router MikroTik Monitor
                    </h3>
                    <div style={{ display: "flex", alignItems: "center", gap: "6px", background: "#EDF3EC", padding: "4px 10px", borderRadius: "6px", border: "1px solid #D1D1CB" }}>
                        <span style={livePulseDot} className="pulse-green"></span>
                        <span style={{ fontSize: "12px", color: "#346538", fontWeight: "600" }}>Connected</span>
                    </div>
                </div>
                <span style={{ ...historyBadgeStyle, background: "#FAF9F6", color: "#111111" }}>
                    {resources?.board_name || "Device"}
                </span>
            </div>

            <p style={{ margin: "0 0 20px 0", color: "#787774", fontSize: "14px" }}>
                Status real-time, spesifikasi beban kerja CPU, RAM, serta pemetaan firewall address-list pelanggan MikroTik.
            </p>

            {/* Navigation Tabs */}
            <div style={panelControlsRow}>
                <div style={tabContainer}>
                    {[
                        { key: "traffic", label: "Traffic" },
                        { key: "resources", label: "Resources" },
                        { key: "active_users", label: `Active Users (${(active_users || []).length})` },
                        { key: "address_lists", label: `Address List (${(address_lists || []).length})` },
                    ].map(({ key, label }) => (
                        <button
                            key={key}
                            type="button"
                            onClick={() => setActiveSection(key)}
                            className="btn-hover"
                            style={{
                                padding: "6px 14px", borderRadius: "6px", border: "none", cursor: "pointer",
                                fontWeight: "600", fontSize: "13px", transition: "all 0.15s ease",
                                background: activeSection === key ? "#111111" : "transparent",
                                color: activeSection === key ? "#fff" : "#787774",
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
                                            {i.name} — {i.type.toUpperCase()} {i.running ? "(Running)" : "(Inactive)"}
                                        </option>
                                    ))}
                                </select>
                            ) : (
                                <span style={{ fontSize: "13px", color: "#787774" }}>Mendeteksi interface...</span>
                            )}

                            {/* Uptime badge */}
                            {resources?.uptime && (
                                <span style={uptimeBadge}>Uptime: {resources.uptime}</span>
                            )}
                        </div>

                        {/* Live RX/TX speeds */}
                        <div style={{ display: "flex", gap: "20px", alignItems: "center" }}>
                            <div style={speedItemCard}>
                                <span style={{ fontSize: "11px", color: "#787774", fontWeight: "700", textTransform: "uppercase" }}>Download RX</span>
                                <strong style={{ fontSize: "18px", color: "#111111" }}>{formatSpeed(currentRxSpeed)}</strong>
                            </div>
                            <div style={speedItemCard}>
                                <span style={{ fontSize: "11px", color: "#787774", fontWeight: "700", textTransform: "uppercase" }}>Upload TX</span>
                                <strong style={{ fontSize: "18px", color: "#111111" }}>{formatSpeed(currentTxSpeed)}</strong>
                            </div>
                            <div style={{ display: "flex", alignItems: "center", gap: "5px" }}>
                                <span style={livePulseDot} className="pulse-green"></span>
                                <span style={{ fontSize: "11px", color: "#111111", fontWeight: "700" }}>Live</span>
                            </div>
                        </div>
                    </div>

                    {/* Chart */}
                    <div style={{ height: "260px", marginTop: "16px", position: "relative" }}>
                        {trafficHistory.times.length === 0 ? (
                            <div style={emptyChartStyle}>
                                <span>Menunggu polling data traffic pertama...</span>
                                <small style={{ color: "#787774" }}>Data akan muncul dalam ~5 detik setelah interface dipilih</small>
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
                                <path stroke="#FAF9F6" strokeWidth="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path stroke="#111111" strokeWidth="3" strokeDasharray={`${resources.cpu_load || 0}, 100`} fill="none" strokeLinecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span style={{ position: "absolute", fontSize: "18px", fontWeight: "800", color: "#111111" }}>{resources.cpu_load || 0}%</span>
                        </div>
                    </div>

                    {/* RAM */}
                    <div style={resourceCard}>
                        <span style={cardLabel}>Memory / RAM Usage</span>
                        <div style={{ display: "flex", justifyContent: "space-between", fontSize: "13px", fontWeight: "bold", color: "#111111", marginBottom: "6px" }}>
                            <span>Terpakai: {ramUsedMb} MB</span>
                            <span style={{ color: "#787774" }}>Total: {ramTotalMb} MB</span>
                        </div>
                        <div style={{ height: "12px", background: "#FAF9F6", borderRadius: "6px", border: "1px solid #D1D1CB", overflow: "hidden" }}>
                            <div style={{ height: "100%", width: `${ramUsedPercent}%`, background: "#111111", borderRadius: "6px" }}></div>
                        </div>
                        <span style={{ fontSize: "11px", color: "#787774", display: "block", marginTop: "8px" }}>Bebas: {ramFreeMb} MB ({100 - ramUsedPercent}%)</span>
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
                        <div style={emptyTabStyle}>Tidak ada pengguna aktif yang ditemukan.</div>
                    ) : (
                        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))", gap: "14px" }}>
                            {filteredActive.map((user, i) => (
                                <div key={i} style={userCardStyle}>
                                    {/* Header card */}
                                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: "10px" }}>
                                        <div>
                                            {user.customer_name ? (
                                                <>
                                                    <div style={{ fontWeight: "700", fontSize: "14px", color: "#111111" }}>
                                                        {user.customer_name}
                                                    </div>
                                                    <div style={{ fontSize: "11px", color: "#787774", fontWeight: "600", marginTop: "2px" }}>
                                                        PPPoE: <code style={{ background: "#FAF9F6", padding: "1px 5px", borderRadius: "4px", border: "1px solid #D1D1CB" }}>{user.name}</code>
                                                    </div>
                                                </>
                                            ) : (
                                                <div style={{ fontWeight: "700", fontSize: "14px", color: "#111111" }}>
                                                    {user.name}
                                                    <span style={{ fontSize: "11px", color: "#787774", marginLeft: "6px" }}>Tidak teridentifikasi</span>
                                                </div>
                                            )}
                                        </div>
                                        <div style={{ display: "flex", flexDirection: "column", gap: "4px", alignItems: "flex-end" }}>
                                            <span style={{
                                                fontSize: "10px", padding: "2px 8px", borderRadius: "999px", fontWeight: "700",
                                                background: user.service === "PPPoE" ? "#EBF3FE" : "#FFF2E6",
                                                color: user.service === "PPPoE" ? "#2D5B9F" : "#A65F1D"
                                            }}>{user.service}</span>
                                            {user.is_active !== null && (
                                                <span className={user.is_active ? "status-badge-active" : "status-badge-inactive"}>
                                                    {user.is_active ? "Aktif" : "Nonaktif"}
                                                </span>
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
                                                <span style={{ ...detailValue, color: "#111111", fontWeight: "700" }}>{user.customer_code}</span>
                                            </>
                                        )}

                                        {user.package_name && (
                                            <>
                                                <span style={detailLabel}>Paket</span>
                                                <span style={detailValue}>{user.package_name}</span>
                                            </>
                                        )}

                                        {user.customer_phone && (
                                            <>
                                                <span style={detailLabel}>Telpon</span>
                                                <a href={`tel:${user.customer_phone}`} style={{ ...detailValue, color: "#111111", textDecoration: "underline" }}>
                                                    {user.customer_phone}
                                                </a>
                                            </>
                                        )}

                                        <span style={detailLabel}>Uptime</span>
                                        <span style={{ ...detailValue, color: "#346538", fontWeight: "700" }}>{user.uptime}</span>

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
                                            Lihat Profil Pelanggan
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
                        <div style={emptyTabStyle}>Tidak ada data address list yang ditemukan.</div>
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
                                        borderLeft: `4px solid ${isIsolated ? "#9F2F2D" : "#346538"}`
                                    }}>
                                        {/* Header */}
                                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: "10px" }}>
                                            <div>
                                                <div style={{ fontWeight: "700", fontSize: "14px", color: "#111111" }}>
                                                    {entry.customer_name}
                                                </div>
                                                {entry.customer_code && entry.customer_code !== '-' && (
                                                    <div style={{ fontSize: "11px", color: "#787774", fontWeight: "600", marginTop: "2px" }}>
                                                        Kode: <strong>{entry.customer_code}</strong>
                                                    </div>
                                                )}
                                            </div>
                                            <div style={{ display: "flex", flexDirection: "column", gap: "4px", alignItems: "flex-end" }}>
                                                {/* Status MikroTik (address-list) */}
                                                <span className={isIsolated ? "status-badge-inactive" : "status-badge-active"}>
                                                    {isIsolated ? "TERISOLIR" : "AKTIF"}
                                                </span>
                                                {/* Status DB */}
                                                {dbStatus !== null && dbStatus !== undefined && (
                                                    <span className={dbStatus ? "status-badge-active" : "status-badge-inactive"}>
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
                                                <span className={isIsolated ? "status-badge-inactive" : "status-badge-active"}>
                                                    {entry.list}
                                                </span>
                                            </span>

                                            {entry.package_name && entry.package_name !== '-' && (
                                                <>
                                                    <span style={detailLabel}>Paket</span>
                                                    <span style={detailValue}>{entry.package_name}</span>
                                                </>
                                            )}

                                            {entry.customer_phone && entry.customer_phone !== '-' && (
                                                <>
                                                    <span style={detailLabel}>Telpon</span>
                                                    <a href={`tel:${entry.customer_phone}`} style={{ ...detailValue, color: "#111111", textDecoration: "underline" }}>
                                                        {entry.customer_phone}
                                                    </a>
                                                </>
                                            )}

                                            {entry.comment && (
                                                <>
                                                    <span style={detailLabel}>Comment</span>
                                                    <span style={{ ...detailValue, color: "#787774", fontSize: "11px" }}>{entry.comment}</span>
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
                                                Lihat Profil Pelanggan
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
    borderRadius: "6px",
    border: "1px solid #E5E5E0",
    padding: "25px",
};

const historyHeaderStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: "20px",
    borderBottom: "1px solid #E5E5E0",
    paddingBottom: "12px"
};

const historyBadgeStyle = {
    background: "#FAF9F6",
    color: "#111111",
    padding: "6px 12px",
    borderRadius: "6px",
    fontSize: "12px",
    fontWeight: "bold",
    border: "1px solid #D1D1CB"
};

const livePulseDot = {
    width: "8px",
    height: "8px",
    borderRadius: "50%",
    background: "#346538",
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
    gap: "4px",
    background: "#FAF9F6",
    padding: "4px",
    borderRadius: "6px",
    border: "1px solid #D1D1CB",
    flexWrap: "wrap"
};

const btnBlue = {
    padding: "8px 14px",
    background: "#111111",
    color: "#fff",
    border: "none",
    borderRadius: "6px",
    cursor: "pointer",
    fontWeight: "600",
    textAlign: "center",
    textDecoration: "none",
    transition: "all 0.15s ease"
};

const localSearchInput = {
    padding: "8px 12px",
    borderRadius: "6px",
    border: "1px solid #D1D1CB",
    fontSize: "14px",
    outline: "none",
    background: "#FAF9F6",
    width: "220px",
};

const resourceCard = {
    background: "#FAF9F6",
    border: "1px solid #E5E5E0",
    borderRadius: "6px",
    padding: "16px",
    display: "flex",
    flexDirection: "column",
    gap: "10px",
    minHeight: "150px",
    justifyContent: "space-between",
};

const cardLabel = {
    fontSize: "11px",
    color: "#787774",
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
    background: "#FAF9F6",
    borderRadius: "6px",
    border: "1px solid #E5E5E0",
};

const ifaceSelectStyle = {
    padding: "8px 14px",
    borderRadius: "6px",
    border: "1px solid #D1D1CB",
    fontSize: "13px",
    fontWeight: "600",
    background: "#FAF9F6",
    color: "#111111",
    outline: "none",
    cursor: "pointer",
    minWidth: "200px",
};

const uptimeBadge = {
    fontSize: "12px",
    color: "#111111",
    background: "#FAF9F6",
    padding: "4px 10px",
    borderRadius: "6px",
    border: "1px solid #D1D1CB",
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
    color: "#787774",
    fontSize: "13px",
};

const userCardStyle = {
    background: "#ffffff",
    border: "1px solid #E5E5E0",
    borderRadius: "6px",
    padding: "16px",
    display: "flex",
    flexDirection: "column",
    justifyContent: "space-between",
    transition: "transform 0.15s ease, box-shadow 0.15s ease",
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
    color: "#787774",
    fontWeight: "600",
};

const detailValue = {
    color: "#111111",
    fontWeight: "500",
    wordBreak: "break-all",
};

const linkBtnStyle = {
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    gap: "6px",
    padding: "8px 12px",
    background: "white",
    color: "#111111",
    borderRadius: "6px",
    fontSize: "12px",
    fontWeight: "700",
    textDecoration: "none",
    textAlign: "center",
    transition: "all 0.15s ease",
    border: "1px solid #D1D1CB",
};

const emptyTabStyle = {
    padding: "40px",
    textAlign: "center",
    color: "#787774",
    background: "#FAF9F6",
    borderRadius: "6px",
    border: "1px dashed #D1D1CB",
    fontSize: "14px",
    fontWeight: "500",
};
