import React, { useState } from "react";

const cleanHtml = (str) => {
    if (!str) return "";
    return String(str).replace(/<[^>]*>/g, "").trim();
};

export default function PrtgMonitoringPanel({ prtgCustomers = [], getDeviceStatus, getDeviceDuration }) {
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
                    <div style={{ display: "flex", alignItems: "center", gap: "6px", background: "#f1f5f9", padding: "4px 10px", borderRadius: "12px" }}>
                        <span style={livePulseDot} className="pulse-green"></span>
                        <span style={{ fontSize: "12px", color: "#64748b", fontWeight: "600" }}>Live</span>
                    </div>
                </div>
                <span style={historyBadgeStyle}>{counts.all} Total Device</span>
            </div>

            <p style={{ margin: "0 0 20px 0", color: "#64748b", fontSize: "14px" }}>
                Status real-time pelanggan yang terhubung ke jaringan. Data diperbarui otomatis setiap 10 detik.
            </p>

            {/* Controls Row */}
            <div style={panelControlsRow}>
                {/* Tabs */}
                <div style={tabContainer}>
                    <TabButton active={activeTab === "all"} onClick={() => setActiveTab("all")} label="Semua" count={counts.all} color="#3b82f6" />
                    <TabButton active={activeTab === "online"} onClick={() => setActiveTab("online")} label="Online" count={counts.online} color="#10b981" />
                    <TabButton active={activeTab === "down"} onClick={() => setActiveTab("down")} label="Down" count={counts.down} color="#ef4444" />
                    <TabButton active={activeTab === "warning"} onClick={() => setActiveTab("warning")} label="Warning" count={counts.warning} color="#f59e0b" />
                    <TabButton active={activeTab === "paused"} onClick={() => setActiveTab("paused")} label="Paused" count={counts.paused} color="#64748b" />
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
                    <div style={{ gridColumn: "1 / -1", textAlign: "center", padding: "40px", color: "#64748b", background: "#f8fafc", borderRadius: "12px", border: "1px dashed #cbd5e1" }}>
                        🔍 Tidak ada device yang cocok dengan kriteria filter/pencarian.
                    </div>
                ) : (
                    filteredList.map((d, index) => {
                        const status = getDeviceStatus(d);
                        const duration = getDeviceDuration(d);
                        
                        // Theme styling berdasarkan status
                        const theme = {
                            online: { bg: "#f0fdf4", border: "#bbf7d0", icon: "🟢", color: "#166534", badgeBg: "#d1fae5", badgeColor: "#065f46" },
                            down: { bg: "#fef2f2", border: "#fecaca", icon: "🔴", color: "#991b1b", badgeBg: "#fee2e2", badgeColor: "#991b1b", pulse: true },
                            warning: { bg: "#fffbeb", border: "#fef3c7", icon: "🟠", color: "#92400e", badgeBg: "#fef3c7", badgeColor: "#92400e", pulse: true },
                            paused: { bg: "#f8fafc", border: "#e2e8f0", icon: "🟡", color: "#475569", badgeBg: "#e2e8f0", badgeColor: "#475569" },
                            unknown: { bg: "#f8fafc", border: "#e2e8f0", icon: "⚪", color: "#64748b", badgeBg: "#f1f5f9", badgeColor: "#64748b" }
                        }[status] || { bg: "#f8fafc", border: "#e2e8f0", icon: "⚪", color: "#64748b", badgeBg: "#f1f5f9", badgeColor: "#64748b" };

                        return (
                            <div key={index} style={{ ...deviceCardStyle, backgroundColor: theme.bg, borderColor: theme.border }}>
                                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: "10px" }}>
                                    <div style={{ fontWeight: "700", color: "#1e293b", fontSize: "14px", display: "flex", alignItems: "center", gap: "6px" }}>
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

                                <div style={{ fontSize: "13px", color: "#475569", marginBottom: "8px", display: "flex", alignItems: "center", gap: "6px" }}>
                                    <span>🕒</span>
                                    <span>
                                        <strong>Durasi:</strong> {duration}
                                    </span>
                                </div>

                                {(d.lastup || d.lastdown) && (
                                    <div style={{ fontSize: "11px", color: "#94a3b8", marginBottom: "8px" }}>
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
                padding: "8px 16px",
                borderRadius: "8px",
                border: "none",
                cursor: "pointer",
                fontWeight: "600",
                fontSize: "13px",
                display: "flex",
                alignItems: "center",
                gap: "8px",
                background: active ? color : isHovered ? "#f1f5f9" : "transparent",
                color: active ? "#ffffff" : "#475569",
                transition: "all 0.2s ease"
            }}
        >
            {label}
            <span style={{
                fontSize: "11px",
                padding: "2px 6px",
                borderRadius: "999px",
                background: active ? "rgba(255,255,255,0.2)" : "#e2e8f0",
                color: active ? "#ffffff" : "#64748b"
            }}>
                {count}
            </span>
        </button>
    );
}

/* Styles */
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

const statusPulseRed = {
    width: "6px",
    height: "6px",
    borderRadius: "50%",
    background: "#ef4444",
    display: "inline-block"
};

const statusPulseOrange = {
    width: "6px",
    height: "6px",
    borderRadius: "50%",
    background: "#f59e0b",
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
    gap: "8px",
    background: "#f8fafc",
    padding: "4px",
    borderRadius: "10px",
    border: "1px solid #e2e8f0",
    flexWrap: "wrap"
};

const inputStyle = {
    padding: "10px 15px",
    borderRadius: "8px",
    border: "1px solid #cbd5e1",
    fontSize: "15px",
    outline: "none",
    cursor: "pointer",
    boxShadow: "0 1px 3px rgba(0,0,0,0.05)",
    background: "#fff"
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
